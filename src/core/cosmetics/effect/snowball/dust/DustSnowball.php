<?php

namespace core\cosmetics\effect\snowball\dust;

use pocketmine\world\sound\Sound;

use core\cosmetics\effect\snowball\SimpleSnowballEffect;

abstract class DustSnowball extends SimpleSnowballEffect {

	public function getSound(): ?Sound {
		return null;
	}
}
