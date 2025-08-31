<?php

namespace core\utils;

use pocketmine\math\Vector3;

final class Ray {

	/** @var Vector3 */
	public Vector3 $origin;
	public Vector3 $direction;

	public function __construct(Vector3 $origin, Vector3 $direction) {
		$this->origin = $origin;
		$this->direction = $direction;
	}

	public function origin(int $i): float {
		return [$this->origin->getX(), $this->origin->getY(), $this->origin->getZ()][$i] ?? 0.001;
	}

	public function direction(int $i): float {
		return [$this->direction->getX(), $this->direction->getY(), $this->direction->getZ()][$i] ?? 0.001;
	}

	public function traverse(float $distance): Vector3 {
		return $this->origin->addVector($this->direction->multiply($distance));
	}

	public function getOrigin(): Vector3 {
		return $this->origin;
	}

	public function getDirection(): Vector3 {
		return $this->direction;
	}
}
