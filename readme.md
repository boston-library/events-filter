todo


***


RSS & Atom Feeds for PHP
========================

[![Downloads this Month](https://img.shields.io/packagist/dm/dg/rss-php.svg)](https://packagist.org/packages/dg/rss-php)
[![Latest Stable Version](https://poser.pugx.org/dg/rss-php/v/stable)](https://github.com/dg/rss-php/releases)
[![License](https://img.shields.io/badge/license-New%20BSD-blue.svg)](https://github.com/dg/rss-php/blob/master/license.md)

RSS & Atom Feeds for PHP is a very small and easy-to-use library for consuming an RSS and Atom feeds.

It requires PHP 5.3 or newer with CURL extension or enabled allow_url_fopen
and is licensed under the New BSD License. You can obtain the latest version from
our [GitHub repository](https://github.com/dg/rss-php/releases) or install it via Composer:

```
php composer.phar require dg/rss-php
```

Usage
-----

Download RSS feed from URL:

```php
$rss = Feed::loadRss($url);
```

The returned properties are SimpleXMLElement objects. Extracting
the information from the channel is easy:

```php
echo 'Title: ', $rss->title;
echo 'Description: ', $rss->description;
echo 'Link: ', $rss->link;

foreach ($rss->item as $item) {
	echo 'Title: ', $item->title;
	echo 'Link: ', $item->link;
	echo 'Timestamp: ', $item->timestamp;
	echo 'Description ', $item->description;
	echo 'HTML encoded content: ', $item->{'content:encoded'};
}
```

Download Atom feed from URL:

```php
$atom = Feed::loadAtom($url);
```

You can also enable caching:

```php
Feed::$cacheDir = __DIR__ . '/tmp';
Feed::$cacheExpire = '5 hours';
```

If you like it, **[please make a donation now](https://nette.org/make-donation?to=rss-php)**. Thank you!


***


# Zap Calendar iCalendar Library

(https://github.com/zcontent/icalendar)

The Zap Calendar iCalendar Library is a PHP library for supporting the iCalendar (RFC 5545) standard.

This PHP library is for reading and writing iCalendar formatted feeds and 
files. Features of the library include:

- Read AND write support for iCalendar files
- Object based creation and manipulation of iCalendar files
- Supports expansion of RRULE to a list of repeating dates
- Supports adding timezone info to iCalendar file

All iCalendar data is stored in a PHP object tree.
This allows any property to be added to the iCalendar feed without
requiring specialized library function calls.
With power comes responsibility.  Missing or invalid properties can cause 
the resulting iCalendar file to be invalid. Visit [iCalendar.org](http://icalendar.org) to view valid
properties and test your feed using the site's [iCalendar validator tool](http://icalendar.org/validator.html).

Library API documentation can be found at http://icalendar.org/zapcallibdocs

See the examples folder for programs that read and write iCalendar
files. At its simpliest, you need to include the library at the top of your program:

```php
require_once($path_to_library . "/zapcallib.php");
```

Create an ical object using the ZCiCal object:

```php
$icalobj = new ZCiCal();
```

Add an event object:

```php
$eventobj = new ZCiCalNode("VEVENT", $icalobj->curnode);
```

Add a start and end date to the event:

```php
// add start date
$eventobj->addNode(new ZCiCalDataNode("DTSTART:" . ZCiCal::fromSqlDateTime("2020-01-01 12:00:00")));

// add end date
$eventobj->addNode(new ZCiCalDataNode("DTEND:" . ZCiCal::fromSqlDateTime("2020-01-01 13:00:00")));
```

Write the object in iCalendar format using the  export() function call:

```php
echo $icalobj->export();
```

This example will not validate since it is missing some required elements. 
Look at the simpleevent.php example for the minimum # of elements 
needed for a validated iCalendar file.

To create a multi-event iCalendar file, simply create multiple event objects. For example:

```php
$icalobj = new ZCiCal();
$eventobj1 = new ZCiCalNode("VEVENT", $icalobj->curnode);
$eventobj1->addNode(new ZCiCalDataNode("SUMMARY:Event 1"));
...
$eventobj2 = new ZCiCalNode("VEVENT", $icalobj->curnode);
$eventobj2->addNode(new ZCiCalDataNode("SUMMARY:Event 2"));
...
```

To read an existing iCalendar file/feed, create the ZCiCal object with a string representing the contents of the iCalendar file:

```php
$icalobj = new ZCiCal($icalstring);
```

Large iCalendar files can be read in chunks to reduce the amount of memory needed to hold the iCalendar feed in memory. This example reads 500 events at a time:

```php
$icalobj = null;
$eventcount = 0;
$maxevents = 500;
do
{
	$icalobj = newZCiCal($icalstring, $maxevents, $eventcount);
	...
	$eventcount +=$maxevents;
}
while($icalobj->countEvents() >= $eventcount);
```

You can read the events from an imported (or created) iCalendar object in this manner:

```php
foreach($icalobj->tree->child as $node)
{
	if($node->getName() == "VEVENT")
	{
		foreach($node->data as $key => $value)
		{
			if($key == "SUMMARY")
			{
				echo "event title: " . $value->getValues() . "\n";
			}
		}
	}
}
```

## Known Limitations

- Since the library utilizes objects to read and write iCalendar data, the 
size of the iCalendar data is limited to the amount of available memory on the machine.
The ZCiCal() object supports reading a range of events to minimize memory
space.
- The library ignores timezone info when importing files, instead utilizing PHP's timezone
library for calculations (timezones are supported when exporting files).
Imported timezones need to be aliased to a [PHP supported timezone](http://php.net/manual/en/timezones.php).
- At this time, the library does not support the "BYSETPOS" option in RRULE items.
- At this time, the maximum date supported is 2036 to avoid date math issues
with 32 bit systems.
- Repeating events are limited to a maximum of 5,000 dates to avoid memory or infinite loop issues
