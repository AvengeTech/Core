<?php

namespace core\cosmetics\item;

use pocketmine\entity\Location;
use pocketmine\entity\projectile\Throwable;
use pocketmine\item\Snowball as PMSnowball;
use pocketmine\player\Player;

use core\cosmetics\entity\Snowball as SnowballEntity;

class Snowball extends PMSnowball {

	protected function createEntity(Location $location, Player $thrower): Throwable {
		return new SnowballEntity($location, $thrower);
	}
}
