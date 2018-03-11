<?php
/**
 * "Simple-C" for GetSimple - Simply add contact forms in your pages
 *
 * This plugin is a fork from Nicolas Liautaud's p01-contact plugin
 * 
 * @link http://get-simple.info/extend/plugin/p01-contact/35 Original Version
 * @link http://get-simple.info/extend/plugin/simple-c/784 Latest Version
 * @author Francesco Simone Carta <entuland@gmail.com>
 * @package simple-c
 * @version 0.0.3
 */

if (session_id() == '') {
  session_start();
}

class SimpleC {

  public $version;
  public $default_email;
  public $default_lang;
  public $securimage_url;
  private $forms;

  public function __construct() {
    $this->version = '0.0.3';
    $this->forms = array();
    $dir = dirname(__FILE__);
    define('SIMPLE_C_LANGPATH', $dir . '/lang/');
    define('SIMPLE_C_CONFIGPATH', $dir . '/config.php');
    define('SIMPLE_C_CAPTCHAPATH', $dir . '/captcha/');

    define('SIMPLE_C_DOCURL', 'http://get-simple.info/extend/plugin/simple-c/784');
    define('SIMPLE_C_DOWNURL', 'http://get-simple.info/extend/plugin/simple-c/784');
    define('SIMPLE_C_FORUMURL', 'http://get-simple.info/forums/showthread.php?tid=6004');
    define('SIMPLE_C_VERSIONURL', 'http://get-simple.info/api/extend/?id=784');
  }

  /**
   * Parse a string to replace tags by forms
   *
   * Find tags, create forms structures, check POST and modify string.
   * @param string $contents the string to parse
   * @return string the modified string
   */
  public function parse($contents) {
    $pattern = '`(?<!<code>)\(%\s*simplec\s*(.*)\s*%\)`';
    preg_match_all($pattern, $contents, $tags, PREG_SET_ORDER);
    $ids = array();

    // create forms structures from TAG
    foreach ($tags as $tag) {
      $id = $this->new_form_id();
      $form = $this->parse_tag($id, $tag[1]);
      $this->forms[$id] = $form;
      $ids[] = $id; // forms manipulated by this parsing session
    }
    // modify forms structures from POST, send mail
    if (!empty($_POST['simple-c_form'])) {
      $this->post();
    }
    // replace tags by forms
    foreach ($ids as $id) {
      $contents = preg_replace($pattern, $this->forms[$id]->html(), $contents, 1);
    }
    return $contents;
  }

  private function format($str) {
    $str = trim(
      preg_replace(
        array('`&nbsp;`', '`&quot;`'), 
        array(' ', '"'),
        $str
      )
    );
    return $str;
  }

  /**
   * Parse a tag to create form structure
   *
   * Find emails and parameters, create and setup form object.
   * @param int $id the form id
   * @param string $tag the tag to parse
   * @return SimpleCForm the form object
   */
  private function parse_tag($id, $tag) {

    $form = new SimpleCForm($this, $id);
    $tag = $this->format($tag);

    $param_pattern = '`[,:]\s*([^ ,"=!]+)(!)?\s*("([^"]*)")?\s*((=&gt;|=)?\s*([^,]*))?\s*`';
    $targets_pattern = '`[,:]\s*([_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3}))`i';
    $values_pattern = '`(?:^|\|)\s*(?:"([^"]+)")?\s*([^| ]+)?`';

    // parse emails
    preg_match_all($targets_pattern, $tag, $targets);
    $targets = array_unique($targets[1]);
    // add targets
    if (empty($targets)) {
      $default_email = $this->get_default_email();
      if ($default_email)
        $form->add_target($default_email);
    } else {
      $form->set_targets($targets);
    }
    // delete them from tag
    $rest = preg_replace($targets_pattern, '', $tag);
    $rest = $this->format($rest);
    // parse parameters
    preg_match_all($param_pattern, $rest, $params, PREG_SET_ORDER);
    $default = $this->settings('default_params');
    $default = $this->format($default);
    preg_match_all($param_pattern, ': ' . $default, $default_params, PREG_SET_ORDER);
    $params = array_merge($params, $default_params);
    foreach ($params as $id => $param) {
      $field = new SimpleCField($form, $id, $param[1]);
      $field->set_title($param[4]);

      if ($param[1] == 'formlang') {
        $form->set_lang($param[7]);
      } else {
        if ($param[1] == 'select' || $param[1] == 'radio' || $param[1] == 'checkbox') {
          // fields with multiples values
          preg_match_all($values_pattern, $param[7], $values, PREG_SET_ORDER);
          $values = $this->unset_r($values, 0);
          $field->set_value($values);
        } elseif ($param[1] == 'askcopy') {
          // create checkbox-like structure
          $field->set_value(array(array(1 => $this->lang('askcopy'))));
        } elseif ($param[1] == 'password') {
          // password value is required value
          $field->set_required($param[7]);
        } else {
          $field->set_value($param[7]);
        }

        if ($param[1] != 'password') {
          $field->set_required($param[2] == '!' ? true : false);
        }
        $field->set_locked($param[6] == '=&gt;' ? true : false);
        $form->add_field($field);
      }
    }

    return $form;
  }
  
