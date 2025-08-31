<?php

namespace core\utils;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\world\sound\Sound;

class PlaySound implements Sound{

	public function __construct(
		protected Vector3 $pos,
		protected string $sound,
		protected float $volume = 100,
		protected float $pitch = 1
	) {}

	public function getSound(): string {
		return $this->sound;
	}

	public function getVolume(): int {
		return $this->volume;
	}

	public function getPitch(): int {
		return $this->pitch;
	}

	public function encode(Vector3 $pos = null): array {
		$pk = new PlaySoundPacket;
		$pk->soundName = $this->getSound();
		$pk->x = $this->pos->getX();
		$pk->y = $this->pos->getY();
		$pk->z = $this->pos->getZ();
		$pk->volume = $this->getVolume();
		$pk->pitch = $this->getPitch();
		return [$pk];
	}
}
