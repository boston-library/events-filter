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

# The output options
$Output = new Output();

# Download link option
# Needs to run before HTML is sent
if (isset($_GET['download'])) {
    $Output->rss2ics($Matches, true);
}

/*
echo '<pre>';
echo "\$Output contents\n\n";
var_dump($Output->rss2ics($Matches));
echo '</pre>';
*/
?>

<!doctype html>
<html lang='en'>

<head>
    <title>Export Events | Boston Public Library</title>
    <meta charset='utf-8'>

    <style>
        tr,
        th,
        td {
            border: 1px solid black;
            padding: 0.25em 0.5em;
        }
    </style>
</head>

<body>

    <p>
        <a href="filter.php?download&<?=$_SERVER['QUERY_STRING']?>"
            target="_blank">Download ICS</a>
        &ensp; | &ensp;
        <a href="/" target="_blank">New Search</a>
    </p>

    <?=$Output->rss2ics($Matches, $Download = false, $HTML = true)?>

</body>

</html>