  private function unset_r($a, $i) {
    foreach ($a as $k => $v) {
      if (isset($v[$i])) {
        unset($a[$k][$i]);
      }
    }
    return $a;
  }

  /**
   * Update POSTed form and try to send mail
   *
   * Check posted data, update form data,
   * define fields errors and form status.
   * At least, if there is no errors, try to send mail.
   */
  private function post() {
    // check posted data and update form data
    $form_id = $_POST['simple-c_form']['id'];
    if (isset($this->forms[$form_id])) {
      $form = $this->forms[$form_id];
      $data = $this->format_data($_POST['simple-c_fields']);
      foreach ($data as $field_id => $field_post) {
        $field = $form->get_field($field_id);
        // for multiple-values fields, posted value define selection
        $value = $field->get_value();
        if (is_array($value)) {
          // selections need to be an array
          if (!is_array($field_post)) {
            $selections = array($field_post);
          } else {
            $selections = $field_post;
          }
          // reset value selection
          foreach ($value as $key => $val) {
            $value[$key][2] = '';
          }
          // set value selection from POST
          foreach ($selections as $selection) {
            foreach ($value as $key => $val) {
              if (trim($val[1]) == trim($selection)) {
                $value[$key][2] = 'selected';
              }
            }
          }
          $field->set_value($value);
        } else {
          // for unique value fields, posted value define value
          $field->set_value($field_post);
        }
        
        $check = $field->check_content();
        $field->set_error($check);
        if ($check) {
          $errors = true;
        }
      }
      // SECURITY : check tokens
      if (!$this->check_token()) {
        $form->set_status('token');
        $this->set_token();
        $form->reset();
      }
      // try to send mail
      elseif (!isset($errors)) {
        if ($this->settings('enable') === false) {
          $form->set_status('disable');
        } elseif ($form->count_targets() == 0) {
          $form->set_status('target');
        } else {
          $form->send_mail();
          $this->set_token();
          $form->reset();
        }
      }
    }
  }

  /**
   * Return next accessible form ID
   * @param string $key the setting key
   * @return mixed the setting value
   */
  private function new_form_id() {
    end($this->forms);
    $id = key($this->forms) + 1;
    reset($this->forms);
    return $id;
  }

  /**
   * Print POST and simple-c content.
   */
  public function debug() {
    ini_set('display_errors', 'on');
    error_reporting(E_ALL);
    echo'<h2 style="color:#c33">simple-c debug</h2>';
    if (!empty($_POST)) {
      echo'<h3>$_POST :</h3>';
      echo'<pre>';
      echo htmlspecialchars(print_r($_POST, true));
      echo'</pre>';
    }
//    echo'<h3>$SimpleC :</h3>';
//    echo'<pre>';
//    echo htmlspecialchars(print_r($this, true));
//    echo'</pre>';
  }

  /**
   * Return a setting value from config file
   * @param string $key the setting key
   * @return mixed the setting value
   */
  public function settings($key) {
    require SIMPLE_C_CONFIGPATH;
    if (isset($simple_c_settings[$key])) {
      return $simple_c_settings[$key];
    }
  }

  /**
   * Format array values
   *
   * For aesthetic and security, and recursive.
   * @param array $array
   * @return array
   */
  private function format_data($array) {
    foreach ($array as $key => $val) {
      if (is_array($val)) {
        $this->format_data($array[$key]);
      } else {
        $tmp = stripslashes($val);
        $tmp = htmlentities($tmp, ENT_QUOTES, 'UTF-8');
        $array[$key] = $tmp;
      }
    }
    return $array;
  }

  /**
   * Return a traduction of the keyword
   *
   * Manage languages between requested langs and existing traductions.
   * @param string $key the keyword
   * @return string
   */
  public function lang($key) {
    return i18n_r('simple-c/' . $key);
  }

  /**
   * Return list of existing langs from lang/langs.php
   * @return array
   */
  public function langs() {
    static $languages;
    if (!isset($languages)) {
      $files = scandir(SIMPLE_C_LANGPATH);
      $languages = array();
      foreach ($files as $file) {
        if (preg_match("#\.php$#", $file)) {
          $languages[] = basename($file, ".php");
        }
      }
    }
    return $languages;
  }

