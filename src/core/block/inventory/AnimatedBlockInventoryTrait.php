<?php

namespace core\block\inventory;

use core\AtPlayer;
use pocketmine\block\inventory\BlockInventoryTrait;
use pocketmine\player\Player;
use pocketmine\world\sound\Sound;
use function count;

trait AnimatedBlockInventoryTrait {
	use BlockInventoryTrait;

	public function getViewerCount(): int {
		return count($this->getViewers());
	}

	public function getNonVanishedViewerCount(): int {
		$count = 0;
		foreach ($this->getViewers() as $viewer) {
			/** @var AtPlayer $viewer */
			if (!$viewer->isVanished()) $count++;
		}
		return $count;
	}

	/**
	 * @return Player[]
	 * @phpstan-return array<int, Player>
	 */
	abstract public function getViewers(): array;

	abstract protected function getOpenSound(): Sound;

	abstract protected function getCloseSound(): Sound;

	public function onOpen(Player $who): void {
		/** @var AtPlayer $who */
		parent::onOpen($who);

		if ($this->holder->isValid() && $this->getNonVanishedViewerCount() === 1 && !$who->isVanished()) {
			//TODO: this shit really shouldn't be managed by the inventory
			$this->animateBlock(true);
			$this->holder->getWorld()->addSound($this->holder->add(0.5, 0.5, 0.5), $this->getOpenSound());
		}
	}

	abstract protected function animateBlock(bool $isOpen): void;

	public function onClose(Player $who): void {
		/** @var AtPlayer $who */
		if ($this->holder->isValid() && $this->getNonVanishedViewerCount() === 1 && !$who->isVanished()) {
			//TODO: this shit really shouldn't be managed by the inventory
			$this->animateBlock(false);
			$this->holder->getWorld()->addSound($this->holder->add(0.5, 0.5, 0.5), $this->getCloseSound());
		}
		parent::onClose($who);
	}
}
