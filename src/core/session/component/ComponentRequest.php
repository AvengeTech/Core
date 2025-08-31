<?php

namespace core\session\component;

use core\session\mysqli\data\{
	MySqlRequest,
	MySqlQuery
};

class ComponentRequest extends MySqlRequest {

	public $xuid;

	public function __construct(int $xuid, string $componentName, MySqlQuery|array $queries) {
		$this->xuid = $xuid;
		parent::__construct($componentName, $queries);
	}

	public function getXuid(): int {
		return $this->xuid;
	}

	public function getComponentName(): string {
		return $this->getId(); //reverse compatible
	}
}
