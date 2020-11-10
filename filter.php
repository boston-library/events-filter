<?php

# Force local timezone
date_default_timezone_set('EST');

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
function set_date(object $req)
{
    # Prevent warnings
    $req->date_radio = (isset($req->date_radio)) ?: false;

    # Common useful dates
    # todo: Move to Metadata class
    $f = 'c';
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
    return (object) $req;
}

# Set the categories (all form checkboxes)
# Strips the bin2hex() suffixes and adds $req->category
function set_categories(object $req)
{
    $req->category = [];
    $regex = '/^category_.{4}$/';

    foreach ($req as $k => $v) {
        if (preg_match($regex, $k)) {
            array_push($req->category, $v);
            unset($req->$k);
        }
    }

    return (object) $req;
}


/**
 * Find matches
 *
 * Note the new object instead of $req
 *
 * todo: Split into 3 clear mini-functions
 * todo: Move to the Parse class
 */

# Compare $req and $rss
# Probs not called directly in production
# todo: Clarify scoping/privacy with a class
function get_categories(object $req, object $rss)
{
    # Output array
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
function filter_options(object $req, object $rss)
{
    # IO arrays
    $in = get_categories($req, $rss);
    $out = [];

    # Prevent warnings
    $req->is_virtual = (isset($req->is_virtual)) ?: false;
    $req->is_featured = (isset($req->is_featured)) ?: false;
    $req->is_cancelled = (isset($req->is_cancelled)) ?: false;

    # Add matches to $out
    if (!empty($in)) {
        foreach ($in as $match) {
            # Define namespace
            $ns = $match->children('bc', true);
            #var_dump(search_namespace('is_virtual', $match));

            # todo: Don't rely on loose equality
            # https://www.php.net/manual/en/types.comparisons.php

            # Filter for virtual events
            $is_virtual = ($ns->{'is_virtual'} == 'true') ? true : false;
            if ($req->is_virtual === true && $is_virtual === true) {
                array_push($out, $match);
                #unset($match);
            }

            # Filter for featured events
            $is_featured = ($ns->{'is_featured'} == 'true') ? true : false;
            $is_featured_at_location = ($ns->{'is_featured_at_location'} == 'true') ? true : false;
            if (($req->is_featured === true && $is_featured === true)
             || ($req->is_featured === true && $is_featured_at_location === true)) {
                array_push($out, $match);
                #unset($match);
            }

            /*
            # Hide cancelled unless checked
            $is_cancelled = ($ns->{'is_cancelled'} == 'true') ? true : false;
            if ($req->is_cancelled && $is_cancelled) {
                array_push($out, $match);
            } else {
                if (!$is_cancelled) {
                    array_push($out, $match);
                }
            }
            */
        }
    }

    #var_dump($out);
    return (array) $out;
}

# todo: Clarify the filter function relationships
function filter_date(object $req, object $rss)
{
    # IO arrays
    $in = filter_options($req, $rss);
    $out = [];

    # Add matches to $out
    if (!empty($in)) {
        foreach ($in as $match) {
            # Define namespace
            $ns = $match->children('bc', true);
            if ($ns->{'start_date'}) {
                #echo 'yes';
                $event_date = new DateTime($ns->{'start_date'});
                $start_date = new DateTime($req->start_date);
                $end_date = new DateTime($req->end_date);
                #$interval = new DatePeriod($start_date, $end_date);
                var_dump(between_dates($event_date, $start_date, $end_date));
            }
        }
    }
}

/**
 * https://stackoverflow.com/a/9065661
 */
function between_dates(DateTime $date, DateTime $start, DateTime $end)
{
    return (bool) $date > $start && $date < $end;
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
$matches = filter_options($req, $rss);

var_dump(filter_date($req, $rss));
#filter_options($req, $rss);
echo '</pre>';
