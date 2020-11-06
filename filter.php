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
 */

# $_GET to new stdClass
$req = (object) $_GET;
$req = set_date($req);
$req = set_categories($req);

# Set start and end dates
function set_date($req)
{
    # Prevent warnings
    $req->date_radio = (isset($req->date_radio)) ?: false;

    # Useful date objects
    $f = 'Y-m-d';
    $today = date($f);
    $tomorrow = date($f, strtotime('+1 day'));
    $this_saturday = date($f, strtotime('saturday'));
    $this_sunday = date($f, strtotime('sunday'));
    $next_week = date($f, strtotime('+7 days'));
    $next_year = date($f, strtotime('+1 year'));

    # Radio button options
    # Only without manual date entry
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

    #var_dump($req);
    return $req;
}


/**
 * Find matches
 * todo: Split into 3 clear mini-functions
 */

# Compare $req and $rss
# e.g., foreach $match: $ics->export();
function filter_categories($req, $rss)
{
    $req->matches = [];

    # Add all possible matches
    if (empty($req->category)) {
        $req->matches = $rss;
    } else {
        foreach ($req->category as $filter) {
            foreach ($rss->item as $event) {
                if (in_array($filter, get_object_vars($event->category))) {
                    array_push($req->matches, $event);
                }
            }
        }
    }

    #var_dump($req->matches);
    return $req->matches;
}

# Filter by radio buttons
# todo: Make it independent of filter_categories() output
function filter_options($req, $rss)
{
    # Prevent warnings
    $req->is_virtual = (isset($req->is_virtual)) ?: false;
    $req->is_featured = (isset($req->is_featured)) ?: false;
    $req->is_cancelled = (isset($req->is_cancelled)) ?: false;

    # Subtract invalid matches
    # https://stackoverflow.com/a/622363
    if (!empty($req->matches)) {
        foreach ($req->matches->item as $match) {
            $ns = $match->children('bc', true);
            #var_dump($ns);

            # is_virtual mismatch
            if ($req->is_virtual && !$ns->is_virtual) {
                unset($req->$match);
                var_dump($match);
            }

            # is_featured mismatch
            if ($req->is_featured && !$ns->is_featured || !$ns->is_featured_at_location) {
                unset($req->$match);
            }

            # is_cancelled mismatch
            # Note default logic: hide cancelled
            if (!$req->is_cancelled && $ns->is_cancelled) {
                unset($req->$match);
            }
        }
    }

    var_dump($req->matches);
    return $req->matches;
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
filter_categories($req, $rss);
filter_options($req, $rss);
echo '</pre>';
