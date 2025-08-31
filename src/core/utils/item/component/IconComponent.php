<?php

namespace core\utils\item\component;

/**
 * From Customies
 */
final class IconComponent implements ItemComponent{

	private string $texture;

	public function __construct(string $texture) {
		$this->texture = $texture;
	}

	public function getName(): string {
		return "minecraft:icon";
	}

	public function getValue(): array {
		return [
			"textures" => [
				"default" => $this->texture
			]
		];
	}

	public function isProperty(): bool {
		return true;
	}
}