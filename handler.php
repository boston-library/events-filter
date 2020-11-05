<?php

/**
 * Libraries
 */

# https://github.com/dg/rss-php
require_once 'lib/feed.php';
Feed::$cacheDir = 'tmp';
Feed::$cacheExpire = '+1 hour';

$url = 'https://bpl.bibliocommons.com/events/rss/all';
$rss = Feed::loadRss($url);

# https://github.com/zcontent/icalendar
require_once 'lib/zapcallib.php';
$ics = new ZCiCal();


/**
 * Parse RSS
 */

# $_POST to new stdClass
$req = (object) $_POST;
$req = set_date($req);
$req = set_categories($req);

# Set start and end dates
function set_date($req)
{
    # Useful date objects
    $f = 'Y-m-d';
    $today = date($f);
    $tomorrow = date($f, strtotime('+1 day'));
    $this_saturday = date($f, strtotime('saturday'));
    $this_sunday = date($f, strtotime('sunday'));
    $next_week = date($f, strtotime('+7 days'));
    $next_year = date($f, strtotime('+1 year'));

    # Prevent warnings
    if (!isset($req->date_radio)) {
        $req->date_radio = null;
    }

    # Radio button options
    if (!$req->start_date || !$req->end_date) {
        switch ($req->date_radio) {
          case 'today':
            $req->start_date = $today;
            $req->end_date = $today;
            break;

          case 'tomorrow':
            $req->start_date = $tomorrow;
            $req->end_date = $tomorrow;
            break;

          case 'this_weekend':
            $req->start_date = $this_saturday;
            $req->end_date = $this_sunday;
            break;

          case 'next_week':
            $req->start_date = $today;
            $req->end_date = $next_week;
            break;

          default:
            $req->start_date = $today;
            $req->end_date = $next_year;
            break;
        }
    }

    unset($req->date_radio);
    return $req;
}

# Categories (all form checkboxes)
function set_categories($req)
{
    $req->category = [];
    $regex = '/^category_.{4}$/';

    foreach ($req as $k => $v) {
        if (preg_match($regex, $k)) {
            array_push($req->category, $v);
            unset($req->$k);
        }
    }
    return $req;
}

# Compare $req and $rss
# e.g., foreach $match: $ics->export();
function filter_feed($req, $rss)
{
    # Populate $req->matches with SimpleXMLElements
    $req->matches = [];

    # Dates
    # todo

    # Categories
    foreach ($req->category as $filter) {
        foreach ($rss->item as $event) {
            if (in_array($filter, get_object_vars($event->category))) {
                array_push($req->matches, $event);
            }
        }
    }

    # Flags
    # todo
    
    return $req;
}


/**
 * Output HTML, iCal, and CSV
 */

# todo


/**
 * Debug
 */

echo '<pre>';
#var_dump($_POST);
#var_dump($req);
var_dump(filter_feed($req, $rss));
echo '</pre>';
