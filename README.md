# mf2-to-iCalendar

Convert microformats [h-event](http://microformats.org/wiki/h-event) to iCalendar.

Note: This is currently very much an _alpha_ version, doing the minimal amount I needed it to do. I plan to expand it, though. Issue reports are welcomed.

## Requirements
* PHP 5.6+
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
        "gregorlove/mf2-to-icalendar": "dev-master"
    }
}

```

Then in the project file you want to use it, import the namespace and add the Composer autoloader:
```php
use GregorMorrill\Mf2toiCal;

require_once('vendor/autoload.php');
```

### Manual Installation

Alternately, you can manually install without user Composer.

You will need to first download the php-mf2 and php-mf-cleaner libraries linked above and include them in your project.

Then download the files in this project's directory `src/GregorMorrill/Mf2toiCal/` and include them directly in your project:

```php
use GregorMorrill\Mf2toiCal;

require_once('src/GregorMorrill/Mf2toiCal/Mf2toiCal.php');
require_once('src/GregorMorrill/Mf2toiCal/functions.php');
```

### Specify the Domain

The generated iCalendar .ics file has a `PRODID` that includes a domain and the name/version of this script.

It's recommended to specify the domain you're using this on. If you don't, it will default to domain, gregorlove.com.

To specify your domain, after installation define the constant:

```php
define('PRODID_DOMAIN', 'example.com');
```

## Usage

```php
try
{
	Mf2toiCal\convert('http://example.com/event');
}
catch ( Exception $e )
{
	echo $e->getMessage();
}
```

### Exceptions

If the specified URL does not have h-event microformats, an Exception is thrown. Your code should be set up to handle that Exception.

### Language and Character Set

This script defaults to language `en` and charset `utf-8` for text content lines in the generated .ics file. You can specify different options when calling `convert()`:

```php
# parameters: $url, $lang, $charset
Mf2toiCal\convert('http://example.com/event', 'sv');
```

Detecting the language from the HTML and using that is on my TODO list.
