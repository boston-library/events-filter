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
 
# $_POST to new stdClass
$req = (object) $_POST;
$req = set_date($req);
$req = set_categories($req);

# Start and end dates
function set_date($req)
{
    # Common dates
    $today = date('Y-m-d');
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $this_saturday = date('Y-m-d', strtotime('saturday'));
    $this_sunday = date('Y-m-d', strtotime('sunday'));
    $next_week = date('Y-m-d', strtotime('+7 days'));
    $next_year = date('Y-m-d', strtotime('+1 year'));

    if (!isset($req->date_radio)) {
        $req->date_radio = null;
    }

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

# Categories (all checkboxes)
function set_categories($req)
{
    $req->category = [];
    $regex = '/^category_.{4}$/';

    foreach ($req as $k => $v) {
        if (preg_match($regex, $k)) {
            array_push($req->category, $v);
            # todo: unset, e.g., category_6fda
            #unset($k);
        }
    }
    return $req;
}

# Compare $req to $rss
function filter_feed($req, $rss)
{
    $req->matches = [];

    foreach ($req->category as $filter) {
        #var_dump($filter);

        foreach ($rss->item as $event) {
            #var_dump($event->category);
            #var_dump(in_array($filter,get_object_vars($event->category)));

            if (in_array($filter, get_object_vars($event->category))) {
                #return $event;
                array_push($req->matches, $event);
            }
        }
    }
    return $req;
}

echo '<pre>';
#var_dump($_POST);
#var_dump($req);
var_dump(filter_feed($req, $rss));
echo '</pre>';

/*
echo '<pre>';
foreach ($rss->item as $rss) {
    print_r($rss->link);
}
echo '</pre>';
 */

echo '<pre>';
#var_dump($rss->item);
echo '</pre>';
