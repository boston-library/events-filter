<?php

class Parse
{
    /**
     * Array object toggle
     *
     * Recursively makes an array into an object,
     * and vice versa, depending on the input type.
     * Quick tests before wasting time on json_*code().
     */
    public function arrayObject($x = false)
    {
        return $y =
        (is_array($x) && !empty($x))
          ? json_decode(json_encode($x), false)
          : (
              (is_object($x) && !empty($x))
                ? json_decode(json_encode($x), true)
                : trigger_error(
                    '$x is neither oject nor array',
                    E_USER_ERROR
                )
          );
    }

    /*
    # todo: Make this work if deemed necessary
    public function search_namespace($needle, $haystack)
    {
        return (!is_object($haystack))
        ? false
        : (
            function ($haystack) {
                $ns = $haystack->children('bc', true);
                return $ns->{$needle};
            }
        );
    }
    */


    /**
     * Date functions
     */

    /**
     * https://stackoverflow.com/a/9065661
     */
    public function betweenDates($date, $start, $end)
    {
        # Add +1 day to $end in seconds
        return (($date >= $start) && ($date <= $end + 86400));
    }

    /**
     * extractDates()
     *
     * Set the start and end dates.
     *
     * @param object $Request The GET request
     * @return object $Request The modified object
     */
    public function extractDates($Request)
    {
        # Prevent warnings
        $Request->date_radio = (isset($Request->date_radio)) ? $Request->date_radio : false;

        # Common useful dates
        $f = 'c';
        $today = date($f);
        $tomorrow = date($f, strtotime('+1 day'));
        $this_saturday = date($f, strtotime('next saturday'));
        $this_sunday = date($f, strtotime('next sunday'));
        $next_week = date($f, strtotime('+7 days'));
        $next_year = date($f, strtotime('+1 year'));

        # Radio button options
        # Only applied absent date entry
        if (!empty($Request->start_date) || !empty($Request->end_date)) {
            # Partial manual date fallback
            $Request->start_date = ($Request->start_date) ?: $today;
            $Request->end_date = ($Request->end_date) ?: $next_year;
        } else {
            switch ($Request->date_radio) {
            case 'today':
                $Request->start_date = $today;
                $Request->end_date = $today;
                break;

            case 'tomorrow':
                $Request->start_date = $tomorrow;
                $Request->end_date = $tomorrow;
                break;

            case 'this_weekend':
                $Request->start_date = $this_saturday;
                $Request->end_date = $this_sunday;
                break;

            case 'next_week':
                $Request->start_date = $today;
                $Request->end_date = $next_week;
                break;

            default:
                $Request->start_date = $today;
                $Request->end_date = $next_year;
                break;
            }
        }

        unset($Request->date_radio);
        return $Request;
    }

    /**
     * filterDates()
     */
    public function filterDates($Request, $Feed, &$Matches)
    {
        # $Request dates
        $StartDate = strtotime($Request->start_date);
        $EndDate = strtotime($Request->end_date);

        # Add matches to $Matches
        foreach ($Matches as $k => $Event) {
        #foreach ($Feed->item as $k => $Event) {
            # Define namespace
            $ns = $Event->children('bc', true);
            $EventDate = strtotime($ns->{'start_date'});

            if (!$this->betweenDates($EventDate, $StartDate, $EndDate)) {
                unset($Matches->$k);
            }

            /*
            if ($this->betweenDates($EventDate, $StartDate, $EndDate)) {
                array_push($Matches, $Event);
            }
            */
        }

        return $Matches;
    }


    /**
     * Category functions
     */

    /**
     * extractCategories()
     *
     * Set the categories (all form checkboxes).
     * Strips the bin2hex() suffixes and adds $Request->category.
     *
     * @param object
     * @return object
     */
    public function extractCategories($Request)
    {
        $Request->category = [];
        $regex = '/^category_.{4}$/';

        foreach ($Request as $k => $v) {
            if (preg_match($regex, $k)) {
                $v_utf8 = quoted_printable_decode($v);
                array_push($Request->category, $v_utf8);
                unset($Request->$k);
            }
        }

        return $Request;
    }

    /**
     * Find matches
     *
     * Note the new object instead of $Request
     *
     * The current order is:
     *  - get_categories() returns $Matches with all matching checkboxes,
     *    or all events if nothing is checked
     *  - filter_date() returns $Matches within the $Request date period
     *  - filter_options() returns $Matches if certain boolean conditions match
     *
     * todo: Split into 3 clear mini-functions
     * todo: Move to the Parse class
     */
    public function filterCategories($Request, $Feed, &$Matches)
    {
        /*
        if (!$Request->category) {
            trigger_error(
                'Please run $this->extractCategories() to strip bin2hex() noise.',
                E_USER_ERROR
            );
        }
        */

        $Matches = $this->arrayObject($Matches);

        if (empty($Request->category)) {
            # Add all possible matches
            foreach ($Feed->item as $Event) {
                array_push($Matches, $Event);
            }
        } else {
            # Add checked categories only
            foreach ($Request->category as $Filter) {
                foreach ($Feed->item as $Event) {
                    if (in_array($Filter, get_object_vars($Event->category))) {
                        array_push($Matches, $Event);
                    }
                }
            }
        }

        return (object) $Matches;
    }


    /**
     * Options functions
     */

    /**
     * Filter by radio buttons
     *
     * It should be:
     *  - online checked = only online
     *  - online unchecked = all events
     *  - featured checked = only featured
     *  - featured unchecked = all events
     *  - hide cancelled by default
     *  - if cancelled checked, include
     *
     * todo: Clarify get_categories() relationship
     */
    public function filterOptions($Request, $Feed, &$Matches)
    {
        #$Matches = $this->arrayObject($Matches);

        # Prevent warnings
        $Request->is_virtual = (isset($Request->is_virtual)) ?: false;
        $Request->is_featured = (isset($Request->is_featured)) ?: false;
        $Request->is_cancelled = (isset($Request->is_cancelled)) ?: false;

        # Add matches to $Matches
        if (!empty($Matches)) {
            foreach ($Matches as $k => $Match) {
                # Define namespace
                $ns = $Match->children('bc', true);

                # Boolean variables
                # todo: Don't rely on loose equality
                # https://www.php.net/manual/en/types.comparisons.php
                $is_virtual = ($ns->{'is_virtual'} == 'true') ? true : false;
                $is_featured = ($ns->{'is_featured'} == 'true') ? true : false;
                $is_featured_at_location = ($ns->{'is_featured_at_location'} == 'true') ? true : false;
                $is_cancelled = ($ns->{'is_cancelled'} == 'true') ? true : false;

                # Virtual and featured
                # todo: Test for virtual featured
                if ($Request->is_virtual === true
                && $Request->is_featured === true) {
                    if ($is_virtual !== true
                    || ($is_featured || $is_featured_at_location) !== true) {
                        unset($Matches->$k);
                    }
                } else {
                    # Virtual only
                    if ($Request->is_virtual === true
                    && $is_virtual !== true) {
                        unset($Matches->$k);
                    }

                    # Featured only
                    if (($Request->is_featured === true
                    && ($is_featured || $is_featured_at_location) !== true)) {
                        unset($Matches->$k);
                    }
                }

                # Hide cancelled unless checked
                # Note reversed logic here
                if ($Request->is_cancelled !== true
                && $is_cancelled === true) {
                    unset($Matches->$k);
                }
            }
        }

        return $Matches;
    }
}
