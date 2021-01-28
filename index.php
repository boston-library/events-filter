<?php
declare(strict_types=1);

require_once 'autoload.php';
$Metadata = new Metadata();

require_once 'templates/header.php';
?>

<form action='filter.php' method='get'>

  <!-- Date -->
  <fieldset style="margin-bottom: 1em;">
    <legend style="font-weight: bold;">Date</legend>

    <input type='radio' name='date_radio' id='date_radio' value='today' />
    <label for='today'>Today</label><br />

    <input type='radio' name='date_radio' id='date_radio' value='tomorrow' />
    <label for='tomorrow'>Tomorrow</label><br />

    <input type='radio' name='date_radio' id='date_radio' value='this_weekend' />
    <label for='this_weekend'>This Weekend</label><br />

    <input type='radio' name='date_radio' id='date_radio' value='next_week' />
    <label for='next_week'>Next 7 Days</label><br />

    <input type='radio' name='date_radio' id='date_radio' value='next_month' />
    <label for='next_month'>Next 30 Days</label><br />

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
  <fieldset style="margin-bottom: 1em;">
    <legend style="font-weight: bold;">Locations</legend>
    <?= $Metadata->checkboxes('Locations', $Metadata->Locations) ?>
  </fieldset>

  <!-- Event Types -->
  <fieldset style="margin-bottom: 1em;">
    <legend style="font-weight: bold;">Event Types</legend>
    <?= $Metadata->checkboxes('Event Types', $Metadata->EventTypes) ?>
  </fieldset>

  <!-- Programs -->
  <fieldset style="margin-bottom: 1em;">
    <legend style="font-weight: bold;">Programs</legend>
    <?= $Metadata->checkboxes('Programs', $Metadata->Programs) ?>
  </fieldset>

  <!-- Audiences -->
  <fieldset style="margin-bottom: 1em;">
    <legend style="font-weight: bold;">Audiences</legend>
    <?= $Metadata->checkboxes('Audiences', $Metadata->Audiences) ?>
  </fieldset>

  <!-- Languages -->
  <fieldset style="margin-bottom: 1em;">
    <legend style="font-weight: bold;">Languages</legend>
    <?= $Metadata->checkboxes('Languages', $Metadata->Languages) ?>
  </fieldset>

  <!-- Options -->
  <fieldset style="margin-bottom: 1em;">
    <legend style="font-weight: bold;">Filter By</legend>

    <input type="checkbox" id="is_virtual" name="is_virtual" value="true" checked />
    <label for="is_virtual">Only Online Events</label><br />

    <input type="checkbox" id="is_featured" name="is_featured" value="true" />
    <label for="is_featured">Only Featured Events</label><br />

    <input type="checkbox" id="is_cancelled" name="is_cancelled" value="true" />
    <label for="is_cancelled">Include Cancelled Events</label><br />
  </fieldset>

  <input type="submit" value="Submit" class="d-button d-button--solid-primary o-link--white" />
</form>

<?php require_once 'templates/footer.php';
