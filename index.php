<?php

require_once 'autoload.php';
$Metadata = new Metadata();
?>

<!doctype html>
<html lang='en'>

<head>
  <title>Export Events | Boston Public Library</title>
  <meta charset='utf-8'>

  <style>
    fieldset {
      margin-bottom: 1em;
    }
  </style>
</head>

<body>


  <form action='filter.php' method='get'>

    <!-- Date -->
    <fieldset>
      <legend>Date</legend>

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
        min='<?= strftime('%Y-%m-%d', strtotime('today')) ?>'
        value='' /><br />

      To
      <input type='date' name='end_date' id='end_date'
        max='<?= strftime('%Y-%m-%d', strtotime('+1 year')) ?>'
        value='' /><br />
    </fieldset>

    <!-- Locations -->
    <fieldset>
      <legend>Locations</legend>
      <?= $Metadata->checkboxes('Locations', $Metadata->Locations) ?>
    </fieldset>

    <!-- Event Types -->
    <fieldset>
      <legend>Event Types</legend>
      <?= $Metadata->checkboxes('Event Types', $Metadata->EventTypes) ?>
    </fieldset>

    <!-- Programs -->
    <fieldset>
      <legend>Programs</legend>
      <?= $Metadata->checkboxes('Programs', $Metadata->Programs) ?>
    </fieldset>

    <!-- Audiences -->
    <fieldset>
      <legend>Audiences</legend>
      <?= $Metadata->checkboxes('Audiences', $Metadata->Audiences) ?>
    </fieldset>

    <!-- Languages -->
    <fieldset>
      <legend>Languages</legend>
      <?= $Metadata->checkboxes('Languages', $Metadata->Languages) ?>
    </fieldset>

    <!-- Options -->
    <fieldset>
      <legend>Filter By</legend>

      <input type="checkbox" id="is_virtual" name="is_virtual" value="true" checked />
      <label for="is_virtual">Only Online Events</label><br />

      <input type="checkbox" id="is_featured" name="is_featured" value="true" />
      <label for="is_featured">Only Featured Events</label><br />

      <input type="checkbox" id="is_cancelled" name="is_cancelled" value="true" />
      <label for="is_cancelled">Include Cancelled Events</label><br />
    </fieldset>

    <input type='submit' value='Submit'>
  </form>


</body>

</html>