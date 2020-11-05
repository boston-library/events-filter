<?php

require_once 'lib/bpl_data.php';
$data = new BplData();

?>

<!doctype html>
<html lang='en'>

<head>
  <title>Export Events | Boston Public Library</title>
  <meta charset='utf-8'>
</head>

<body>


  <form action='handler.php' method='post'>

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
    <?= $data->make_checkboxes('Locations', $data->locations) ?>

    <!-- Event Types -->
    <h2>Event Types</h2>
    <?= $data->make_checkboxes('Event Types', $data->event_types) ?>

    <!-- Programs -->
    <h2>Programs</h2>
    <?= $data->make_checkboxes('Programs', $data->programs) ?>

    <!-- Audiences -->
    <h2>Audiences</h2>
    <?= $data->make_checkboxes('Audiences', $data->audiences) ?>

    <!-- Languages -->
    <h2>Languages</h2>
    <?= $data->make_checkboxes('Languages', $data->languages) ?>

    <!-- Options -->
    <h2>Options</h2>

    <input type="checkbox" id="is_virtual" name="is_virtual" value="true" />
    <label for="is_virtual">Online Events</label><br />

    <input type="checkbox" id="is_cancelled" name="is_cancelled" value="true" />
    <label for="is_cancelled">Include Cancelled</label><br />

    <input type="checkbox" id="is_featured" name="is_featured" value="true" />
    <label for="is_featured">Featured Events</label><br />

    <!--
    <input type="checkbox"
      id="is_featured_at_location" name="is_featured_at_location"
      value="true" />
    <label for="is_featured_at_location">Show Featured at Location</label><br />
    -->

    <input type='submit' value='Submit'>
  </form>


</body>

</html>