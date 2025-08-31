<?php

namespace core\event\block;

use core\block\Chest;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;

final class ChestPairEvent extends Event implements Cancellable {
	use CancellableTrait;

	public function __construct(
		private Chest $left,
		private Chest $right
	) {
	}

	public function getLeft(): Chest {
		return $this->left;
	}

	public function getRight(): Chest {
		return $this->right;
	}
}
