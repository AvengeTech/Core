<?php

namespace core\rules;

class RuleManager {

	const KEY_NAME = 0;
	const KEY_ORDER = 1;

	public $categories = [];

	public function __construct() {
		foreach (RuleData::RULES["categories"] as $category) {
			$name = $category["name"];
			$color = $category["color"];
			$servers = $category["servers"];
			$rules = [];
			foreach ($category["rules"] as $rule) {
				$rname = $rule["name"];
				$rdesc = $rule["description"];
				$rules[$rname] = new Rule($rname, $rdesc);
			}
			$this->categories[$name] = new RuleCategory($name, $color, $servers, $rules);
		}
	}

	public function getCategory(string $name): ?RuleCategory {
		return $this->categories[$name] ?? null;
	}

	public function getCategories(int $key = self::KEY_NAME) {
		return $key == self::KEY_NAME ? $this->categories : array_values($this->categories);
	}
}
