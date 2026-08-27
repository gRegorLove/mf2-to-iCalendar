<?php

declare(strict_types=1);

namespace GregorMorrill\Mf2toiCal;

/**
 * Function to call Mf2toiCal::convert()
 */
function convert(
    string $url,
    string $lang = 'en',
    string $charset = 'utf-8'
) {
    $Mf2toiCal = new Mf2toiCal($url, $lang, $charset);
    return $Mf2toiCal->convert();
}

/**
 * Function to call Mf2toiCal::convert() with direct HTML input
 *
 * This does not fetch the $url
 */
function convertHtmlInput(
    string $html,
    string $url,
    string $lang = 'en',
    string $charset = 'utf-8'
) {
    $Mf2toiCal = new Mf2toiCal($url, $lang, $charset);
    $Mf2toiCal->setHtml($html);
    return $Mf2toiCal->convert();
}

