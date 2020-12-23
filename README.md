# mf2-to-iCalendar

Convert microformats [h-event](https://microformats.org/wiki/h-event) to iCalendar.

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

It's recommended to specify the domain you're using this on. If you don't, it will default to example.com.

To specify your domain, after installation define the constant:

```php
define('PRODID_DOMAIN', 'example.com');
```

## Usage

```php
Mf2toiCal\convert('https://example.com/event');
```

### Exceptions

If the specified URL does not have h-event microformats, an Exception is thrown. Your code should be set up to handle that Exception.

### Language and Character Set

This script defaults to language `en` and charset `utf-8` for text content lines in the generated .ics file. You can specify different options when calling `convert()`:

```php
# parameters: $url, $lang, $charset
Mf2toiCal\convert('https://example.com/event', 'sv');
```

Detecting the language from the HTML and using that is on my TODO list.

## Changelog

### 0.0.3
2020-12-23
* No longer throws an Exception if no h-event microformats found when converting. Instead will generate an "empty" iCalendar.
* Changed default domain to example.com

### 0.0.2
2018-03-29
* Now prefers `content` h-event property over `description`
* Adds support for dates with local time
* Adds unit tests

### 0.0.1
2017-07-27
* initial release

