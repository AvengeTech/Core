<?php

namespace core\utils\item\component;

/**
 * From Customies
 */
final class ThrowableComponent implements ItemComponent {

	private bool $doSwingAnimation;

	public function __construct(bool $doSwingAnimation) {
		$this->doSwingAnimation = $doSwingAnimation;
	}

	public function getName(): string {
		return "minecraft:throwable";
	}

	public function getValue(): array {
		return [
			"do_swing_animation" => $this->doSwingAnimation
		];
	}

	public function isProperty(): bool {
		return false;
	}
}