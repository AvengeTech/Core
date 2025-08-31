<?php

namespace core\chat\emoji;

class EmojiCategory {

	public array $emojis = [];

	public function __construct(
		public int $type,
		public string $name
	) {
	}

	public function getType(): int {
		return $this->type;
	}

	public function getName(): string {
		return $this->name;
	}

	public function addEmoji(Emoji $emoji): void {
		$this->emojis[$emoji->getName()] = $emoji;
	}

	public function getEmojis(): array {
		return $this->emojis;
	}

	public function getEmoji(string $name): ?Emoji {
		return $this->emojis[$name] ?? null;
	}
}
