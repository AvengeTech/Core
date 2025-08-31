<?php

namespace  core\tutorial\sequence;

use pocketmine\entity\Location;

use core\AtPlayer as Player;

class TechieSequence extends Sequence {

	public $techie = null;

	public function __construct(
		string $name,
		int $length,
		Location $location,
		string $message = "",
		string $title = "",
		string $subTitle = "",
		array $titleLengths = [],
		public string $movementFile
	) {
		parent::__construct($name, $length, $location, $message, $title, $subTitle, $titleLengths);
	}

	public function tick(): bool {
		//move techie
		return parent::tick();
	}

	public function getMovementFilePath(): string {
		return $this->movementFile;
	}

	public function start(Player $player): void {
		parent::start($player);
		//spawn special techie with movement file
	}

	public function end(Player $player): void {
		parent::end($player);
		//despawn techie
	}
}
