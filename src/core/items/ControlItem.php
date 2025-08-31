<?php

namespace core\items;

use pocketmine\item\Item;

class ControlItem extends Item {

	public function getMaxStackSize(): int
	{
		return 1;
	}

	public function getCanPlaceOn(): array {
		return [];
	}

	public function getCanDestroy(): array {
		return [];
	}

}