<?php

namespace core\utils\item\component;

/**
 * From Customies
 */
final class FoodComponent implements ItemComponent {

	private bool $canAlwaysEat;

	public function __construct(bool $canAlwaysEat = false) {
		$this->canAlwaysEat = $canAlwaysEat;
	}

	public function getName(): string {
		return "minecraft:food";
	}

	public function getValue(): array {
		return [
			"can_always_eat" => $this->canAlwaysEat
		];
	}

	public function isProperty(): bool {
		return false;
	}
}