  /**
   * Return the last version of simple-c in GS
   * @return string
   */
  private function last_version() {
    $apiback = file_get_contents(SIMPLE_C_VERSIONURL);
    $response = json_decode($apiback);
    if ($response->status == 'successful') {
      return $response->version;
    }
  }

  /**
   * Check if a new version exists. Return version number if exists, or false.
   * @return mixed
   */
  private function exists_new_version() {
    $actual = explode('.', $this->version);
    $last = $this->last_version();
    $last_r = explode('.', $last);
    foreach ($actual as $key => $val) {
      if (isset($last_r[$key]) && $val < $last_r[$key]) {
        return $last;
      }
    }
    return false;
  }

  /**
   * Save settings if necessary and display configuration panel content
   * Parse and replace values in php config file by POST values.
   */
  public function panel() {
    if(isset($_GET['info'])) {
      echo $this->txt_files();
      return;
    }
    if (isset($_POST['simple-c']['settings'])) {
      $data = $this->format_data($_POST['simple-c']['settings']);
      if ($content = file_get_contents(SIMPLE_C_CONFIGPATH)) {

        $enable = isset($data['enable']) ? 'true' : 'false';
        $content = preg_replace("`('enable' => )(true|false)`", "\\1$enable", $content);
        $debug = isset($data['debug']) ? 'true' : 'false';
        $content = preg_replace("`('debug' => )(true|false)`", "\\1$debug", $content);
        $content = preg_replace("`('lang' => ')[a-zA-Z_]*'`", "\\1{$data['lang']}'", $content);
        $content = preg_replace("`('default_params' => ')[^']*'`", "\\1{$data['default_params']}'", $content);
        $content = preg_replace("`('default_email' => ')[^']*'`", "\\1{$data['default_email']}'", $content);
        $content = preg_replace("`'message_len' => [0-9]+`", "'message_len' => {$data['message_len']}", $content);

        foreach ($data['checklist'] as $key => $val) {
          $content = preg_replace("`('checklist_$key' => ')[^']*'`", "\\1$val'", $content);
        }
        if (file_exists(SIMPLE_C_CONFIGPATH) && $file = fopen(SIMPLE_C_CONFIGPATH, 'w')) {
          fwrite($file, $content);
          fclose($file);

          global $simple_c_settings;
          require(SIMPLE_C_CONFIGPATH);
          $updated = '<div class="updated">' . $this->lang('config_updated') . '</div>';
        } else {
          $error = $this->lang('config_error_modify');
        }
      } else {
        $error = $this->lang('config_error_open');
      }
    }
    if (isset($updated)) {
      echo $updated;
    } elseif (isset($errors)) {
      echo '<div class="error">' . $error . '<pre>' . SIMPLE_C_CONFIGPATH . '</pre></div>';
    }
    echo $this->panel_content();
  }

