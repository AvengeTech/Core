<?php

namespace  core\tutorial\sequence;

use pocketmine\entity\Location;

class RotationSequence extends Sequence {

	public function __construct(
		string $name,
		int $length,
		Location $location,
		public int $endingRotation,
		string $message = "",
		string $title = "",
		string $subTitle = "",
		array $titleLengths = []
	) {
		parent::__construct($name, $length, $location, $message, $title, $subTitle, $titleLengths);
	}

	public function getStartingRotation(): int {
		return $this->getLocation()->getYaw();
	}

	public function getEndingRotation(): int {
		return $this->endingRotation;
	}

	public function getRotationPerTick(): int {
		return (int) (($this->getEndingRotation() - $this->getStartingRotation()) / $this->getLength());
	}
}
