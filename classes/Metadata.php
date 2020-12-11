<?php
declare(strict_types=1);

class Metadata
{
    /**
     * Variables
     *
     * Note there's no public API available to pull metadata from
     * https://partnerportal.bibliocommons.com/hc/en-us/articles/205053354-Events-RSS-Feed
     *
     * So we must manually keep it up to date,
     * e.g., when '(formerly Dudley)' goes away
     */

    # Locations
    public $Locations = [
        'Adams Street',
        'Brighton',
        'Central Library in Copley Square',
        'Charlestown',
        'Chinatown',
        'Codman Square',
        'Connolly',
        'East Boston',
        'Egleston Square',
        'Faneuil',
        'Fields Corner',
        'Grove Hall',
        'Honan-Allston',
        'Hyde Park',
        'Jamaica Plain',
        'Lower Mills',
        'Mattapan',
        'North End',
        'Parker Hill',
        'Roslindale',
        'Roxbury (formerly Dudley)',
        'South Boston',
        'South End',
        'Uphams Corner',
        'West End',
        'West Roxbury',
    ];

    # Event Types
    public $EventTypes = [
        'Arts & Crafts',
        'Author Talk',
        'Board of Trustees Meetings',
        'Book Group',
        'Book Sale',
        'Career Development',
        'Community Meeting',
        'Computers/Technology Classes',
        'Concerts',
        'Early Literacy',
        'English as a Second Language',
        'Environmental Events',
        'Film',
        'Games / Gaming',
        'Health / Fitness',
        'Homework Help',
        'Kirstein Business Library & Innovation Center Classes',
        'Leventhal Map and Education Center Event',
        'Math & Science',
        'Nature',
        'Online Event',
        'Performing Arts',
        'President\'s Picks',
        'Story Time',
        'Talks & Lectures',
        'Workshops & Classes',
        'World Language Conversation Group',
    ];

    # Programs
    public $Programs = [
        'Central Library Author Talk Series',
        'Future Readers Club',
        'Google Class Series',
        'Local & Family History Lecture Series',
        'Never Too Late Group',
        'Software for Creatives',
    ];

    # Audiences
    public $Audiences = [
        'Adults',
        'Businesses',
        'Children (Ages 0-5)',
        'Children (Ages 6-12)',
        'College Students',
        'Families',
        'Older Adults',
        'Teens (Ages 13-18)',
        'Tweens (Ages 9-12)',
        'Visitors',
        'Young Adults (Ages 20-34)',
    ];

    # Languages
    public $Languages = [
        'English',
        'Español',
        '國語',
        '粵語',
    ];


    /**
     * Functions
     */

    # Make HTML form checkboxes
    public function checkboxes($label, $values)
    {
        $output = '';
        foreach ($values as $v) {
            $id_tmp = 'category_'.bin2hex(random_bytes(2));
            #$v_uri = urlencode($v);

            # value="$v_uri" />
            $output .= <<<HTML
              <input type='checkbox'
                name='$id_tmp' id='$id_tmp'
                value="$v" />
              <label for='$id_tmp'>$v</label>
              <br />
HTML;
        }
        return $output;
    }

    # Print plain array list
    public function pr_array($array)
    {
        echo '<pre>';
        foreach ($array as $a) {
            echo "$a<br />";
        }
        echo '</pre>';
    }
}
