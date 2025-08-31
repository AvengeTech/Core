<?php

namespace core\block;

use core\block\tile\EndGateway as TileEndGateway;
use pocketmine\block\Block;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\Limits;
use pocketmine\world\BlockTransaction;

class EndGateway extends EndPortal{

	const MIN_AGE = -100;
	const MAX_AGE = 100;

	protected int $age = -100;

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if(parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player)){
			$tile = $this->getPosition()->getWorld()->getTile($blockReplace->getPosition());

			if(!(is_null($tile)) && $tile instanceof TileEndGateway){
				$tile->setAge($this->getAge());
			}
			return true;
		}
		
		return false;
	}

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->boundedIntAuto(self::MIN_AGE, self::MAX_AGE, $this->age);
	}

	public function getAge() : int{ return $this->age; }

	/**
	 * @return $this
	 */
	public function setAge(int $age) : self{
		$this->age = max($age, self::MIN_AGE);
		return $this;
	}
}