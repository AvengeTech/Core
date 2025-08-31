<?php

namespace core\rules;

class RuleCategory {

	const KEY_NAME = 0;
	const KEY_ORDER = 1;

	public $name;
	public $color;

	public $servers = [];
	public $rules = [];

	public function __construct(string $name, string $color, array $servers = [], array $rules = []) {
		$this->name = $name;
		$this->color = $color;
		$this->servers = $servers;
		$this->rules = $rules;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getColor(): string {
		return $this->color;
	}

	public function getServers(): array {
		return $this->servers;
	}

	public function getRules(int $key = self::KEY_NAME): array {
		return $key == self::KEY_NAME ? $this->rules : array_values($this->rules);
	}

	public function getRuleByName(string $name): ?Rule {
		return $this->rules[$name] ?? null;
	}
}
