<?php

require_once 'lib/metadata.php';
$meta = new Metadata();

?>

<!doctype html>
<html lang='en'>

<head>
  <title>Export Events | Boston Public Library</title>
  <meta charset='utf-8'>
</head>

<body>


  <form action='handler.php' method='get'>

    <!-- Date -->
    <h2>Date</h2>

    <input type='radio' name='date_radio' id='date_radio' value='today' />
    <label for='today'>Today</label><br />

    <input type='radio' name='date_radio' id='date_radio' value='tomorrow' />
    <label for='tomorrow'>Tomorrow</label><br />

    <input type='radio' name='date_radio' id='date_radio' value='this_weekend' />
    <label for='this_weekend'>This Weekend</label><br />

    <input type='radio' name='date_radio' id='date_radio' value='next_week' />
    <label for='next_week'>Next 7 Days</label><br />

    From
    <input type='date' name='start_date' id='start_date'
      min='<?= $today ?>' value='' /><br />

    To
    <input type='date' name='end_date' id='end_date'
      max='<?= strftime('%Y-%m-%d', strtotime('+1 year')) ?>'
      value='' /><br />

    <!-- Locations -->
    <h2>Locations</h2>
    <?= $meta->make_checkboxes('Locations', $meta->locations) ?>

    <!-- Event Types -->
    <h2>Event Types</h2>
    <?= $meta->make_checkboxes('Event Types', $meta->event_types) ?>

    <!-- Programs -->
    <h2>Programs</h2>
    <?= $meta->make_checkboxes('Programs', $meta->programs) ?>

    <!-- Audiences -->
    <h2>Audiences</h2>
    <?= $meta->make_checkboxes('Audiences', $meta->audiences) ?>

    <!-- Languages -->
    <h2>Languages</h2>
    <?= $meta->make_checkboxes('Languages', $meta->languages) ?>

    <!-- Options -->
    <h2>Filter By</h2>

    <input type="checkbox" id="is_virtual" name="is_virtual" value="true" />
    <label for="is_virtual">Online Events</label><br />

    <input type="checkbox" id="is_featured" name="is_featured" value="true" />
    <label for="is_featured">Featured Events</label><br />

    <input type="checkbox" id="is_cancelled" name="is_cancelled" value="true" />
    <label for="is_cancelled">Cancelled Events</label><br />

    <input type='submit' value='Submit'>
  </form>


</body>

</html>