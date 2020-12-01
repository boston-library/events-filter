<?php

/**
 * Adapted from
 * https://gist.github.com/pamelafox-coursera/5359246
 */

class Calendar
{
    protected $events;
    protected $title;
    protected $author;

    public function __construct($parameters)
    {
        $parameters += array(
          'events' => array(),
          'title' => 'Event Export',
          'author' => 'Boston Public Library'
        );

        $this->events = $parameters['events'];
        $this->title  = $parameters['title'];
        $this->author = $parameters['author'];
    }


    /**
     * Call this function to download the invite
     */
    public function generateDownload()
    {
        $generated = $this->generateString();
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // tell it we just updated
        header('Cache-Control: no-store, no-cache, must-revalidate'); // force revaidation
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Content-type: text/calendar; charset=utf-8');
        header('Content-Disposition: inline; filename="calendar.ics"');
        header("Content-Description: File Transfer");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . strlen($generated));
        print $generated;
    }


    /**
     * generateString()
     *
     * The function generates the actual content of the ICS
     * file and returns it.
     *
     * @return string|bool
     */
    public function generateString()
    {
        $content = "BEGIN:VCALENDAR\n"
                 . "VERSION:2.0\n"
                 . "PRODID:-//" . $this->author . "//NONSGML//EN\n"
                 . "X-WR-CALNAME:" . $this->title . "\n"
                 . "CALSCALE:GREGORIAN\n";

        foreach ($this->events as $event) {
            $content .= $event->generateString();
        }
        
        $content .= "END:VCALENDAR";
        return $content;
    }
}
