<?php

namespace core\vote\prize;

use core\AtPlayer as Player;
use core\utils\TextFormat;

class PrizeCluster{

	public function __construct(
		public int $day,
		public array $items = []
	) {
	}

	public function isDaily(): bool{
		return $this->getDay() == 0;
	}

	public function isLastDay(): bool{
		return $this->getDay() == 31;
	}

	public function getDay(): int{
		return $this->day;
	}

	public function getItems(): array{
		return $this->items;
	}

	public function addItem(PrizeItem $item): void{
		$this->items[] = $item;
	}

	public function reward(Player $player): void{
		foreach ($this->getItems() as $prizeItem){
			$prizeItem->give($player);
		}
	}

	public function getItemList(): string{
		$list = "";
		$cr = 0;
		$clrs = [
			TextFormat::AQUA,
			TextFormat::YELLOW,
		];
		foreach ($this->getItems() as $item) {
			$list .= TextFormat::GRAY . "- " . $clrs[$cr] . $item->getName() . PHP_EOL;
			$cr++;
			if ($cr > 1) $cr = 0;
		}
		return $list . TextFormat::RESET;
	}
}
