<?php
declare(strict_types = 1);

/**
 * Adapted from Gazelle's feed.class.php
 */

class FeedPublish
{
    /**
     * open
     */
    public function open()
    {
        header('Content-Type: application/rss+xml; charset=utf-8');
        echo '<?xml version="1.0" encoding="UTF-8" ?>',
             '<rss xmlns:bc="http://bibliocommons.com/rss/1.0/modules/event/" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:atom="http://www.w3.org/2005/Atom" version="2.0"><channel>';
    }

    /**
     * close
     */
    public function close()
    {
        echo '</channel></rss>';
    }

    /**
     * channel
     */
    public function channel()
    {
        # echo commas because <<<XML would copy whitespace
        echo '<title>Export Events | Boston Public Library</title>',
             '<description>Events RSS feed</description>',
             '<link>https://apps.bpl.org/events-filter</link>',
             '<language>en-us</language>',
             '<lastBuildDate>'.date('r').'</lastBuildDate>',
             '<generator>BiblioCommons</generator>';
    }

    /**
     * item
     * todo: take one object
     */
    public function item($Match)
    {
        /*
        # Sanitize date format
        $StartDate = date('r', strtotime($Match->{'bc:start_date'}));
        $EndDate = date('r', strtotime($Match->{'bc:end_date'}));
        */

        // Escape with CDATA, otherwise the feed breaks.
        $Item  = "<item>";
        $Item .= "<title><![CDATA[$Match->title]]></title>";
        $Item .= "<description><![CDATA[$Match->description]]></description>";
        $Item .= "<link>$Match->link</link>";
        $Item .= "<guid>$Match->link</guid>";

        if (!empty($Match->enclosure)) {
            $Item .= "<enclosure url='$Match->enclosure->url' type='$Match->enclosure->type' length='0' />";
        }

        # Start and end dates
        $Item .= "<bc:start_date>".$Match->{'bc:start_date'}."</bc:start_date>";
        $Item .= "<bc:start_date_local>".$Match->{'bc:start_date_local'}."</bc:start_date_local>";
        $Item .= "<bc:end_date>".$Match->{'bc:end_date'}."</bc:end_date>";
        $Item .= "<bc:end_date_local>".$Match->{'bc:end_date_local'}."</bc:end_date_local>";

        # Boolean options
        $Item .= "<bc:is_cancelled>".$Match->{'bc:is_cancelled'}."</bc:is_cancelled>";
        $Item .= "<bc:is_featured>".$Match->{'bc:is_featured'}."</bc:is_featured>";
        $Item .= "<bc:is_featured_at_location>".$Match->{'bc:is_featured_at_location'}."</bc:is_featured_at_location>";
        $Item .= "<bc:is_virtual>".$Match->{'bc:is_virtual'}."</bc:is_virtual>";

        # Categories. Note lack of category domains
        foreach ($Match->category as $Category) {
            $Item .= "<category>$Category</category>";
        }

        # Contact info
        $Item .= "<bc:contact>";
        $Item .= "<bc:name>".$Match->{'bc:contact'}->{'bc:name'}."</bc:name>";
        $Item .= "<bc:phone>".$Match->{'bc:contact'}->{'bc:phone'}."</bc:phone>";
        $Item .= "<bc:email>".$Match->{'bc:contact'}->{'bc:email'}."</bc:email>";
        $Item .= "</bc:contact>";

        # Registration info
        $Item .= "<bc:registration_info>";
        $Item .= "<bc:is_required>".$Match->{'bc:registration_info'}->{'bc:is_required'}."</bc:is_required>";
        $Item .= "<bc:is_full>".$Match->{'bc:registration_info'}->{'bc:is_full'}."</bc:is_full>";
        $Item .= "</bc:registration_info>";
 
        # Okay done
        $Item .= "</item>";
        echo $Item;
    }
}