  /**
   * Return configuration panel content
   *
   * Display informations, parse config file and display settings form.
   * @return string
   */
  private function panel_content() {

    $c = '<h2>' . $this->lang('config_title') . '</h2>';

    //new release
    if ($newversion = $this->exists_new_version()) {
      $c.= '<div class="updated">' . $this->lang('new_release');
      $c.= '<br /><a href="' . SIMPLE_C_DOWNURL . '" target="_blank">';
      $c.= $this->lang('download') . ' (' . $newversion . ')</a></div>';
    }
    //links
    $c.= '<p><a href="' . SIMPLE_C_DOCURL . '" target="_blank">' . $this->lang('doc') . '</a>';
    $c.= ' - <a href="' . SIMPLE_C_FORUMURL . '" target="_blank">' . $this->lang('forum') . '</a></p>';

    $c.= '<form action="" method="post"><table>';

    //enable
    $c.= '<tr><td><b><label style="display:block;float:none">' . $this->lang('enable') . '</label></b>';
    $c.= '<i>' . $this->lang('enable_sub') . '</i></td>';
    $c.= '<td><input type="checkbox" name="simple-c[settings][enable]" ';
    $c.= $this->settings('enable') ? 'checked="checked" ' : '';
    $c.= '/></td></tr>';

    //default email
    $c.= '<tr><td><b><label style="display:block;float:none">';
    $c.= $this->lang('default_email') . '</label></b>';
    $c.= '<i>' . $this->lang('default_email_sub') . ' ';
    $c.= ($this->default_email ? $this->default_email : '"not set"') . '</i></td><td>';
    $c.= '<input type="text" name="simple-c[settings][default_email]" ';
    $settings_email = $this->settings('default_email');
    $c.= 'value="' . $settings_email . '" />';
    $c.= '</td></tr>';

    // language
    $c.= '<tr><td><b><label style="display:block;float:none">' . $this->lang('lang') . '</label></b>';
    $c.= '<i>' . $this->lang('lang_sub') . ' ' . $this->default_lang . '</i></td>';
    $c.= '</td><td><select name="simple-c[settings][lang]">';
    $lang = $this->settings('lang');
    $c.= '<option value=""' . ($lang == '' ? ' selected="selected" ' : '') . '>Default</option>';
    foreach ($this->langs() as $iso) {
      $c.= '<option value="' . $iso . '" ';
      if ($lang == $iso) {
        $c.= 'selected="selected" ';
      }
      $c.= '/>' . $iso . '</option>';
    }
    $c.= '</select></td></tr>';

    //message length
    $c.= '<tr><td><b><label style="display:block;float:none">' . $this->lang('message_len') . '</label></b>';
    $c.= '<i>' . $this->lang('message_len_sub') . '</i></td>';
    $c.= '<td><input type="text" name="simple-c[settings][message_len]" size=3 maxlength=3 ';
    $c.= 'value="' . $this->settings('message_len') . '" /></td></tr>';

    // default parameters
    $c.= '<tr><td colspan="2"><b><label style="display:block;float:none">';
    $c.= $this->lang('default_params') . '</label></b>';
    $c.= '<i>' . $this->lang('default_params_sub') . '</i><br />';
    $c.= '<textarea name="simple-c[settings][default_params]" style="width:100%;height:40px">';
    $c.= $this->settings('default_params');
    $c.= '</textarea></td></tr>';

    //checklists
    $c.= '<tr><td colspan="2"><b><label style="display:block;float:none">';
    $c.= $this->lang('checklists') . '</label></b>';
    $c.= '<i>' . $this->lang('checklists_sub') . '</i>';
    $fields = array(
      'general_fields' => array('text', 'textarea'), 'special_fields' =>
      array('name', 'email', 'address', 'phone', 'website', 'subject', 'message'));
    foreach ($fields as $type => $f) {
      foreach ($f as $id => $field) {
        if (!$id) {
          $c.= '<p></p><p><b>' . $this->lang($type) . ' :</b></p>';
        }
        $content = $this->settings('checklist_' . $field);
        $c.= '<div><b>' . ucfirst($field);
        $c.= ' </b><input name="simple-c[settings][checklist_type][' . $field . ']"';
        $c.= ' type="radio" value="blacklist" checked /> ' . $this->lang('blacklist');
        $c.= ' <input name="simple-c[settings][checklist_type][' . $field . ']"';
        $c.= ' type="radio" value="whitelist" disabled /> ' . $this->lang('whitelist') . '</div>';
        $c.= '<textarea name="simple-c[settings][checklist][' . $field . ']" ';
        $c.= 'style="width:100%;height:' . (40 + strlen($content) * 0.2) . 'px">';
        $c.= $content . '</textarea>';
      }
    }
    $c.= '</tr></td>';

    //debug
    $c.= '<tr><td><b><label style="display:block;float:none">' . $this->lang('debug') . '</label></b>';
    $c.= '<i>' . $this->lang('debug_sub') . '</i><br />';
    $c.= '<b>' . $this->lang('debug_warn') . '</b></td>';
    $c.= '<td><input type="checkbox" name="simple-c[settings][debug]" ';
    $c.= $this->settings('debug') ? 'checked="checked" ' : '';
    $c.= '/></td></tr>';

    $c.= '<tr><td><input type="submit" value="' . $this->lang('save') . '" /></td></tr>';

    $c.= '</table></form>';
    
    $c.= "<p><a href='load.php?id=simple-c&info'>" . $this->lang('info_link_text') . "</a></p>";
    
    return $c;
  }

  /*
   * Return the content of the TXT files which go along this script
   */

  private function txt_files() {
    echo "<h2>Simple-C Plugin</h2>";
    $files = array(
      "CREDITS.txt",
      "LICENSE.txt",
      "CHANGELOG.txt",
    );
    $intro = $data = "";
    $dir = dirname(__FILE__);
    foreach($files as $file) {
      $filename = "$dir/$file";
      $intro .= "<li><a href='#$file'>$file</a></li>";
      $data .= "<a name='$file'></a>";
      if(file_exists($filename)) {
        $content = htmlspecialchars(file_get_contents($filename));
        $title = $this->lang("content_of") . " <em>" . $file . "</em>:";
        $data .= "<h2>$title</h2><pre>$content</pre>";
      } else {
        $title = "<em>" . $file . "</em> " . $this->lang("not_found");
        $data .= "<h2>$title</h2>";
      }
      $data .= "<hr><br>";
    }
    return "<ul>$intro</ul>$data";
  }

