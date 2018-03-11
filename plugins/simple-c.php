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

require_once GSPLUGINPATH . 'simple-c/simple-c-functions.php';
$simple_c = new SimpleC();
$simple_c->default_email = simple_c_default_email();
$simple_c->default_lang = $LANG;
$simple_c->securimage_url = $SITEURL . 'plugins/simple-c/captcha/';

$thisfile = basename(__FILE__, '.php');
i18n_merge('simple-c') || i18n_merge('simple-c', 'en_US');

register_plugin(
        $thisfile, // ID of plugin, should be filename minus php
        'Simple-C', // Title of plugin
        $simple_c->version, // Version of plugin
        'Francesco Simone Carta', // Author of plugin
        'http://get-simple.info/extend/a/entuland', // Author URL
        'Simply add contact forms in your pages.<br>
    ("Simple-C" is a fork of Nicolas Liautaud\'s <a href="http://get-simple.info/extend/plugin/p01-contact/35">p01-contact</a> plugin)', // Plugin Description
        'plugins', // Page type of plugin
        'simple_c_action'     // Function that displays content
);

add_filter('content', 'simple_c_filter');
add_action('plugins-sidebar', 'createSideMenu', array($thisfile, $simple_c->lang('menu_settings_text'), 'settings'));

/*
 * Handle for GS content filter (parse GS pages)
 */

function simple_c_filter($contents) {
  global $simple_c;
  global $i18n;
  $contents = $simple_c->parse($contents);
  if ($simple_c->settings('debug')) {
    $simple_c->debug();
  }
  return $contents;
}

/*
 * Handle for GS action (display admin panel)
 */

function simple_c_action() {
  global $simple_c;
  echo $simple_c->panel();
}

/*
 * Return email of the first GS user found
 * @return string
 */

function simple_c_default_email() {
  $files = scandir(GSUSERSPATH);
  foreach ($files as $filename) {
    if (preg_match("#\.xml$#", $filename)) {
      $data = getXML(GSUSERSPATH . $filename);
      return $data->EMAIL;
    }
  }
  return "";
}
