<?php

if (!function_exists('get_url')) {
	/**
	 * Get the base url with given path.
	 * @param   string $baseurl
	 * @param   string $path
	 * @return  string
	 */
	function get_url($baseurl, $path = '')
	{
		if (0 !== strpos($path, '/')) {
			$path = "/{$path}";
		}

		return $baseurl . $path;
	}
}

if (!function_exists('DomArrayToXml')) {
	/**
	 * Converti (récursivement) un array en XML (via DOM)
	 * @param 	array $array
	 * @param 	DOMDocument $dom_doc
	 * @param 	DOMElement $node
	 * @return 	string
	 */
	function DomArrayToXml($array, $dom_doc, $node)
	{
		return \Freebox\Helper::DomArrayToXml($array, $dom_doc, $node);
	}
}