  /*
   * Create an unique hash in SESSION
   */

  private function set_token() {
    $_SESSION['simple-c_token'] = uniqid(md5(microtime()), true);
  }

  /*
   * Get the token in SESSION (create it if not exists)
   * @return string
   */

  public function get_token() {
    if (!isset($_SESSION['simple-c_token'])) {
      $this->set_token();
    }
    return $_SESSION['simple-c_token'];
  }

  /*
   * Compare the POSTed token to the SESSION one
   * @return boolean
   */

  private function check_token() {
    if ($this->get_token() === $_POST['simple-c_form']['token']) {
      return true;
    } else {
      return false;
    }
  }

  /*
   * Return settings default email if set and valid,
   * or $this->default_email if set and valid,
   * or false.
   */

  public function get_default_email() {
    $settings_email = $this->settings('default_email');
    $pattern = '`^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$`i';

    if (!empty($settings_email) && preg_match($pattern, $settings_email)) {
      return $settings_email;
    } if (!empty($this->default_email) && preg_match($pattern, $this->default_email)) {
      return $this->default_email;
    }
    
    return false;
  }

}

/*
 * Contact form class

 * Contains fields, manage mail sending.
 */

class SimpleCForm {

  public $SimpleC;
  private $id;
  private $status;
  private $targets;
  private $fields;
  private $lang;
  public static $translations = null;

  /*
   * @param SimpleC $SimpleC
   * @param int $id the form id
   */

  public function __construct($SimpleC, $id) {
    $this->SimpleC = $SimpleC;
    $this->id = $id;
    $this->status = '';
    $this->targets = array();
    $this->fields = array();
    $this->lang = "";
    if (self::$translations === null) {
      self::$translations = array();
      $langs = $SimpleC->langs();
      foreach ($langs as $lang) {
        include SIMPLE_C_LANGPATH . $lang . ".php";
        self::$translations[$lang] = $i18n;
      }
    }
  }

  public function set_lang($lang) {
    if (!empty($lang)) {
      if (strlen($lang) == 2) {
        $lang = strtolower($lang);
        if ($lang == "en") {
          $lang = "en_US";
        } else {
          $lang = strtolower($lang) . "_" . strtoupper($lang);
        }
      }
      $this->lang = $lang;
    }
  }

  /*
   * Return the html display of the form
   * @return string the <form>
   */

  public function html() {
    $html = '<form action="#simple-c' . $this->id . '" autocomplete="off" ';
    $html .= 'id="simple-c' . $this->id . '" class="simple-c" method="post">';

    $html .= $this->html_status();

    if ($this->status != 'sent') {
      foreach ($this->fields as $id => $field) {
        $html .= $field->html();
      }

      $html .= '<div><input name="simple-c_form[id]" type="hidden" value="' . $this->id . '" />';
      $html .= '<input name="simple-c_form[token]" type="hidden" value="' . $this->SimpleC->get_token() . '" />';
      $html .= '<input class="submit" ';
      $html .= 'type="submit" value="' . $this->lang('send') . '" /></div>';
    }
    $html .= '</form>';

    return $html;
  }

  /*
   * Return an html display of the form status
   * @return string the <div>
   */

  private function html_status() {
    if (!$this->status) {
      return '';
    }
    $style = '
	        margin:0 0 20px 0;
	        background:#FCFBB8; 
	        line-height:30px;
	        padding:0 10px;
	        border:1px solid #F9CF51;
	        border-radius: 5px;
	        -moz-border-radius: 5px;
	        -khtml-border-radius: 5px;
	        -webkit-border-radius: 5px;';
    $style .= $this->status == 'sent' ? 'color:#308000;' : 'color:#D94136;';

    return '<div style="' . $style . '">' . $this->lang($this->status) . '</div>';
  }

  /*
   * Return an html http:// link
   * @param string $href the link address
   * @param string $title if not used, the link title will be the address
   * @return string the <a>
   */

  private function html_link($href, $title = false) {
    if (!$title) {
      $title = $href;
    }
    return '<a href="http://' . $href . '">' . $title . '</a>';
  }

  /*
   * Return an html mailto: link
   * @param string $href the email
   * @param string $title if not used, the link title will be the email
   * @return string the <a>
   */

  private function html_mail_link($href, $title = false) {
    if (!$title) {
      $title = $href;
    }
    return '<a href="mailto:' . $href . '">' . $title . '</a>';
  }

