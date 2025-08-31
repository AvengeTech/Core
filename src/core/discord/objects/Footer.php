<?php

namespace core\discord\objects;

class Footer {

	public $text;
	public $icon_url;

	public function __construct(string $text = "", string $icon_url = "") {
		$this->text = $text;
		$this->icon_url = $icon_url;
	}

	public function getText(): string {
		return $this->text;
	}

	public function getIconUrl(): string {
		return $this->icon_url;
	}

	public function toArray(): array {
		return [
			"text" => $this->getText(),
			"icon_url" => $this->getIconUrl()
		];
	}
}
