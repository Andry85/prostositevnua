<?php if(!defined('IN_GS')){ die('you cannot load this page directly.'); } ?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
	<title><?php get_page_clean_title(); ?></title>
	<meta name='yandex-verification' content='442c868422f9102e' />
	<meta name="viewport" content="width=device-width; initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta http-equiv="pragma" content="no-cache">
	<meta http-equiv="cache-control" content="no-cache">
	<meta name="robots" content="INDEX,FOLLOW">
	<?php get_i18n_header(); ?>
	<link rel="shortcut icon" href="favicon.ico">
	<link href="<?php get_theme_url(); ?>/style.css" rel="stylesheet">
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?php get_theme_url(); ?>/animation/animate.min.css">
    <link rel="stylesheet" href="<?php get_theme_url(); ?>/jquery.mCustomScrollbar.css">
    <link rel="stylesheet" href="<?php get_theme_url(); ?>/chosen.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <script src="<?php get_theme_url(); ?>/animation/wow.min.js"></script>
    <script src="<?php get_theme_url(); ?>/animation/wow-call.js"></script>
    <script src="http://cdnjs.cloudflare.com/ajax/libs/modernizr/2.6.2/modernizr.min.js"></script>
    <script src="<?php get_theme_url(); ?>/assets/js/jquery.slicknav.js"></script>
    <script src="<?php get_theme_url(); ?>/assets/js/html5lightbox.js"></script>
    <!--
    <script src="http://maps.google.com/maps/api/js?sensor=true"></script>
    <script src="<?php get_theme_url(); ?>/assets/js/gmaps.js"></script>
    <script src="<?php get_theme_url(); ?>/assets/js/map-call.js"></script>
-->
    <script src="<?php get_theme_url(); ?>/assets/js/prettify.js"></script>
    
	<script src="<?php get_theme_url(); ?>/assets/js/owl.carousel.js"></script>
    <script src="<?php get_theme_url(); ?>/assets/js/owl.call.js"></script>
    <script src="<?php get_theme_url(); ?>/assets/js/jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="<?php get_theme_url(); ?>/assets/js/chosen.js"></script>

    <script type="text/javascript" src="<?php get_theme_url(); ?>/assets/js/jquery.validate.min.js"></script>
    <script type="text/javascript" src="<?php get_theme_url(); ?>/assets/js/myscripts.js"></script>
    <script type="text/javascript" src="<?php get_theme_url(); ?>/assets/js/jquery.mask.min.js"></script>
    <script type="text/javascript" src="<?php get_theme_url(); ?>/assets/js/maskedinput.js"></script>
    <script src="<?php get_theme_url(); ?>/assets/js/main.js"></script>
</head>
<body id="<?php get_page_slug(); ?>">
    <a class="brif" href="#" title="">&nbsp;</a>
    <div id="top" class="wrapper">
            <main class="main" role="main">
                <?php get_page_content(); ?>
            </main>
            <header class="header">
                <div class="inner">
                    <?php include('header_index.php'); ?>
                </div>
            </header>
            <?php include('footer.php'); ?>



