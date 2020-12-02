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

# The requested event properties
$Parse = new Parse();
$Request = $Parse->arrayObject($_GET);
$Request = $Parse->extractCategories($Request);
$Request = $Parse->extractDates($Request);

echo '<pre>';
echo "\$Request contents\n\n";
var_dump($Request);
echo '</pre>';


# The filtered feed
$Matches = new StdClass();
$Matches = $Parse->filterCategories($Request, $Feed, $Matches);
$Matches = $Parse->filterDates($Request, $Feed, $Matches);
$Matches = $Parse->filterOptions($Request, $Feed, $Matches);

echo '<pre>';
echo "\$Matches contents\n\n";
var_dump($Matches);
echo '</pre>';

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
    $Matchesput = [];

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
            #'location' => ($Request->is_virtual) ? 'Online' : (array_intersect()),
            'url' => strval($match->link),
            'organizer' => strval($organizer),
        ];

        $event = new CalendarEvent($parameters);
        array_push($Matchesput, $event);
    }

    $r = new Calendar(['events' => $Matchesput]);
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
?>

<!doctype html>
<html lang='en'>

<head>
    <title>Export Events | Boston Public Library</title>
    <meta charset='utf-8'>
</head>

<body>

DOWNLOAD LINK HERE<br />
TABLE HERE

</body>

</html>