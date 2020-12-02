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

/*
echo '<pre>';
echo "\$Request contents\n\n";
var_dump($Request);
echo '</pre>';
*/

# The filtered feed
$Matches = new stdClass();
$Matches = $Parse->filterCategories($Request, $Feed, $Matches);
$Matches = $Parse->filterDates($Request, $Feed, $Matches);
$Matches = $Parse->filterOptions($Request, $Feed, $Matches);

/*
echo '<pre>';
echo "\$Matches contents\n\n";
var_dump($Matches);
echo '</pre>';
*/

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
function rss2ics($Matches)
{
    $Metadata = new Metadata();
    $Parse = new Parse();
    $Output = [];

    foreach ($Matches as $Match) {
        $ns = $Match->children('bc', true);

        /**
         * $Organizer
         *
         * If no callback is supplied,
         * all empty entries of array will be removed
         * @see https://www.php.net/manual/en/function.array-filter.php
         */
        $Organizer = 'MAILTO:'.$ns->{'contact'}->{'email'};

        /*
        $Organizer = implode(
            "\n",
            array_filter([
                $ns->{'contact'}->{'name'},
                $ns->{'contact'}->{'phone'},
                $ns->{'contact'}->{'email'},
            ])
        );
        */

        # $Location
        $Location =
        (trim(
            $L = implode(
                ', ',
                array_filter([
                    $ns->{'location'}->{'name'},
                    $ns->{'location'}->{'number'}.' '.$ns->{'location'}->{'street'},
                    $ns->{'location'}->{'city'}.', '.$ns->{'location'}->{'state'}.' '.$ns->{'location'}->{'zip'},
                ])
            )
        # Failed implode implies online event
        # todo: Find a less fragile condition to test
        ) !== ', ,')
        ? $L
        : 'Online Event';

        # $Coordinates
        # Already empty if nonexistent
        $Coordinates = implode(
            ";",
            array_filter([
                $ns->{'location'}->{'latitude'},
                $ns->{'location'}->{'longitude'},
            ])
        );

        # $Categories
        $Categories = [];
        foreach (get_object_vars($Match->category) as $Category) {
            array_push($Categories, $Category);
        }
        
        array_shift($Categories);
        $CatString = implode(
            ', ',
            $Categories
        );

        # Set the params for CalendarEvent
        $Parameters = [
            'start' => strval($Match->start_date),
            'end' => strval($Match->end_date),
            'summary' => strval($Match->title),
            'description' => strval($Match->description),
            'location' => strval($Location),
            'url' => strval($Match->link),
            'contact' => strval($Organizer),
            'geo' => strval($Coordinates),
            'categories' => strval($CatString),
        ];

        $Event = new CalendarEvent($Parameters);
        array_push($Output, $Event);
    }

    $r = new Calendar(['events' => $Output]);
    return $r->generateString();

}
echo '<pre>';
var_dump(rss2ics($Matches));
echo '</pre>';
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