<?php

namespace core\utils\item\component;

/**
 * From Customies
 */
final class MaxStackSizeComponent implements ItemComponent {

	private int $maxStackSize;

	public function __construct(int $maxStackSize) {
		$this->maxStackSize = $maxStackSize;
	}

	public function getName(): string {
		return "max_stack_size";
	}

	public function getValue(): int {
		return $this->maxStackSize;
	}

	public function isProperty(): bool {
		return true;
	}
}