  /**
   * Send a mail based on form
   *
   * Create the mail content and headers along to settings, form
   * and fields datas; and update the form status (sent|error).
   */
  public function send_mail() {
    $server = $_SERVER['SERVER_NAME'];
    $uri = $_SERVER['REQUEST_URI'];

    // title
    $content = '<h2>' . $this->lang('fromsite') . ' <i>' . $_SERVER['SERVER_NAME'] . '</i></h2>';
    $content .= '<h3>' . date('r') . '</h3><br/>';

    // fields
    $skip = array('captcha');
    foreach ($this->fields as $field) {
      $type = $field->get_type();
      $value = $field->get_value();
      $title = $field->get_title();
      $title = !empty($title) ? $title : $this->lang($type);
      if ($type == 'name') {
        $name = $value;
        $content .= "<p><b>$title:</b> $name</p>";
      } elseif ($type == 'email') {
        $email = $value;
        $email_link = $this->html_mail_link($email);
        $content .= "<p><b>$title:</b> $email_link</p>";
      } elseif ($type == 'subject') {
        $subject = $value;
        $content .= "<p><b>$title:</b> $subject</p>";
      } elseif (!in_array($type, $skip) && !empty($value)) {
        if ($type != 'askcopy') { // managed blow for him.
          $content .= "<p><b>$title:</b> ";
        }
        switch ($type) {
          case 'message' :
          case 'textarea' :
            $content .= '<p style="margin:10px;padding:10px;border:1px solid silver">';
            $content .= nl2br($value) . '</p>';
            break;
          case 'website' :
            $content .= $this->html_link($value);
            break;
          case 'checkbox' :
          case 'select' :
          case 'radio' :
            $content .= '<ul>';
            foreach ($value as $v)
              if (isset($v[2]) && $v[2] == 'selected')
                $content .= '<li>' . $v[1] . '</li>';
            $content .= '</ul>';
            break;
          case 'askcopy' :
            $askcopy = true;
            $content .= '<p><b>' . $this->lang('askedcopy') . '.</b></p>';
            break;
          default :
            $content .= $value;
        }
        $content .= '</p>';
      }
    }
    if (!isset($askcopy)) {
      $askcopy = false;
    }
    // footer infos
    $footer = '<p><i>' . $this->lang('sentfrom');
    $footer .= ' ' . $this->html_link($server . $uri, $uri);
    $footer_copy = $footer . '</i></p>'; // version without infos below
    $footer .= '<br />If this mail should not be for you, please contact ';
    $footer .= $this->html_mail_link($this->SimpleC->get_default_email());
    $footer .= '</i></p>';

    $targets = implode(',', $this->targets);

    if (empty($name)) {
      $name = $this->lang('nofrom');
    } 
    
    if (empty($email)) {
      $askcopy = false;
      $email = $this->lang('nofrom');
    }
    
    if (empty($subject)) {
      $subject = $this->lang('nosubject');
    }
    
    $subject = '=?utf-8?B?' . base64_encode($subject) . '?=';

    $headers = "From: $name <$email>\r\n";
    $headers .= "Reply-To: $name <$email>\r\n";
    $headers .= "Return-Path: $name <$email>\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

    if (!$this->settings('debug')) {
      // send mail
      $status = mail($targets, $subject, $content . $footer, $headers);
      if ($status) {
        if ($askcopy) { // send copy
          $copy = mail($email, $subject, $content . $footer_copy, $headers);
          if ($copy) {
            $this->status = 'sent_copy';
          } else {
            $this->status = 'error_copy';
          }
        } else {
          $this->status = 'sent';
        }
      } else {
        $this->status = 'error';
      }
    } else {
      // display mail for debug
      echo'<h2 style="color:#c33">simple-c (not) sent mail :</h2>';
      echo'<pre>' . htmlspecialchars($headers) . '</pre>';
      echo'<div style="border:1px solid black;padding:10px">' . $content . $footer . '</div>';
      $this->status = 'debug';
    }
  }

  /*
   * Reset all fields values and errors
   */

  public function reset() {
    foreach ($this->fields as $id => $field) {
      $field->set_value('');
      $field->set_error('');
    }
  }

  /**
   * GETTERS / SETTERS
   */
  public function add_target($tget) {
    $this->targets[] = $tget;
  }

  public function set_targets(array $targets) {
    $this->targets = $targets;
  }

  public function count_targets() {
    return count($this->targets);
  }

  public function get_field($id) {
    foreach($this->fields as $field) {
      if($field->get_id() == $id) {
        return $field;
      }
    }
    throw "Field $id not found!";
  }

  public function get_fields() {
    return $this->fields;
  }

  public function add_field(SimpleCField $field) {
    $this->fields[] = $field;
  }

  public function set_status($status) {
    if (is_string($status)) {
      $this->status = $status;
    }
  }

