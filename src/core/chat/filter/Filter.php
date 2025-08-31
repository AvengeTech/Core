<?php

namespace core\chat\filter;

use core\network\Links;
use core\Core;

class Filter {

	const FILTER_ADVERTISING = 0;

	const CAP_LIMIT = 20;

	public $plugin;
	public $domain_endings = [];

	public $maxCaps;

	public function __construct(Core $plugin) {
		$this->plugin = $plugin;
		//$this->registerDomainEndings();
	}

	public function registerDomainEndings() {
		$file = file("http://data.iana.org/TLD/tlds-alpha-by-domain.txt");
		array_shift($file);
		foreach ($file as $de) {
			$de = str_replace("\n", "", $de);
			$this->domain_endings[] = strtolower($de);
		}
	}

	public function getMaxCaps() {
		return $this->maxCaps;
	}

	public function getDomainEndings() {
		return $this->domain_endings;
	}

	public function passedMaxCapsLimit($string) {
		return strlen(preg_replace("![^A-Z]+!", "", $string)) >= self::CAP_LIMIT;
	}

	public function isAdvertising($string) {
		$orig = $string;
		$string = $this->formatString($string);
		$string_array = explode(" ", strtolower($string));
		foreach ($string_array as $str) {
			if (is_numeric($str)) {
				if ($string >= 10000 && $string <= 25555) return true;
			}
		}
		foreach ($this->getDomainEndings() as $de) {
			$checks = ["." . $de, "," . $de];
			foreach ($checks as $check) {
				if (stripos($orig, $check)) {
					if (stristr($orig, Links::MAIN)) {
						return false;
					} else {
						return true;
					}
				}
			}
		}
		return false;
	}

	public function formatString($string) {
		$string = strtolower(implode("", explode(" ", $string)));

		foreach ([
			"&" => "a", "@" => "a", "4" => "a",
			"3" => "e",
			"6" => "g",
			"!" => "i", "1" => "i", "|" => "i",
			"0" => "o",
			"$" => "s", "5" => "s",
			"+" => "t",
		] as $old => $to) $string = str_replace($old, $to, $string);

		$new = "";
		$array = str_split($string);
		foreach ($array as $key => $letter) {
			if (substr($new, -1) != $letter) {
				if (ctype_alnum($letter)) {
					$new .= $letter;
				}
			}
		}

		return $string;
	}

	public function getStringOffense($string): int {
		if ($this->isAdvertising($string)) return 1;
		return 0;
	}
}
