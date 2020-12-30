<?php
Header( 'Cache-Control: no-cache' );
Header( 'Pragma: no-cache' );

$title = 'Export Events';
$description = 'unused';

$api_url = 'https://bpl.bibliocommons.com/widgets/external_templates.json';
$template_parts = file_get_contents( $api_url ); //ideally cache this response
$template_parts = json_decode( $template_parts );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1"> 
	<title><?= $title ?> | Boston Public Library</title>
	<style type="text/css"><!-- #footer {
			width: 630px;
		}
		input {
		box-sizing : border-box !important;
		}
		input[type=submit] {
		background: #006072 !important;
		border: #006072 !important;
		}
		.libcal_button {
		  background: #006072;
		  border: 1px solid #474747;
		  border-radius: 4px;
		  color: #FFFFFF;
		  font: 14px Arial, Helvetica, Verdana;
		  padding: 8px 20px;
		  cursor: pointer;
		}
		.libcal_button:hover, #eq_13697:active, #eq_13697:focus {
		  opacity: 0.6;
		}
		.fltrt {
		float: right;
		margin-left: 1em;
		}
		.fltlft {
		float: left;
		margin-right: 1em;
		}
	</style>
	<link rel="shortcut icon" href="https://bpl.bibliocommons.com/images/MA-BOSTON-BRANCH/favicon.ico" />
<?php echo $template_parts->css; ?>
<link rel='stylesheet' id='wp-block-library-css'  href='https://d4804za1f1gw.cloudfront.net/wp-includes/css/dist/block-library/style.min.css?ver=5.2.5' type='text/css' media='all' />
<link rel='stylesheet' id='bibliostyle-css'  href='https://d4804za1f1gw.cloudfront.net/wp-content/themes/bpl/style.css?ver=3.16.3' type='text/css' media='all' />
<link rel='stylesheet' id='style2-css'  href='https://d4804za1f1gw.cloudfront.net/wp-content/themes/bpl/style2.css?ver=3.16.3' type='text/css' media='all' />
<link rel='stylesheet' id='header_all-css'  href='https://d4804za1f1gw.cloudfront.net/wp-content/themes/bpl/css/header_all.css?ver=3.16.3' type='text/css' media='all' />
<link rel='stylesheet' id='fontello-css'  href='https://d4804za1f1gw.cloudfront.net/wp-content/themes/bibliocommons/css/fontello/css/cms-wordpress.css?ver=3.16.3' type='text/css' media='all' />
<link rel='stylesheet' id='google-font-css'  href='https://fonts.googleapis.com/css?family=Libre+Baskerville&#038;subset=latin-ext' type='text/css' media='all' />
<link rel='stylesheet' id='biblioweb-css'  href='https://d4804za1f1gw.cloudfront.net/wp-content/themes/bpl/css/v3.css?ver=3.16.3' type='text/css' media='all' />
<link rel='stylesheet' id='fl-builder-layout-6217990-css'  href='https://d4804za1f1gw.cloudfront.net/wp-content/uploads/sites/30/bb-plugin/cache/6217990-layout.css?ver=85629da33b4d3745d59882307445a82b' type='text/css' media='all' />
</head>
<body class="page-template-default page page-id-6578018 logged-in admin-bar fl-builder boston-bibliocommons-theme customize-support fl-builder-breakpoint-large" data-feedly-extension-follow-feed="1.0.3" style="position: relative; min-height: 100%; top: 0px;">
<div class="a11y-skip-links">
<?php echo $template_parts->screen_reader_navigation; ?>
	<a class="hidden-lg hidden-md screen_reader_nav" href="#content">Skip to content</a> 
</div>
<?php echo $template_parts->header; ?>
<div class="c-sidebar-drawer-anchor js-sidebar-drawer-anchor" style="padding: 2em"><section class="biblioweb_container" id="content-start" tabindex="-1">
    <div class="clear"></div>
    <div class="systemMessages">
            </div>
<div id="page">
<div class="clear"></div>                    <article id="post-6578018" class="post-6578018 page type-page status-publish hentry">
                                        <header class="a11y-visually-hidden">
                    <h1 data-key="visually-hidden-title"><?= $title ?></h1>
                </header>
            
            <div class="entry-content js-sidebar-button-anchor">
                <div class="fl-builder-content fl-builder-content-6578018 fl-builder-content-primary" data-post-id="6578018"><div class="fl-row fl-row-full-width fl-row-bg-none fl-node-5eed131ed9e35" data-node="5eed131ed9e35">
	<div class="fl-row-content-wrap">
						<div class="fl-row-content fl-row-fixed-width fl-node-content">
		
<div class="fl-col-group fl-node-5eed188fe6104" data-node="5eed188fe6104">
			<div class="fl-col fl-node-5eed188fe626f" data-node="5eed188fe626f">
	<div class="fl-col-content fl-node-content">
	<div class="fl-module fl-module-heading fl-node-5eed188fe602a" data-node="5eed188fe602a">
	<div class="fl-module-content fl-node-content">
		<h1 class="fl-heading">
		<span class="fl-heading-text"><?= $title ?></span>
	</h1>
	</div>
</div>
	</div>
</div>
	</div>

<div class="fl-col-group fl-node-5eed131ede222" data-node="5eed131ede222">
			<div class="fl-col fl-node-5eed131ede39a" data-node="5eed131ede39a">
	<div class="fl-col-content fl-node-content">
	<div class="fl-module fl-module-rich-text fl-node-5eed131ecfc2a" data-node="5eed131ecfc2a">
	<div class="fl-module-content fl-node-content">
		<div class="fl-rich-text">