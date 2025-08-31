<?php

namespace core\cosmetics\effect\arrow\dust;

use pocketmine\world\sound\Sound;

use core\cosmetics\effect\arrow\SimpleArrowEffect;

abstract class DustArrow extends SimpleArrowEffect {

	public function getSound(): ?Sound {
		return null;
	}
}
