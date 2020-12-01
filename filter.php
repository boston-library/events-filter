<?php

# Force timezone and charset
date_default_timezone_set('EST');
ini_set('default_charset', 'UTF-8');
require_once 'autoload.php';

# https://github.com/dg/rss-php
Feed::$cacheDir = 'tmp';
Feed::$cacheExpire = '+1 hour';
$URI = 'https://bpl.bibliocommons.com/events/rss/all';
$Feed = Feed::loadRss($URI);

# These functions handle objects
$Parse = new Parse();
$req = $Parse->arrayObject($_GET);
$req = $Parse->getDates($req);
$req = $Parse->getCategories($req);

# These handle arrays

# CONTINUE HERE

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
function rss2ics($matches = [], $method = '')
{
    $json = array_map('json_encode', $matches);
    $output = [];

    foreach ($matches as $match) {
        $ns = $match->children('bc', true);

        /**
         * If no callback is supplied,
         * all empty entries of array will be removed
         * @see https://www.php.net/manual/en/function.array-filter.php
         */
        $organizer = implode(
            "\n",
            array_filter([
                $ns->{'contact'}->{'name'},
                $ns->{'contact'}->{'phone'},
                $ns->{'contact'}->{'email'},
            ])
        );

        $parameters = [
            'start' => strval($ns->{'start_date'}),
            'end' => strval($ns->{'end_date'}),
            'summary' => strval($match->title),
            'description' => strval($match->description),
            # todo: Fix the location property
            #'location' => ($req->is_virtual) ? 'Online' : 'NEED TO FIND LOCATION',
            'url' => strval($match->link),
            'organizer' => strval($organizer),
        ];

        $event = new CalendarEvent($parameters);
        array_push($output, $event);
    }

    $r = new Calendar(['events' => $output]);
    switch ($method) {
        case 'download':
            return $r->generateDownload();
            break;

        case 'ical':
            return $r->generateString();
            break;

        case 'html':
            break;

        default:
            throw new InvalidArgumentException(
                '$method must be one of download, ical, or html'
            );
            break;

    }

    return false;
}


/**
 * Debug
 */

echo '<pre>';
#$matches = filter_options($req, $Feed);
#var_dump($matches);
#var_dump(rss2ics($matches, 'download'));
echo '</pre>';

if (isset($_GET['download'])) {
    rss2ics($matches, 'download');
}

?>


<a
    href="filter.php?<?= $_SERVER['QUERY_STRING'] ?>?download">Download
    ICS</a>