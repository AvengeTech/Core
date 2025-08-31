<?php

namespace core\chat\emoji;

class Emoji {

	public function __construct(
		public string $textCode,
		public string $icon,
		public int $type,
		public array $alias = []
	) {
	}

	public function getName(): string {
		return ucwords(str_replace(":", "", str_replace("_", " ", $this->getTextCode())));
	}

	public function getTextCode(): string {
		return $this->textCode;
	}

	public function getIcon(): string {
		return $this->icon;
	}

	public function getType(): int {
		return $this->type;
	}

	public function getAlias(): array {
		return $this->alias;
	}
}
