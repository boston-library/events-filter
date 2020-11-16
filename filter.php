<?php

# Force local timezone
date_default_timezone_set('EST');
ini_set('default_charset', 'UTF-8');

/**
 * Libraries
 */

# https://github.com/dg/rss-php
require_once 'lib/feed.php';
Feed::$cacheDir = 'tmp';
Feed::$cacheExpire = '+1 hour';

$url = 'https://bpl.bibliocommons.com/events/rss/all';
$rss = Feed::loadRss($url);

# https://gist.github.com/pamelafox-coursera/5359246
require_once 'lib/CalendarHelper.class.php';

# https://github.com/Frimkron/PHPCalFeed
#require_once 'lib/calendar/calendar.php';

/*
# https://github.com/zcontent/icalendar
require_once 'lib/zapcallib.php';
$ics = new ZCiCal();
*/

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
    $req->date_radio = (isset($req->date_radio)) ? $req->date_radio : false;

    # Common useful dates
    # todo: Move to Metadata class
    $f = 'c';
    $today = date($f);
    $tomorrow = date($f, strtotime('+1 day'));
    $this_saturday = date($f, strtotime('next saturday'));
    $this_sunday = date($f, strtotime('next sunday'));
    $next_week = date($f, strtotime('+7 days'));
    $next_year = date($f, strtotime('+1 year'));

    # Radio button options
    # Only applied absent date entry
    if (!empty($req->start_date) || !empty($req->end_date)) {
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
function set_categories($req)
{
    $req->category = [];
    $regex = '/^category_.{4}$/';

    foreach ($req as $k => $v) {
        if (preg_match($regex, $k)) {
            $v_utf8 = quoted_printable_decode($v);
            array_push($req->category, $v_utf8);
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
 * The current order is:
 *  - get_categories() returns $out with all matching checkboxes,
 *    or all events if nothing is checked
 *  - filter_date() returns $out within the $req date period
 * - filter_options() returns $out if certain boolean conditions match
 *
 * todo: Split into 3 clear mini-functions
 * todo: Move to the Parse class
 */

# Compare $req and $rss
# Probs not called directly in production
# todo: Clarify scoping/privacy with a class
function get_categories($req, $rss)
{
    # Output array
    $out = [];

    if (empty($req->category)) {
        # Add all possible matches
        foreach ($rss->item as $event) {
            array_push($out, $event);
        }
    } else {
        # Add checked categories only
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

# todo: Clarify the filter function relationships
function filter_date($req, $rss)
{
    # IO arrays
    $in = get_categories($req, $rss);
    $out = [];

    # $req dates
    #$start_date = new DateTime($req->start_date);
    #$end_date = new DateTime($req->end_date);
    $start_date = strtotime($req->start_date);
    $end_date = strtotime($req->end_date);

    # Add matches to $out
    foreach ($in as $match) {
        # Define namespace
        $ns = $match->children('bc', true);
        #$event_date = new DateTime($ns->{'start_date'});
        $event_date = strtotime($ns->{'start_date'});

        if (between_dates($event_date, $start_date, $end_date)) {
            array_push($out, $match);
        }
    }

    return (array) $out;
}

/**
 * https://stackoverflow.com/a/9065661
 */
function between_dates($date, $start, $end)
{
    # Add +1 day to $end in seconds
    return (($date >= $start) && ($date <= $end + 86400));
}

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
    $in = filter_date($req, $rss);
    $out = [];

    # Prevent warnings
    $req->is_virtual = (isset($req->is_virtual)) ?: false;
    $req->is_featured = (isset($req->is_featured)) ?: false;
    $req->is_cancelled = (isset($req->is_cancelled)) ?: false;

    # Add matches to $out
    if (!empty($in)) {
        foreach ($in as $k => $match) {
            # Define namespace
            $ns = $match->children('bc', true);
            #var_dump(search_namespace('is_virtual', $match));

            # Boolean variables
            # todo: Don't rely on loose equality
            # https://www.php.net/manual/en/types.comparisons.php
            $is_virtual = ($ns->{'is_virtual'} == 'true') ? true : false;
            $is_featured = ($ns->{'is_featured'} == 'true') ? true : false;
            $is_featured_at_location = ($ns->{'is_featured_at_location'} == 'true') ? true : false;
            $is_cancelled = ($ns->{'is_cancelled'} == 'true') ? true : false;

            # Virtual and featured
            if ($req->is_virtual && $req->is_featured === true) {
                if ($is_virtual && ($is_featured || $is_featured_at_location) === true) {
                    array_push($out, $match);
                }
            } else {
                # Virtual only
                if ($req->is_virtual && $is_virtual === true) {
                    array_push($out, $match);
                }

                # Featured only
                if (($req->is_featured && ($is_featured || $is_featured_at_location) === true)) {
                    array_push($out, $match);
                }
            }

            # Hide cancelled unless checked
            if ($req->is_cancelled === false && $is_cancelled === true) {
                array_pop($out);
            }
        }
    }

    #var_dump($out);
    return (array) $out;
    #return (object) $out;
}


/**
 * Output HTML, iCal, and CSV
 * Expected JSON input:
 *
 * {
 *  "name": "Mark's Calendar",
 *   "events": [
 *     {
 *       "name": "Super Fun Party",
 *       "date": "2013-02-28",
 *       "time": "20:30",
 *       "duration": "4h 30m"
 *     },
 *     {
 *       "name": "How to be Awesome - A Lecture",
 *       "date": "2013-09-10",
 *       "description": "A talk about how to be more awesome.",
 *       "url": "http://example.com/awesome"
 *     }
 *   ],
 *   "recurring-events": [
 *     {
 *       "name": "Ada Lovelace Day",
 *       "recurrence": "yearly on 256th day",
 *       "description": "Celebrating the world's first computer programmer"
 *     }
 *   ]
 * }
 */


function rss2ics($matches)
{
    $json = array_map('json_encode', $matches);

    foreach ($matches as $match) {
        $ns = $match->children('bc', true);

        $parameters = [
            'start' => strval($ns->{'start_date'}),
            'end' => strval($ns->{'end_date'}),
            'summary' => strval($match->title),
            'description' => strval($match->description),
            'location' => '',
            'url' => strval($match->link),
        ];
        var_dump($parameters);

        $event = new CalendarEvent($parameters);
        var_dump($event);

        #$event->addNode(new ZCiCalDataNode("DTSTART:" . $ns->{'start_date'}));
        #$event->addNode(new ZCiCalDataNode("DTEND:" . $ns->{'end_date'}));
    }
    #var_dump($event->export());
    #return $event->export();
}


/**
 * Debug
 */

echo '<pre>';
$matches = filter_options($req, $rss);
var_dump($matches);
var_dump(rss2ics($matches));
echo '</pre>';
