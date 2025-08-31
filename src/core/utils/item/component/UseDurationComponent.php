<?php

namespace core\utils\item\component;

/**
 * From Customies
 */
final class UseDurationComponent implements ItemComponent {

	private int $duration;

	public function __construct(int $duration) {
		$this->duration = $duration;
	}

	public function getName(): string {
		return "use_duration";
	}

	public function getValue(): int {
		return $this->duration;
	}

	public function isProperty(): bool {
		return true;
	}
}