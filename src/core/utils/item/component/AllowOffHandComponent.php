<?php

namespace core\utils\item\component;

/**
 * From Customies
 */
final class AllowOffHandComponent implements ItemComponent {

	private bool $offHand;

	public function __construct(bool $offHand = true) {
		$this->offHand = $offHand;
	}

	public function getName(): string {
		return "allow_off_hand";
	}

	public function getValue(): bool {
		return $this->offHand;
	}

	public function isProperty(): bool {
		return true;
	}
}