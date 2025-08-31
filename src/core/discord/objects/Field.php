<?php

namespace core\discord\objects;

class Field {

	public $name;
	public $value;

	public $inline = false;

	public function __construct(string $name, string $value, bool $inline = false) {
		$this->name = $name;
		$this->value = $value;

		$this->inline = $inline;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getValue(): string {
		return $this->value;
	}

	public function inline(): bool {
		return $this->inline;
	}

	public function toArray(): array {
		return [
			"name" => $this->getName(),
			"value" => $this->getValue(),
			"inline" => $this->inline(),
		];
	}
}
