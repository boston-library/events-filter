<?php
declare(strict_types=1);

/**
 * Adapted from
 * https://gist.github.com/pamelafox-coursera/5359246
 *
 * @see https://en.wikipedia.org/wiki/ICalendar
 */

class CalendarEvent
{
    /**
     * The event ID
     * @var string
     */
    private $uid;

    /**
     * The event start date
     * @var DateTime
     */
    private $start;

    /**
     * The event end date
     * @var DateTime
     */
    private $end;

    /**
     * The event title
     * @var string
     */
    private $summary;

    /**
     * The event description
     * @var string
     */
    private $description;

    /**
     * The event location
     * @var string
     */
    private $location;

    /**
     * The event contact
     * @var string
     */
    private $contact;


    /**
     * formatValue()
     *
     * Escape commas, semi-colons, backslashes
     * @see https://stackoverflow.com/q/1590368
     */
    private function formatValue($str)
    {
        return trim(
            addcslashes(
                strip_tags($str),
                ",\\;"
            )
        );
    }


    /**
     * new CalendarEvent()
     */
    public function __construct($parameters)
    {
        $parameters += array(
          'summary' => 'Untitled Event',
          'description' => '',
          'location' => '',
          'organizer' => '',
          'geo' => '',
          'categories' => '',
        );

        if (isset($parameters['uid'])) {
            $this->uid = $parameters['uid'];
        } else {
            $this->uid = uniqid('', true);
            #$this->uid = uniqid(rand(0, getmypid()));
        }

        $this->start = $parameters['start'];
        $this->end = $parameters['end'];
        $this->summary = $parameters['summary'];
        $this->description = $parameters['description'];
        $this->location = $parameters['location'];

        # Custom fields
        $this->contact = $parameters['contact'];
        $this->url = $parameters['url'];
        $this->geo = $parameters['geo'];
        $this->categories = $parameters['categories'];

        return $this;
    }

    
    /**
     * generateString()
     */
    public function generateString()
    {
        $now = formatDate(strtotime('now'));
        $content = "BEGIN:VEVENT\n"
                 . "UID:{$this->uid}\n"
                 . "DTSTART;TZID=America/New_York:{$this->start}\n"
                 . "DTEND;TZID=America/New_York:{$this->end}\n"
                 . "DTSTAMP;TZID=America/New_York:{$now}\n"
                 . "CREATED;TZID=America/New_York:{$now}\n"
                 . "DESCRIPTION:{$this->formatValue($this->description)}\n"
                 . "LAST-MODIFIED:{$now}\n"
                 . "LOCATION:{$this->location}\n"

                 # Custom fields
                 . "URL:{$this->url}\n"
                 . "ORGANIZER:{$this->contact}\n"
                 . "GEO:{$this->geo}\n"
                 . "CATEGORIES:{$this->categories}\n"

                 . "SUMMARY:{$this->formatValue($this->summary)}\n"
                 . "SEQUENCE:0\n"
                 . "STATUS:CONFIRMED\n"
                 . "TRANSP:OPAQUE\n"
                 . "END:VEVENT\n";
        return $content;
    }
}
