<?php

namespace core\block;

use pocketmine\block\Transparent;
use pocketmine\item\Item;

class EndPortal extends Transparent{

	public function getLightLevel() : int{
		return 15;
	}

	public function isSolid() : bool{
		return true;
	}

	public function getDrops(Item $item) : array{
		return [];
	}
}