<?php
declare(strict_types=1);

require_once 'autoload.php';

/**
 * formatDate()
 */
function formatDate($date)
{
    $f = '%Y%m%dT%H%M%S';

    if (is_object($date)) {
        $date = strtotime(
            implode(
                '',
                get_object_vars($date)
            )
        );
    }

    if (is_int($date)) {
        return strftime($f, $date);
    } else {
        return strftime(
            $f,
            strtotime($date)
        );
    }
}

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

# The filtered feed
$Matches = new stdClass();
$Matches = $Parse->filterCategories($Request, $Feed, $Matches);
$Matches = $Parse->filterDates($Request, $Feed, $Matches);
$Matches = $Parse->filterOptions($Request, $Feed, $Matches);

# The output options
$Output = new Output();

# ICS download option
# Needs to run before HTML is sent
if (isset($_GET['download_ics'])) {
    $Output->rss2ics($Matches, true);
}

# RSS subscribe option
if (isset($_GET['subscribe_rss'])) {
    $FeedPublish = new FeedPublish();
    $FeedPublish->open();
    $FeedPublish->channel();

    foreach ($Matches as $Match) {
        $FeedPublish->item($Match);
    }

    $FeedPublish->close();
}


# Plain HTML output option
# Used to embed an unstyled table, e.g., in an iframe
if (isset($_GET['plain_html'])) {
    echo $Output->rss2ics($Matches, $Download = false, $HTML = true);
} elseif (isset($_GET['download_ics']) || isset($_GET['subscribe_rss'])) {
    die();
} else {
    $QueryString = $_SERVER['QUERY_STRING'];
    require_once 'templates/header.php';

    echo $HTML = <<<HTML
    <p>
      <a href="filter.php?download_ics&$QueryString"
        target="_blank">Download ICS</a>
      &ensp; | &ensp;
      <a href="filter.php?subscribe_rss&$QueryString"
        target="_blank">Subscribe RSS</a>
      &ensp; | &ensp;
      <a href="filter.php?plain_html&$QueryString"
        target="_blank">Embeddable HTML</a>
      &ensp; | &ensp;
      <a href="/events-filter">New Search</a>
    </p>
HTML;

    echo $Output->rss2ics($Matches, $Download = false, $HTML = true);
    require_once 'templates/footer.php';
}
