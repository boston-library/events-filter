<?php

/**
 * Adapted from
 * https://gist.github.com/pamelafox-coursera/5359246
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
     * new CalendarEvent()
     */
    public function __construct($parameters)
    {
        $parameters += array(
          'summary' => 'Untitled Event',
          'description' => '',
          'location' => '',
          'organizer' => '',
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
        $this->contact = $parameters['organizer'];
        $this->url = $parameters['url'];

        return $this;
    }


    /**
     * formatDate()
     *
     * Get the start time set for the event
     * @return string
     */
    private function formatDate($date)
    {
        return date("Ymd\THis\Z");
        #return $date->format("Ymd\THis\Z");
    }


    /**
     * formatValue()
     *
     * Escape commas, semi-colons, backslashes
     * @see https://stackoverflow.com/q/1590368
     */
    private function formatValue($str)
    {
        return addcslashes($str, ",\\;");
    }


    /**
     * generateString()
     */
    public function generateString()
    {
        $created = new DateTime();
        $content = '';

        $content = "BEGIN:VEVENT\r\n"
                 . "UID:{$this->uid}\r\n"
                 . "DTSTART:{$this->formatDate($this->start)}\r\n"
                 . "DTEND:{$this->formatDate($this->end)}\r\n"
                 . "DTSTAMP:{$this->formatDate($this->start)}\r\n"
                 . "CREATED:{$this->formatDate($created)}\r\n"
                 . "DESCRIPTION:{$this->formatValue($this->description)}\r\n"
                 . "LAST-MODIFIED:{$this->formatDate($this->start)}\r\n"
                 . "LOCATION:{$this->location}\r\n"

                 # Custom fields
                 . "URL:{$this->url}\r\n"
                 . "ORGANIZER:{$this->contact}\r\n"

                 . "SUMMARY:{$this->formatValue($this->summary)}\r\n"
                 . "SEQUENCE:0\r\n"
                 . "STATUS:CONFIRMED\r\n"
                 . "TRANSP:OPAQUE\r\n"
                 . "END:VEVENT\r\n";
        return $content;
    }
}
