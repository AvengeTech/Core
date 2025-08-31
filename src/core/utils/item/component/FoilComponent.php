<?php

namespace core\utils\item\component;

/**
 * From Customies
 */
final class FoilComponent implements ItemComponent {

	private bool $foil;

	public function __construct(bool $foil = true) {
		$this->foil = $foil;
	}

	public function getName(): string {
		return "foil";
	}

	public function getValue(): bool {
		return $this->foil;
	}

	public function isProperty(): bool {
		return true;
	}
}