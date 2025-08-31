<?php

namespace core\utils\item\component;

/**
 * From Customies
 */
final class HandEquippedComponent implements ItemComponent {

	private bool $handEquipped;

	public function __construct(bool $handEquipped = true) {
		$this->handEquipped = $handEquipped;
	}

	public function getName(): string {
		return "hand_equipped";
	}

	public function getValue(): bool {
		return $this->handEquipped;
	}

	public function isProperty(): bool {
		return true;
	}
}