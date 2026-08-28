<?php

declare(strict_types=1);

namespace GregorMorrill\Mf2toiCal;

use DateTime;
use DateTimeZone;
use Mf2;
use BarnabyWalters\Mf2 as Mf2helper;

mb_internal_encoding('UTF-8');

if (!defined('PRODID_DOMAIN')) {
    define('PRODID_DOMAIN', 'example.com');
}

class Mf2toiCal
{
    private string $version = '0.0.4';
    private string $url;
    private string $lang;
    private string $charset;
    private ?string $html = null;

    public function __construct(
        string $url,
        string $lang = 'en',
        string $charset = 'utf-8'
    ) {
        $this->url = $url;
        $this->lang = $lang;
        $this->charset = $charset;
    }

    /**
     * Get Mf2toiCal property
     */
    public function __get($name)
    {
        if (isset($this->$name)) {
            return $this->$name;
        }

        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);
        return null;
    }

    public function setHtml(string $html): void
    {
        $this->html = $html;
    }

    /**
     * Convert h-event microformats to iCalendar
     */
    public function convert(): void
    {
        // mf2 parsing can return null if not an HTML document.
        // use null coalesce operator to ensure $microformats
        // is always an array

        if ($this->html) {
            $microformats = Mf2\parse($this->html, $this->url) ?? [];
        } else {
            $microformats = Mf2\fetch($this->url) ?? [];
        }

        $events = Mf2helper\findMicroformatsByType($microformats, 'h-event');

        $lines = [];
        $lines[] = 'BEGIN:VCALENDAR';
        $lines[] = sprintf('PRODID:-//%s//mf2 to iCalendar %s//EN', PRODID_DOMAIN, $this->version);
        $lines[] = 'VERSION:2.0';
        $lines[] = 'METHOD:PUBLISH';

        foreach ($events as $event) {
            $lines[] = 'BEGIN:VEVENT';

            # default $url
            $url = $this->url;

            # mf2 has a u-url, update $url
            if (Mf2helper\hasProp($event, 'url')) {
                $url = Mf2helper\getPlaintext($event, 'url');
            }

            # mf2 has u-uid, use it
            if (Mf2helper\hasProp($event, 'uid')) {
                $lines[] = $this->fold( 'UID:' . Mf2helper\getPlaintext($event, 'uid') );
            } else {
                # fallback to $url
                $lines[] = $this->fold( 'UID:' . $url );
            }

            $lines[] = $this->fold( 'URL:' . $url );
            $lines[] = $this->format_dtstamp( Mf2helper\getPlaintext($event, 'published', '') );
            $lines[] = 'DTSTART:' . $this->format_date( Mf2helper\getPlaintext($event, 'start') );

            if (Mf2helper\hasProp($event, 'end')) {
                $lines[] = 'DTEND:' . $this->format_date( Mf2helper\getPlaintext($event, 'end') );
            }

            $lines[] = $this->fold( $this->format_property('SUMMARY') . $this->text(Mf2helper\getPlaintext($event, 'name')) );

            $property = $this->format_property('DESCRIPTION');

            # mf2 has `content` property, use it
            if (Mf2helper\hasProp($event, 'content')) {
                $lines[] = $this->fold( $property . $this->text(Mf2helper\getPlaintext($event, 'content')) );
            } elseif (Mf2helper\hasProp($event, 'description')) {
                # mf2 has description property, use it
                $lines[] = $this->fold( $property . $this->text(Mf2helper\getPlaintext($event, 'description')) );
            } elseif (Mf2helper\hasProp($event, 'summary')) {
                # mf2 has summary property, use it as fallback description
                $lines[] = $this->fold( $property . $this->text(Mf2helper\getPlaintext($event, 'summary')) );
            }

            # mf2 has a location, use it
            if (Mf2helper\hasProp($event, 'location')) {
                $lines[] = $this->fold( $this->format_property('LOCATION') . $this->text(Mf2helper\getPlaintext($event, 'location')) );
            }

            $status = Mf2helper\getPlaintext($event, 'status') ?? Mf2helper\getPlaintext($event, 'x-status');
            if ($status) {
                $lines[] = $this->format_status($status);
            }

            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';

        header('Content-Disposition: attachment; filename=calendar.ics');
        header('Connection: close');
        header('Content-Type: text/calendar; charset=utf8; name=calendar.ics');
        echo implode("\r\n", array_filter($lines));
        exit;
    }

    /**
     * Format iCalendar property name
     */
    public function format_property(string $name): string
    {
        return sprintf('%s;LANGUAGE=%s;CHARSET=%s:',
            $name,
            $this->lang,
            $this->charset
        );
    }

    /**
     * Format iCalendar dtstamp content line
     */
    public function format_dtstamp(string $input): string
    {
        if (!($dtstamp = $this->format_date($input))) {
            $dtstamp = $this->format_date('now');
        }

        return 'DTSTAMP:' . $dtstamp;
    }

    /**
     * Format iCalendar date value
     */
    public function format_date(string $input): string
    {
        if (!$input) {
            return '';
        }

        $parsed = date_parse($input);
        $has_timezone = ( isset($parsed['zone']) ) ? true : false;

        # datetime string has timezone
        if ($has_timezone) {
            $date = new DateTime($input);
            $date->setTimezone(new DateTimeZone('UTC'));
            return $date->format('Ymd\THis\Z');
        } else {
            $date = new DateTime($input);
            return $date->format('Ymd\THis');
        }
    }

    /**
     * EXPERIMENTAL: the `status` property is not known to be published
     * currently, so this is intended as a proof of concept.
     *
     * If there is a p-status or p-x-status property with a valid value,
     * add it to the generated lines
     *
     * @see https://datatracker.ietf.org/doc/html/rfc5545#section-3.8.1.11
     */
    public function format_status(string $input): ?string
    {
        $status = trim(strtoupper($input));

        if (!in_array($status, ['TENTATIVE', 'CONFIRMED', 'CANCELLED',])) {
            return null;
        }

        return 'STATUS:' . $status;
    }

    /**
     * Escape iCalendar text content lines
     */
    public function text(string $input): string
    {
        $output = str_replace('\\', '\\\\', $input);
        $output = str_replace(';', '\\;', $output);
        $output = str_replace(',', '\\,', $output);
        $output = str_replace(["\r\n", "\n"], '\n', $output);
        return trim($output);
    }

    /**
     * Fold iCalendar content lines
     */
    public function fold(string $input): string
    {
        $bc = strlen($input);
        $index = 0;
        $lines = [];

        if ($bc <= 75) {
            return $input;
        }

        while ($bc > 0) {
            $cut_length = ($index == 0) ? 75 : 74;
            $line = mb_strcut($input, $index, $cut_length);
            $length = strlen($line);
            $index += $length;
            $bc -= $length;

            $lines[] = $line;
        }

        return implode("\r\n\t", $lines);
    }
}

