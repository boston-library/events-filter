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
 * Takes a raw $_GET superglobal and returns a legible object
 * todo: Move to its own class
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

    return $req;
}


/**
 * Find matches
 * todo: Split into 3 clear mini-functions
 * todo: Move to the Parse class
 */

# Compare $req and $rss
# Probs not called directly in production
# todo: Clarify scoping/privacy with a class
function get_categories($req, $rss)
{
    # Collect output in new array
    $out = [];

    # Add all possible matches
    if (empty($req->category)) {
        foreach ($rss->item as $event) {
            array_push($out, $event);
        }
    } else {
        foreach ($req->category as $filter) {
            foreach ($rss->item as $event) {
                if (in_array($filter, get_object_vars($event->category))) {
                    array_push($out, $event);
                }
            }
        }
    }

    #return (object) $out;
    return (array) $out;
}

/*
# todo: Make this work
function search_namespace($needle, $haystack)
{
    return (!is_object($haystack))
        ? false
        : (
            function ($haystack) {
                $ns = $haystack->children('bc', true);
                return $ns->{$needle};
            }
        );
}
*/

/**
 * Filter by radio buttons
 *
 * It should be:
 *  - online checked = only online
 *  - online unchecked = all events
 *  - featured checked = only featured
 *  - featured unchecked = all events
 *  - hide cancelled by default
 *  - if cancelled checked, include
 *
 * todo: Clarify get_categories() relationship
 */
function filter_options($req, $rss)
{
    # IO arrays
    $in = get_categories($req, $rss);
    $out = [];

    # Prevent warnings
    $req->is_virtual = (isset($req->is_virtual)) ?: false;
    $req->is_featured = (isset($req->is_featured)) ?: false;
    $req->is_cancelled = (isset($req->is_cancelled)) ?: false;

    # Add matches to $out
    # https://stackoverflow.com/a/622363
    if (!empty($in)) {
        foreach ($in->item as $match) {
            # Define namespace
            #var_dump(search_namespace('is_virtual', $match));
            $ns = $match->children('bc', true);
            #var_dump($match);
            #var_dump($ns);

            $is_virtual = ($ns->{'is_virtual'} == 'true') ? true : false;
            if ($req->is_virtual === $is_virtual) {
                #array_push($out, $match);
                #var_dump($match);
            }

            $is_featured = ($ns->{'is_featured'} == 'true') ? true : false;
            $is_featured_at_location = ($ns->{'is_featured_at_location'} == 'true') ? true : false;
            if ($req->is_featured === $is_featured
            || $req->is_featured === $is_featured_at_location) {
                #var_dump($match);
                array_push($out, $match);
            }



            # Cast string to bool for comparisons
            # todo: Don't rely on loose equality
            # https://www.php.net/manual/en/types.comparisons.php
            /*
            $is_virtual = ($ns->{'is_virtual'} = 'true') ? true : false;
            $is_featured = ($ns->{'is_featured'} = 'true'
                || $ns->{'is_featured_at_location'} = 'true') ? true : false;
            $is_cancelled = ($ns->{'is_cancelled'} = 'true') ? true : false;

            /*
            var_dump($req->is_featured);
            var_dump($is_featured);
            var_dump($ns->{'is_featured'});
            echo "\n\n";
            *

            # is_virtual match
            if ($req->is_virtual === $is_virtual) {
                array_push($out, $match);
            }

            /*
            # is_featured match
            if ($req->is_featured = $is_featured) {
                #var_dump($match);
                array_push($out, $match);
                var_dump($out);
            }
            *

            # is_cancelled mismatch
            # Note default logic: hide cancelled
            if ($req->is_cancelled !== $is_cancelled) {
                array_push($out, $match);
            }
            */
        }
    }

    var_dump($out);

    return (object) $out;
}

function filter_date($req, $rss)
{
    # todo
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
#var_dump($req->matches);
var_dump($rss);
#var_dump($matches);
#filter_options($req, $rss);
echo '</pre>';
