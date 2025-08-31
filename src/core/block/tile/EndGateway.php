<?php

namespace core\block\tile;

use pocketmine\block\tile\Spawnable;
use pocketmine\nbt\tag\CompoundTag;

class EndGateway extends Spawnable{

	const TAG_AGE = "Age";

	private int $age = -100;

	public function readSaveData(CompoundTag $nbt) : void{
		$this->setAge($nbt->getInt(self::TAG_AGE, -100));
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$nbt->setInt(self::TAG_AGE, $this->getAge());
	}

	public function getAge() : int{
		return $this->age;
	}

	public function setAge(int $age) : self{
		$this->age = $age;

		return $this;
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
		// empty
	}
}