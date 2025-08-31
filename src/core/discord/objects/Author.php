<?php

namespace core\discord\objects;

class Author {

	public $name;
	public $url = "";

	public function __construct(string $name, string $url = "") {
		$this->name = $name;
		$this->url = $url;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getUrl(): string {
		return $this->url;
	}

	public function toArray(): array {
		return [
			"name" => $this->getName(),
			"url" => $this->getUrl(),
		];
	}
}
