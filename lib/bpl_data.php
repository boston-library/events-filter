<?php

class BplData
{
    /**
     * Functions
     */

    # Make HTML form checkboxes
    public function make_checkboxes($label, $values)
    {
        $output = '';
        foreach ($values as $v) {
            $id_tmp = 'category_'.bin2hex(random_bytes(2));
            $output .= <<<EOT
              <input type='checkbox'
                name='$id_tmp' id='$id_tmp'
                value="$v" />
              <label for='$id_tmp'>$v</label>
              <br />
EOT;
        }
        return $output;
    }

    # Print plain array list
    public function debug_list($array)
    {
        echo '<pre>';
        foreach ($array as $a) {
            echo "$a<br />";
        }
        echo '</pre>';
    }


    /**
     * Variables
     */

    # Locations
    public $locations = [
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
    public $event_types = [
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
    public $programs = [
        'Central Library Author Talk Series',
        'Future Readers Club',
        'Google Class Series',
        'Local & Family History Lecture Series',
        'Never Too Late Group',
        'Software for Creatives',
    ];

    # Audiences
    public $audiences = [
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
    public $languages = [
        'English',
        'Español',
        '國語',
        '粵語',
    ];
}
