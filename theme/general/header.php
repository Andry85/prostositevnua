<?php if(!defined('IN_GS')){ die('you cannot load this page directly.'); } ?>
<div class="header-top">
    <div class="logo">
        <a href="<?php get_site_url(); ?>" title="">&nbsp;</a>
    </div>
    <nav class="nav">
        <ul class="menu">
            <?php get_i18n_navigation(return_page_slug(),0,99,I18N_SHOW_NORMAL); ?>
        </ul>
    </nav>
</div>
<div class="lang">
    <ul class="lang-list">
        <li><a href="<?php echo htmlspecialchars(return_i18n_setlang_url('ua')); ?>"><span class="folFlag flag-ua"></span></a></li>
        <li><a href="<?php echo htmlspecialchars(return_i18n_setlang_url('ru')); ?>"><span class="folFlag flag-ru"></span></a></li>
    </ul>
</div>