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
#$rss->registerXPathNamespace('bc', 'http://bibliocommons.com/rss/1.0/modules/event/');

# https://github.com/zcontent/icalendar
require_once 'lib/zapcallib.php';
$ics = new ZCiCal();


/**
 * Parse RSS
 * Takes a raw $_GET superglobal and returns a legible object
 */

# $_GET to new stdClass
$req = (object) $_GET;
$req = set_date($req);
$req = set_categories($req);

# Set the start and end dates
function set_date($req)
{
    # Prevent warnings
    $req->date_radio = (isset($req->date_radio)) ?: false;

    # Common useful dates
    $f = 'Y-m-d';
    $today = date($f);
    $tomorrow = date($f, strtotime('+1 day'));
    $this_saturday = date($f, strtotime('saturday'));
    $this_sunday = date($f, strtotime('sunday'));
    $next_week = date($f, strtotime('+7 days'));
    $next_year = date($f, strtotime('+1 year'));

    # Radio button options
    # Only applied absent date entry
    if ($req->start_date || $req->end_date) {
        # Partial manual date fallback
        $req->start_date = ($req->start_date) ?: $today;
        $req->end_date = ($req->end_date) ?: $next_year;
    } else {
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

# Set the categories (all form checkboxes)
# Strips the bin2hex() suffixes and adds $req->category
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

    #var_dump($req);
    return $req;
}


/**
 * Find matches
 * todo: Split into 3 clear mini-functions
 */

# Instantiate the $req->matches object
$req->matches = new stdClass();
$req->matches = add_categories($req, $rss);
#$req->matches = add_categories($req, $rss);

# Compare $req and $rss
# Probs not called directly in production
# todo: Clarify scoping/privacy with a class
function add_categories($req, $rss)
{
    # Collect output in new array
    $out = [];

    # Add all possible matches
    if (empty($req->category)) {
        $out = $rss;
    } else {
        foreach ($req->category as $filter) {
            foreach ($rss->item as $event) {
                if (in_array($filter, get_object_vars($event->category))) {
                    array_push($out, $event);
                }
            }
        }
    }

    #var_dump($req->matches);
    return (object) $out;
}

# Filter by radio buttons
# todo: Make it independent of add_categories() output
function filter_options($req, $rss)
{
    # Collect output in new array
    $out = [];
    #add_categories($req, $rss);

    # Prevent warnings
    $req->is_virtual = (isset($req->is_virtual)) ?: false;
    $req->is_featured = (isset($req->is_featured)) ?: false;
    $req->is_cancelled = (isset($req->is_cancelled)) ?: false;

    # Subtract invalid matches
    # https://stackoverflow.com/a/622363
    if (!empty($req->matches->item)) {
        foreach ($req->matches->item as $match) {
            # Define namespace
            $ns = $match->children('bc', true);
            #var_dump($ns);

            # Cast string to bool
            # https://www.php.net/manual/en/types.comparisons.php
            $is_virtual = ((string) $ns->is_virtual === 'true') ? true : false;
            $is_featured = ((string) $ns->is_featured === 'true' || (string) $ns->is_featured_at_location === 'true') ? true : false;
            $is_cancelled = ((string) $ns->is_cancelled === 'true') ? true : false;

            # is_virtual mismatch
            if ($req->is_virtual === true && $is_virtual === true) {
                array_push($out, $match);
            }

            # is_featured mismatch
            if ($req->is_featured === true && $is_featured === true) {
                array_push($out, $match);
            }

            # is_cancelled mismatch
            # Note default logic: hide cancelled
            if ($req->is_cancelled === false && $is_cancelled === false) {
                array_push($out, $match);
            }
        }
    }

    #var_dump((object)$output);
    return (object) $out;
}

function filter_date($req, $rss)
{
    return false;
}


/**
 * Output HTML, iCal, and CSV
 */

# todo


/**
 * Debug
 */

echo '<pre>';
#var_dump($req);
#var_dump($rss);
var_dump($req->matches);

var_dump(filter_options($req, $rss));
echo '</pre>';
