<?php

namespace AppBundle\Twig;

class UrlExtension extends \Twig_Extension {

	/**
	 * {@inheritdoc}
	 */
	public function getFilters() {
		return array(
		    new \Twig_SimpleFilter('url_decode', array($this, 'urlDecode')),
		    new \Twig_SimpleFilter('filter_decode', array($this, 'filterDecode')),
		);
	}

	/**
	 * URL Decode a string
	 *
	 * @param string $url
	 *
	 * @return string The decoded URL
	 */
	public function urlDecode($url) {
		return urldecode($url);
	}

	/**
	 * URL Decode a string
	 *
	 * @param string $url
	 *
	 * @return string The decoded URL
	 */
	public function filterDecode($url) {
		$search = array('%3A', '%2C', '%3B');
		$replace = array(':', ',', ';');
		return str_replace($search, $replace, $url);
	}

	/**
	 * Returns the name of the extension.
	 *
	 * @return string The extension name
	 */
	public function getName() {
		return 'url';
	}

}
