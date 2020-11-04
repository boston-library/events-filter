<?php

/**
 * Libraries
 */

# https://github.com/dg/rss-php
require_once 'lib/feed.php';
Feed::$cacheDir = 'tmp';
Feed::$cacheExpire = '1 hour';

$url = 'https://bpl.bibliocommons.com/events/rss/all';
$rss = Feed::loadRss($url);

# https://github.com/zcontent/icalendar
require_once 'lib/zapcallib.php';
$ics = new ZCiCal();


/**
 * Handler
 */

$req = array_map(
    function ($a) {
        return false;
    },
    $_POST
);

echo '<pre>';
var_dump($_POST);
echo '</pre>';

/*
echo '<pre>';
foreach ($rss->item as $rss) {
    print_r($rss->link);
}
echo '</pre>';
 */

echo '<pre>';
var_dump($rss->item);
echo '</pre>';
