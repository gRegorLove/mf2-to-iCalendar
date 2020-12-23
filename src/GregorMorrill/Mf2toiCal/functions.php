<?php
namespace GregorMorrill\Mf2toiCal;

/**
 * Function to call Mf2toiCal::convert()
 * @param string $url
 * @param string $lang defaults to 'en'
 * @param string $charset defaults to 'utf-8'
 */
function convert($url, $lang = 'en', $charset = 'utf-8')
{
	$Mf2toiCal = new Mf2toiCal($url, $lang, $charset);
	return $Mf2toiCal->convert();
}

