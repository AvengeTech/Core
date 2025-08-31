<?php

namespace core\cosmetics\effect\idle\circle;

use pocketmine\math\Vector3;

use core\cosmetics\effect\idle\IdleEffect;

abstract class CircleIdleEffect extends IdleEffect {

	public function addCircle(Vector3 $pos): Vector3 {
		$i = 2 * M_PI / 70 * ($this->ticks % 70);
		$x = cos($i);
		$z = sin($i);

		return $pos->add($x, 0.1, $z);
	}
}
