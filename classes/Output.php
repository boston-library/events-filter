<?php
declare(strict_types=1);

/**
 * Output HTML, iCal, and CSV
 */

class Output
{
    /**
     * rss2ics()
     * todo: rename
     */
    public function rss2ics($Matches = null, $Download = false, $HTML = false)
    {
        # Quickly
        if (!$Matches || empty($Matches)) {
            return trigger_error(
                '$Matches must exist',
                E_USER_ERROR
            );
        }

        # Autoload
        $Metadata = new Metadata();
        $Parse = new Parse();

        # Since we're already transforming the Feed objects for CalendarEvent,
        # we might as well prepare an array for htmlTable() as the same time
        $OutputICS = [];
        $OutputHTML = [];

        foreach ($Matches as $Match) {
            $ns = $Match->children('bc', true);

            /**
             * $Organizer
             *
             * If no callback is supplied,
             * all empty entries of array will be removed
             * @see https://www.php.net/manual/en/function.array-filter.php
             */
            $Organizer = 'MAILTO:'.$ns->{'contact'}->{'email'};

            /*
            $Organizer = implode(
                "\n",
                array_filter([
                    $ns->{'contact'}->{'name'},
                    $ns->{'contact'}->{'phone'},
                    $ns->{'contact'}->{'email'},
                ])
            );
            */

            # $Location
            $Location =
            (trim(
                $L = implode(
                    ', ',
                    array_filter([
                        $ns->{'location'}->{'name'},
                        $ns->{'location'}->{'number'}.' '.$ns->{'location'}->{'street'},
                        $ns->{'location'}->{'city'}.', '.$ns->{'location'}->{'state'}.' '.$ns->{'location'}->{'zip'},
                    ])
                )
            # Failed implode implies online event
            # todo: Find a less fragile condition to test
            ) !== ', ,')
            ? $L
            : 'Online Event';

            # $Coordinates
            # Already empty if nonexistent
            $Coordinates = implode(
                ";",
                array_filter([
                    $ns->{'location'}->{'latitude'},
                    $ns->{'location'}->{'longitude'},
                ])
            );

            # $Categories
            $Categories = [];
            foreach (get_object_vars($Match->category) as $Category) {
                array_push($Categories, $Category);
            }
        
            array_shift($Categories);
            $CatString = implode(
                ', ',
                $Categories
            );

            # Set the params for CalendarEvent
            $ParametersICS = [
                # https://icalendar.org/iCalendar-RFC-5545/3-3-5-date-time.html
                'start' => strval(
                    strftime(
                        '%Y%m%dT%H%M%S',
                        strtotime($ns->start_date)
                    )
                ),

                'end' => strval(
                    strftime(
                        '%Y%m%dT%H%M%S',
                        strtotime($ns->end_date)
                    )
                ),

                'summary' => strval($Match->title),
                'description' => strval($Match->description),
                'location' => strval($Location),
                'url' => strval($Match->link),
                'contact' => strval($Organizer),
                'geo' => strval($Coordinates),
                'categories' => strval($CatString),
            ];

            $Event = new CalendarEvent($ParametersICS);
            array_push($OutputICS, $Event);

            # Set the params for htmlTable()
            # todo: Update after seeing what format Lisa wants
            $ParametersHTML = [
                'start' => strftime(
                    '%c',
                    strtotime($ns->start_date)
                ),
                'end' => strftime(
                    '%c',
                    strtotime($ns->end_date)
                ),

                'title' => strval($Match->title),
                'description' => strval($Match->description),
                'location' => strval($Location),
                'url' => strval($Match->link),
                'contact' => strval($Organizer),
                'categories' => strval($CatString),
            ];
            
            array_push($OutputHTML, $ParametersHTML);
        }

        /**
         * Compact ternary return switch workings:
         *   if $HTML, $this->htmlTable()
         *   elseif $Download, Calendar->generateDownload()
         *   else Calendar->generateString()
         */
        $R = new Calendar(['events' => $OutputICS]);
        return ($HTML === true)
            ? $this->htmlTable($OutputHTML)
            : (($Download === true)
                ? $R->generateDownload()
                : $R->generateString());
    }

    /**
     * htmlTable()
     */
    public function htmlTable($Matches)
    {
        $HTML = <<<HTML
        <table>
          <tr>
            <th>Title</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Location</th>
            <th>Categories</th>
            <th>Organizer</th>
          </tr>
HTML;

        foreach ($Matches as $Match) {
            # Format event title as link to event
            $Link = '<a href="'.$Match['url'].'" target="_blank">'.$Match['title'].'</a>';

            # Get raw email for display below
            $Contact = preg_replace(
                '/^MAILTO\:/i',
                '',
                $Match['contact']
            );

            $HTML .= <<<HTML
            <tr>
              <td>$Link</td>
              <td>{$Match['start']}</td>
              <td>{$Match['end']}</td>
              <td>{$Match['location']}</td>
              <td>{$Match['categories']}</td>
              <td>
                <a href="mailto:$Contact">$Contact</a>
              </td>
            </tr>
HTML;
        }

        $HTML .= '</table>';
        return $HTML;
    }
}