  public function get_id() {
    return $this->id;
  }

  public function get_status() {
    return $this->status;
  }

  public function settings($key) {
    return $this->SimpleC->settings($key);
  }

  public function lang($key) {
    if (array_key_exists($this->lang, self::$translations)) {
      if (array_key_exists($key, self::$translations[$this->lang])) {
        return self::$translations[$this->lang][$key];
      }
    }
    $lang = $this->lang;
    // the addition of /$lang/ is done on purpose
    // to make the missing language evident
    return i18n_r("simple-c/$lang/$key");
  }

}

class SimpleCField {

  private $form;
  private $id;
  private $type;
  private $title;
  private $value;
  private $required;
  private $locked;
  private $error;

  /*
   * @param SimpleCForm $form the container form
   * @param int $id the field id
   * @param string $type the field type
   */

  public function __construct($form, $id, $type) {
    $this->form = $form;

    $this->id = $id;
    $this->type = $type;
    $this->title = '';
    $this->value = '';
    $this->required = false;
    $this->locked = false;
    $this->error = '';
  }

  /**
   * Check field value
   *
   * Check if field is empty and required or
   * not empty but not valid.
   * @return string the error key, or empty
   */
  public function check_content() {
    if (empty($this->value) && $this->required) {
      // empty and required
      return 'field_required';
    } elseif (!empty($this->value) && !$this->check_validity()) {
      // not empty but not valid
      return 'field_' . $this->type;
    }
    return '';
  }

  /**
   * Check if field value is valid
   * Mean different things depending on field type
   * @return boolean
   */
  public function check_validity() {
    if ($this->blacklisted()) {
      return false;
    }
    $isvalid = true;
    switch ($this->type) {
      case 'email':
        $pattern = '`^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$`i';
        $isvalid = preg_match($pattern, $this->value);
        break;
      case 'phone':
        $pattern = '`^\+?[-0-9(). ]{6,}$$`i';
        $isvalid = preg_match($pattern, $this->value);
        break;
      case 'website':
        $pattern = "`^((http|https|ftp):\/\/(www\.)?|www\.)[a-zA-Z0-9\_\-]+\.([a-zA-Z]{2,4}|[a-zA-Z]{2}\.[a-zA-Z]{2})(\/[a-zA-Z0-9\-\._\?\&=,'\+%\$#~]*)*$`i";
        $isvalid = preg_match($pattern, $this->value);
        break;
      case 'message':
        $size = strlen($this->value);
        $isvalid = ($size >= $this->form->settings('message_len'));
        break;
      case 'captcha':
        include_once SIMPLE_C_CAPTCHAPATH . 'securimage.php';
        $securimage = new Securimage();
        $isvalid = $securimage->check($this->value);
        break;
      case 'fieldcaptcha':
        $isvalid = empty($this->value);
        break;
      case 'password':
        $isvalid = ($this->value == $this->required);
        break;
    }
    return $isvalid;
  }

  /**
   * Check if field value is blacklisted
   *
   * Search any entry of config file field type
   * blacklist in field value.
   * @return boolean
   */
  public function blacklisted() {
    $list = $this->form->settings('checklist_' . $this->type);
    if (empty($list)) {
      return false;
    }
    $array = explode(',', $list);
    foreach ($array as $avoid) {
      if (preg_match('`' . $avoid . '`', $this->value)) {
        return true;
      }
    }
    return false;
  }

  /*
   * Return the html display of the field
   *
   * Manage field title, error message, and type-based display
   * @return string the <div>
   */

