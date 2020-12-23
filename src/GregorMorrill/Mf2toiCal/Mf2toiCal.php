<?php namespace GregorMorrill\Mf2toiCal;

use DateTime;
use DateTimeZone;
use Mf2;
use BarnabyWalters\Mf2 as Mf2helper;


mb_internal_encoding('UTF-8');

if ( !defined('PRODID_DOMAIN') )
{
	define('PRODID_DOMAIN', 'gregorlove.com');
}

class Mf2toiCal
{
	/**
	 * @var string
	 */
	private $version = '0.0.3';

	/**
	 * @var string
	 */
	private $url;

	/**
	 * @var string
	 */
	private $lang;

	/**
	 * @var string
	 */
	private $charset;

	/**
	 * Constructor
	 * @param string $url
	 * @param string $lang default to 'en'
	 * @param string $charset default to 'utf-8'
	 * @access public
	 * @return
	 */
	public function __construct($url, $lang = 'en', $charset = 'utf-8')
	{
		$this->url = $url;
		$this->lang = $lang;
		$this->charset = $charset;
	} # end method __construct()

	/**
	 * Get Mf2toiCal property
	 */
	public function __get($name)
	{

		if ( isset($this->$name) )
		{
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

	/**
	 * This method converts h-event microformats to iCalendar
	 * @access public
	 */
	public function convert()
	{
		$microformats = Mf2\fetch($this->url);
		$events = Mf2helper\findMicroformatsByType($microformats, 'h-event');

		$lines = [];
		$lines[] = 'BEGIN:VCALENDAR';
		$lines[] = sprintf('PRODID:-//%s//mf2 to iCalendar %s//EN', PRODID_DOMAIN, $this->version);
		$lines[] = 'VERSION:2.0';
		$lines[] = 'METHOD:PUBLISH';

		# loop: each event
		foreach ( $events as $event )
		{
			$lines[] = 'BEGIN:VEVENT';

			# if: mf2 has a u-url, update $this->url
			if ( Mf2helper\hasProp($event, 'url') )
			{
				$this->url = Mf2helper\getPlaintext($event, 'url');
			} # end if

			# if: mf2 has u-uid, use it
			if ( Mf2helper\hasProp($event, 'uid') )
			{
				$lines[] = $this->fold( 'UID:' . Mf2helper\getPlaintext($event, 'uid') );
			}
			# else: fallback to $this->url
			else
			{
				$lines[] = $this->fold( 'UID:' . $this->url );
			} # end if

			$lines[] = $this->fold( 'URL:' . $this->url );
			$lines[] = $this->format_dtstamp( Mf2helper\getPlaintext($event, 'published') );
			$lines[] = 'DTSTART:' . $this->format_date( Mf2helper\getPlaintext($event, 'start') );

			if ( Mf2helper\hasProp($event, 'end') )
			{
				$lines[] = 'DTEND:' . $this->format_date( Mf2helper\getPlaintext($event, 'end') );
			}

			$lines[] = $this->fold( $this->format_property('SUMMARY') . $this->text(Mf2helper\getPlaintext($event, 'name')) );

			$property = $this->format_property('DESCRIPTION');

			# if: mf2 has `content` property, use it
			if ( Mf2helper\hasProp($event, 'content') )
			{
				$lines[] = $this->fold( $property . $this->text(Mf2helper\getPlaintext($event, 'content')) );
			}
			# else if: mf2 has description property, use it
			else if ( Mf2helper\hasProp($event, 'description') )
			{
				$lines[] = $this->fold( $property . $this->text(Mf2helper\getPlaintext($event, 'description')) );
			}
			# else if: mf2 has summary property, use it as fallback description
			else if ( Mf2helper\hasProp($event, 'summary') )
			{
				$lines[] = $this->fold( $property . $this->text(Mf2helper\getPlaintext($event, 'summary')) );
			} # end if

			# if: mf2 has a location, use it
			if ( Mf2helper\hasProp($event, 'location') )
			{
				$lines[] = $this->fold( $this->format_property('LOCATION') . $this->text(Mf2helper\getPlaintext($event, 'location')) );
			} # end if

			$lines[] = 'END:VEVENT';
		} # end loop

		$lines[] = 'END:VCALENDAR';

		// header('Content-Type: text/plain; charset=utf8');
		header('Content-Disposition: attachment; filename=calendar.ics');
		header('Connection: close');
		header('Content-Type: text/calendar; charset=utf8; name=calendar.ics');
		echo implode("\r\n", array_filter($lines));
		exit;
	} # end method convert()

	/**
	 * Format iCalendar property name
	 * @param string $name
	 * @return string
	 */
	public function format_property($name)
	{
		return sprintf('%s;LANGUAGE=%s;CHARSET=%s:', $name, $this->lang, $this->charset);
	} # end method format_property()

	/**
	 * Format iCalendar dtstamp content line
	 * @param string $input
	 * @return string
	 */
	public function format_dtstamp($input)
	{

		if ( !$dtstamp = $this->format_date($input) )
		{
			$dtstamp = $this->format_date('now');
		}

		return 'DTSTAMP:' . $dtstamp;
	} # end method format_dtstamp()

	/**
	 * Format iCalendar date value
	 * @param string $input
	 * @return string
	 */
	public function format_date($input)
	{

		if ( !$input )
		{
			return '';
		}

		$parsed = date_parse($input);
		$has_timezone = ( isset($parsed['zone']) ) ? true : false;

		# if: datetime string has timezone
		if ( $has_timezone )
		{
			$date = new DateTime($input);
			$date->setTimezone(new DateTimeZone('UTC'));
			return $date->format('Ymd\THis\Z');
		}
		# else:
		else
		{
			$date = new DateTime($input);
			return $date->format('Ymd\THis');
		}

	} # end method format_date()

	/**
	 * Escape iCalendar text content lines
	 * @param string $input
	 * @return string
	 */
	public function text($input)
	{
		$output = str_replace('\\', '\\\\', $input);
		$output = str_replace(';', '\\;', $output);
		$output = str_replace(',', '\\,', $output);
		$output = str_replace(["\r\n", "\n"], '\n', $output);
		return trim($output);
	} # end method text()

	/**
	 * Fold iCalendar content lines
	 * @param string $input
	 * @return string
	 */
	public function fold($input)
	{
		$bc = strlen($input);
		$index = 0;
		$lines = [];

		if ( $bc <= 75 )
		{
			return $input;
		}

		while ( $bc > 0 )
		{
			$cut_length = ( $index == 0 ) ? 75 : 74;
			$line = mb_strcut($input, $index, $cut_length);
			$length = strlen($line);
			$index += $length;
			$bc -= $length;

			$lines[] = $line;
		}

		return join($lines, "\r\n\t");
	} # end method fold()

} # end class Mf2toiCal

