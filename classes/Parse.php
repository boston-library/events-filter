<?php

class Parse
{
    /**
     * Array object toggle
     *
     * Recursively makes an array into an object,
     * and vice versa, depending on the input type.
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
    # todo: Make this work
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
     * getDates()
     *
     * Set the start and end dates.
     *
     * @param object $req The GET request
     * @return object $req The modified object
     */
    public function getDates($req)
    {
        # Prevent warnings
        $req->date_radio = (isset($req->date_radio)) ? $req->date_radio : false;

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
        if (!empty($req->start_date) || !empty($req->end_date)) {
            # Partial manual date fallback
            $req->start_date = ($req->start_date) ?: $today;
            $req->end_date = ($req->end_date) ?: $next_year;
        } else {
            switch ($req->date_radio) {
            case 'today':
                $req->start_date = $today;
                $req->end_date = $today;
                break;

            case 'tomorrow':
                $req->start_date = $tomorrow;
                $req->end_date = $tomorrow;
                break;

            case 'this_weekend':
                $req->start_date = $this_saturday;
                $req->end_date = $this_sunday;
                break;

            case 'next_week':
                $req->start_date = $today;
                $req->end_date = $next_week;
                break;

            default:
                $req->start_date = $today;
                $req->end_date = $next_year;
                break;
            }
        }

        unset($req->date_radio);
        return (object) $req;
    }

    /**
     * matchDates()
     */
    public function matchDates($req, $Feed)
    {
        # IO arrays
        $in = $this->matchCategories($req, $Feed);
        $out = [];

        # $req dates
        $start_date = strtotime($req->start_date);
        $end_date = strtotime($req->end_date);

        # Add matches to $out
        foreach ($in as $match) {
            # Define namespace
            $ns = $match->children('bc', true);
            $event_date = strtotime($ns->{'start_date'});

            if (betweenDates($event_date, $start_date, $end_date)) {
                array_push($out, $match);
            }
        }

        return (array) $out;
    }


    /**
     * Category functions
     */

    /**
     * getCategories()
     *
     * Set the categories (all form checkboxes).
     * Strips the bin2hex() suffixes and adds $req->category.
     *
     * @param object
     * @return object
     */
    public function getCategories($req)
    {
        $req->category = [];
        $regex = '/^category_.{4}$/';

        foreach ($req as $k => $v) {
            if (preg_match($regex, $k)) {
                $v_utf8 = quoted_printable_decode($v);
                array_push($req->category, $v_utf8);
                unset($req->$k);
            }
        }

        return (object) $req;
    }

    /**
     * Find matches
     *
     * Note the new object instead of $req
     *
     * The current order is:
     *  - get_categories() returns $out with all matching checkboxes,
     *    or all events if nothing is checked
     *  - filter_date() returns $out within the $req date period
     *  - filter_options() returns $out if certain boolean conditions match
     *
     * todo: Split into 3 clear mini-functions
     * todo: Move to the Parse class
     */
    public function matchCategories($req, $Feed)
    {
        return (!$req->category) ?? false;

        # Output array
        $out = [];

        if (empty($req->category)) {
            # Add all possible matches
            foreach ($Feed->item as $event) {
                array_push($out, $event);
            }
        } else {
            # Add checked categories only
            foreach ($req->category as $filter) {
                foreach ($Feed->item as $event) {
                    if (in_array($filter, get_object_vars($event->category))) {
                        array_push($out, $event);
                    }
                }
            }
        }

        return (array) $out;
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
    public function matchOptions($req, $Feed)
    {
        # IO arrays
        $in = filter_date($req, $Feed);
        $out = [];

        # Prevent warnings
        $req->is_virtual = (isset($req->is_virtual)) ?: false;
        $req->is_featured = (isset($req->is_featured)) ?: false;
        $req->is_cancelled = (isset($req->is_cancelled)) ?: false;

        # Add matches to $out
        if (!empty($in)) {
            foreach ($in as $k => $match) {
                # Define namespace
                $ns = $match->children('bc', true);

                # Boolean variables
                # todo: Don't rely on loose equality
                # https://www.php.net/manual/en/types.comparisons.php
                $is_virtual = ($ns->{'is_virtual'} == 'true') ? true : false;
                $is_featured = ($ns->{'is_featured'} == 'true') ? true : false;
                $is_featured_at_location = ($ns->{'is_featured_at_location'} == 'true') ? true : false;
                $is_cancelled = ($ns->{'is_cancelled'} == 'true') ? true : false;

                # Virtual and featured
                if ($req->is_virtual && $req->is_featured === true) {
                    if ($is_virtual && ($is_featured || $is_featured_at_location) === true) {
                        array_push($out, $match);
                    }
                } else {
                    # Virtual only
                    if ($req->is_virtual && $is_virtual === true) {
                        array_push($out, $match);
                    }

                    # Featured only
                    if (($req->is_featured && ($is_featured || $is_featured_at_location) === true)) {
                        array_push($out, $match);
                    }
                }

                # Hide cancelled unless checked
                if ($req->is_cancelled === false && $is_cancelled === true) {
                    array_pop($out);
                }
            }
        }

        return (array) $out;
        #return (object) $out;
    }
}