  public function html() {
    $id = 'simple-c' . $this->form->get_id() . '_field' . $this->id;
    $name = 'simple-c_fields[' . $this->id . ']';
    $type = $this->general_type();
    $value = $this->value;
    $disabled = $this->locked ? ' disabled="disabled"' : '';

    $html = '
        <div class="field ' . $type . '">';
    if ($this->type != 'askcopy') { // not needed here, the value say everything
      $html .= $this->html_label($id);
    }

    switch ($type) {
      case 'text' :
        $html .= '<input id="' . $id . '" ';
        $html .= 'name="' . $name . '" type="text" ';
        $html .= 'value="' . $value . '"' . $disabled . ' />';
        break;
      case 'textarea' :
        $html .= '<textarea id="' . $id . '" rows="10" ';
        $html .= 'name="' . $name . '"' . $disabled;
        $html .= '>' . $value . '</textarea>';
        break;
      case 'captcha' :
        $html .= '<div class="captchaimg">';
        $html .= '<img id="captchaimg" ';
        $html .= 'src="' . $this->securimage_url() . 'securimage_show.php" ';
        $html .= 'alt="CAPTCHA Image" />';
        $html .= '</div></label></div><a href="#"';
        $html .= 'onclick="document.getElementById(\'captchaimg\').src = ';
        $html .= '\'' . $this->securimage_url() . 'securimage_show.php?\' ';
        $html .= '+ Math.random(); return false">';
        $html .= $this->form->lang('reload');
        $html .= '</a><input id="' . $id . '" ';
        $html .= 'type="text" name="' . $name . '" ';
        $html .= 'size="10" maxlength="6"' . $disabled . ' />';
        break;
      case 'fieldcaptcha' :
        $html .= '<input id="' . $id . '" type="text" name="' . $name . '" />';
        break;
      case 'checkbox' :
        foreach ($this->value as $i => $v) {
          $value = !empty($v[1]) ? ' ' . $v[1] : '';
          $selected = !empty($v[2]) && $v[2] == 'selected' ? ' checked' : '';
          $html .= '<input id="' . $id . '_option' . $i . '"';
          $html .= ' type="checkbox" name="' . $name . '[' . $i . ']"';
          $html .= ' value="' . $value . '"' . $disabled . $selected;
          $html .= ' />' . $value;
        }
        break;
      case 'select' :
        $html .= '<select id="' . $id . '" name="' . $name . '"' . $disabled . '>';
        foreach ($this->value as $i => $v) {
          $value = !empty($v[1]) ? ' ' . $v[1] : ' Default';
          $selected = !empty($v[2]) && $v[2] == 'selected' ? 'selected="selected"' : '';
          $html .= '<option id="' . $id . '_option' . $i . '" value="' . $value;
          $html .= '"' . $selected . ' >' . $value . '</option>';
        }
        $html.= '</select>';
        break;
      case 'radio' :
        foreach ($this->value as $i => $v) {
          $value = !empty($v[1]) ? ' ' . $v[1] : ' Default';
          $selected = !empty($v[2]) && $v[2] == 'selected' ? ' checked' : '';
          $html .= '<input id="' . $id . '_option' . $i . '" type="radio" ';
          $html .= 'name="' . $name . '" value="' . $value . '"';
          $html .= $disabled . $selected . ' />' . $value;
        }
        break;
      case 'password' :
        $html .= '<input id="' . $id . '" ';
        $html .= 'name="' . $name . '" type="password" ';
        $html .= $disabled . ' />';
        break;
        //case 'file' :
        $html .= '<input id="' . $id . '" ';
        $html .= 'type="file" name="' . $name . '"' . $disabled . ' />';
        break;
    }
    $html .= '</div>';
    return $html;
  }

  /*
   * Return the label of the field
   * @param string $for id of the target field 
   * @return string the <div> (unclosed for captcha)
   */

  private function html_label($for) {
    $html = '<div class="label">';
    $html .= '<label for="' . $for . '">';
    if (!empty($this->title)) {
      $html .= $this->title;
    } else {
      $html .= ucfirst($this->form->lang($this->type));
    }
    
    $html .= $this->required ? ' <strong style="color:red">*</strong>' : '';

    if (!empty($this->error)) {
      $html .= ' <span style="font-size:0.7em;color:red">';
      $html .= $this->form->lang($this->error);
      $html .= '</span>';
    }
    if ($this->type != 'captcha') { // captcha close label after image
      $html .= '</label></div>';
    }
    return $html;
  }

  /**
   * Return the general type of a field, even of specials fields.
   */
  function general_type() {
    $types = array(
      'name' => 'text',
      'email' => 'text',
      'phone' => 'text',
      'website' => 'text',
      'subject' => 'text',
      'address' => 'textarea',
      'message' => 'textarea',
      'file' => 'file',
      'captcha' => 'captcha',
      'askcopy' => 'checkbox');
    if (isset($types[$this->type])) {
      return $types[$this->type];
    } else {
      return $this->type;
    }
  }

  /**
   * GETTERS / SETTERS
   */
  public function get_id() {
    return $this->id;
  }

  public function get_type() {
    return $this->type;
  }

  public function get_title() {
    return $this->title;
  }

  public function set_title($title) {
    if (is_string($title)) {
      $this->title = $title;
    }
  }

  public function get_value() {
    return $this->value;
  }

  public function set_value($value) {
    if (is_string($value) || is_array($value)) {
      $this->value = $value;
    }
  }

  public function set_required($required) {
    $this->required = $required;
  }

  public function set_locked($locked) {
    if (is_bool($locked)) {
      $this->locked = $locked;
    }
  }

  public function set_error($error) {
    if (is_string($error)) {
      $this->error = $error;
    }
  }

  public function securimage_url() {
    return $this->form->SimpleC->securimage_url;
  }

}