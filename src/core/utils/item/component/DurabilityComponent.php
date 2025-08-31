<?php

namespace core\utils\item\component;

/**
 * From Customies
 */
final class DurabilityComponent implements ItemComponent {

	private int $maxDurability;

	public function __construct(int $maxDurability) {
		$this->maxDurability = $maxDurability;
	}

	public function getName(): string {
		return "minecraft:durability";
	}

	public function getValue(): array {
		return [
			"max_durability" => $this->maxDurability
		];
	}

	public function isProperty(): bool {
		return false;
	}
}