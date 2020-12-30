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

# Download link option
# Needs to run before HTML is sent
if (isset($_GET['download'])) {
    $Output->rss2ics($Matches, true);
}

require_once 'templates/header.php';
?>

    <p>
        <a href="filter.php?download&<?=$_SERVER['QUERY_STRING']?>"
            target="_blank">Download ICS</a>
        &ensp; | &ensp;
        <a href="/events-filter">New Search</a>
    </p>
    <?= $Output->rss2ics($Matches, $Download = false, $HTML = true) ?>

<?php require_once 'templates/footer.php';