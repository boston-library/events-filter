<?php

/**
 * Output HTML, iCal, and CSV
 */

class Output
{
    /**
     * rss2ics()
     */
    public function rss2ics($Matches, $Download = false)
    {
        $Metadata = new Metadata();
        $Parse = new Parse();
        $Output = [];

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
            $Parameters = [
                'start' => strval($Match->start_date),
                'end' => strval($Match->end_date),
                'summary' => strval($Match->title),
                'description' => strval($Match->description),
                'location' => strval($Location),
                'url' => strval($Match->link),
                'contact' => strval($Organizer),
                'geo' => strval($Coordinates),
                'categories' => strval($CatString),
            ];

            $Event = new CalendarEvent($Parameters);
            array_push($Output, $Event);
        }

        $R = new Calendar(['events' => $Output]);
        return ($Download === true)
            ? $R->generateDownload()
            : $R->generateString();
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
            <th>Category</th>
            <th>Organizer</th>
          </tr>
HTML;

        foreach ($Matches as $Match) {
            $ns = $Match->children('bc', true);

            $HTML .= <<<HTML
            <tr>
              <td>$Match->title</td>
              <td>$ns->start_date</td>
              <td>$ns->end_date</td>
              <td>$ns->location</td>
              <td>$Match->category</td>
              <td>$Match->Organizer</td>
            </tr>
HTML;
        }

        $HTML .= '</table>';
        return $HTML;
    }
}
