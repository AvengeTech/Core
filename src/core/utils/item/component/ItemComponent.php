<?php

namespace core\utils\item\component;

/**
 * From Customies
 */
interface ItemComponent {

	public function getName(): string;

	public function getValue(): mixed;

	public function isProperty(): bool;
}