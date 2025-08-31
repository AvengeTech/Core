<?php

namespace core\utils\item\component;

use core\utils\item\CreativeInventoryInfo;

/**
 * From Customies
 */
final class CreativeCategoryComponent implements ItemComponent {

	private CreativeInventoryInfo $creativeInfo;

	public function __construct(CreativeInventoryInfo $creativeInfo) {
		$this->creativeInfo = $creativeInfo;
	}

	public function getName(): string {
		return "creative_category";
	}

	public function getValue(): int {
		return $this->creativeInfo->getNumericCategory();
	}

	public function isProperty(): bool {
		return true;
	}
}