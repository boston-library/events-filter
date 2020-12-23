<?php

header('Cache-Control: no-cache'); # HTTP 1.1
header('Pragma: no-cache'); # HTTP 1.0

$api_url = 'https://bpl.bibliocommons.com/widgets/external_templates.json';
$template_parts = file_get_contents($api_url); // ideally cache this response
$template_parts = json_decode($template_parts);

$header = <<<EOT
<!DOCTYPE html>
<html style="font-size: 16px;">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Export Events | Boston Public Library</title>
    $template_parts->css

    <!-- Start BiblioCommons -->
    <link rel="shortcut icon" href="https://bpl.bibliocommons.com/images/MA-BOSTON-BRANCH/favicon.ico" />
    <link rel='stylesheet' id='bibliostyle-css' href='https://d34rompce3lx70.cloudfront.net/wp-content/themes/bpl/style.css' type='text/css' media='all'/>
    <link rel='stylesheet' id='style2-css' href='https://d34rompce3lx70.cloudfront.net/wp-content/themes/bpl/style2.css' type='text/css' media='all'/>
    <link rel='stylesheet' id='header_all-css' href='https://d34rompce3lx70.cloudfront.net/wp-content/themes/bpl/css/header_all.css' type='text/css' media='all'/>
    <!-- End BiblioCommons -->

    <!-- DataTables -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs-3.3.7/dt-1.10.18/r-2.2.2/datatables.min.css"/>
    <link rel="stylesheet" type="text/css" href="lib/style.css" />

  </head>
  <body>

    <!-- Start BiblioCommons -->
    <div class="a11y-skip-links">
    <a class="hidden-lg hidden-md screen_reader_nav" href="#content">Skip to content</a>
    </div>
    $template_parts->header
    <section class="biblioweb_container" id="content-start" tabindex="-1">
    <div class="clear"></div>
    <div class="systemMessages"></div>
    <div id="page">
    <div class="container_12">
    <!-- #masthead -->
    <div class="clear"></div>
    <div class="content_wrap rev2">
    <div id="primary">
    <div id="content" role="main">
    <article id="post-170332" class="post-170332 page type-page status-publish hentry" style="" itemscope itemtype="https://schema.org/Article">
    <header class="entry-header" style="margin-bottom: 2em;">
    <h1 itemprop="name" class="entry-title o-heading--giant o-heading--giant@mobile">Export Events</h1>
    </header>
    <div class="entry-content">
    <!-- End BiblioCommons -->
EOT;
echo $header;
