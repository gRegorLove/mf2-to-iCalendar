<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use GregorMorrill\Mf2toiCal\Mf2toiCal;

final class Mf2toiCalTest extends TestCase
{

    public function testConstructorDefaults(): void
    {
        $Mf2toiCal = new Mf2toiCal('http://example.com');
        $this->assertEquals('http://example.com', $Mf2toiCal->url);
        $this->assertEquals('en', $Mf2toiCal->lang);
        $this->assertEquals('utf-8', $Mf2toiCal->charset);
    }

    public function testConstructorCustom(): void
    {
        $Mf2toiCal = new Mf2toiCal('http://example.de', 'de', 'utf-16');
        $this->assertEquals('http://example.de', $Mf2toiCal->url);
        $this->assertEquals('de', $Mf2toiCal->lang);
        $this->assertEquals('utf-16', $Mf2toiCal->charset);
    }

    public function testConstructorLanguageOnly(): void
    {
        $Mf2toiCal = new Mf2toiCal('http://example.com', 'sv');
        $this->assertEquals('http://example.com', $Mf2toiCal->url);
        $this->assertEquals('sv', $Mf2toiCal->lang);
        $this->assertEquals('utf-8', $Mf2toiCal->charset);
    }

    public function testFormatProperty(): void
    {
        $Mf2toiCal = new Mf2toiCal('http://example.com');
        $property = $Mf2toiCal->format_property('SUMMARY');
        $this->assertEquals('SUMMARY;LANGUAGE=en;CHARSET=utf-8:', $property);
    }

    public function testFormatDateWithTimezone(): void
    {
        $Mf2toiCal = new Mf2toiCal('http://example.com');
        $date = $Mf2toiCal->format_date('2018-03-29 06:20:00-0800');
        $this->assertEquals('20180329T142000Z', $date);
    }

    public function testFormatDateWithoutTimezone(): void
    {
        $Mf2toiCal = new Mf2toiCal('http://example.com');
        $date = $Mf2toiCal->format_date('2018-03-29 07:20:00');
        $this->assertEquals('20180329T072000', $date);
    }

    public function testFormatDtstamp(): void
    {
        $Mf2toiCal = new Mf2toiCal('http://example.com');
        $date = $Mf2toiCal->format_dtstamp('2018-03-29 18:20:00-0500');
        $this->assertEquals('DTSTAMP:20180329T232000Z', $date);
    }

    public function testTextEscapeBackslashes(): void
    {
        $Mf2toiCal = new Mf2toiCal('http://example.com');
        $text = $Mf2toiCal->text('this\that');
        $this->assertEquals('this\\\\that', $text);
    }

    public function testTextEscapeSemicolons(): void
    {
        $Mf2toiCal = new Mf2toiCal('http://example.com');
        $text = $Mf2toiCal->text('this; then that');
        $this->assertEquals('this\; then that', $text);
    }

    public function testTextEscapeCommas(): void
    {
        $Mf2toiCal = new Mf2toiCal('http://example.com');
        $text = $Mf2toiCal->text('this, that');
        $this->assertEquals('this\, that', $text);
    }

    public function testTextNormalizeCRLF(): void
    {
        $Mf2toiCal = new Mf2toiCal('http://example.com');
        $text = $Mf2toiCal->text("this\r\nthat");
        $this->assertEquals('this\nthat', $text);
    }

    public function testFold75Bytes(): void
    {
        $Mf2toiCal = new Mf2toiCal('http://example.com');
        $folded = $Mf2toiCal->fold('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec aliquet sed.');
        $this->assertEquals('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec aliquet sed.', $folded);
    }

    public function testFoldMoreThan75Bytes(): void
    {
        $Mf2toiCal = new Mf2toiCal('http://example.com');
        $folded = $Mf2toiCal->fold('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc porttitor neque non nibh auctor metus.');
        $this->assertEquals("Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc porttitor neq\r\n\tue non nibh auctor metus.", $folded);
    }

    /**
     * 'Lorem ipsum' text is 74 bytes. Poop emoji is 4 bytes.
     */
    public function testFoldMultibyteCharacters(): void
    {
        $Mf2toiCal = new Mf2toiCal('http://example.com');
        $folded = $Mf2toiCal->fold('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec et posuere.💩');
        $this->assertEquals("Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec et posuere.\r\n\t💩", $folded);
    }

}

