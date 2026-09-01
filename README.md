# mf2-to-iCalendar

Convert microformats [h-event](https://microformats.org/wiki/h-event) to iCalendar.

Note: This is currently very much an _alpha_ version, doing the minimal amount I needed it to do. I plan to expand it, though. Issue reports are welcomed.

## Requirements
* PHP 7.4+
* [php-mf2](https://github.com/indieweb/php-mf2) - included via Composer
* [php-mf-cleaner](https://github.com/barnabywalters/php-mf-cleaner) - included via Composer

## Installation

It is recommended to install via [Composer](https://getcomposer.org/). This project is not listed on packagist yet, but will be once it's more stable. Until then, you can install by adding to your composer.json:

```
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/gRegorLove/mf2-to-iCalendar"
        }
    ],
    "require": {
        "gregorlove/mf2-to-icalendar": "0.0.5"
    }
}

```

Then in the project file you want to use it, import the namespace and add the Composer autoloader:
```php
use GregorMorrill\Mf2toiCal;

require_once 'vendor/autoload.php';
```

### Manual Installation

Alternately, you can manually install without using Composer.

You will need to first download the php-mf2 and php-mf-cleaner libraries linked above and include them in your project.

Then download the files in this project's directory `src/GregorMorrill/Mf2toiCal/` and include them directly in your project:

```php
use GregorMorrill\Mf2toiCal;

require_once 'src/GregorMorrill/Mf2toiCal/Mf2toiCal.php';
require_once 'src/GregorMorrill/Mf2toiCal/functions.php';
```

### Specify the Domain

The generated iCalendar .ics file has a `PRODID` that includes a domain and the name/version of this script.

It's recommended to specify the domain you're using this on. If you don't, it will default to `example.com`.

To specify your domain, after installation define the constant:

```php
define('PRODID_DOMAIN', 'example.com');
```

## Usage

To fetch a URL and convert:

```php
Mf2toiCal\convert('https://example.com/event');
```

To provide direct HTML input and convert:

```php
Mf2toiCal\convertHtmlInput('<div class="h-event">...</div>', 'https://example.com/event2');
```

Note: the URL is not fetched when using `convertHtmlInput()`, but is still used as the default `URL` and `UID` properties in the .ics file.

### Language and Character Set

This script defaults to language `en` and charset `utf-8` for text content lines in the generated .ics file. You can specify different options when calling either function:

```php
# parameters: $url, $lang, $charset
Mf2toiCal\convert('https://example.com/event', 'sv');
```

```php
# parameters: $html, $url, $lang, $charset
Mf2toiCal\convertHtmlInput($html, 'https://example.com/event2', 'sv');
```

Detecting the language from the HTML and using that is on the TODO list.

### Experimental

Starting with v0.0.5, it will attempt to parse `p-event-status` properties for the [iCalendar values](https://datatracker.ietf.org/doc/html/rfc5545#section-3.8.1.11) “tentative”, “cancelled”, or “confirmed” and include those in the generated .ics.

These properties are not known to be in published `h-event` posts yet and it is possible the property will change when there is more consensus. If you are a publisher, consider this before adding the properties to your events.

I will plan to keep this tool up to date with whatever property represents the event status.

## Changelog

* [Changelog](CHANGELOG.md)

