<?php

namespace core\staff\anticheat;

use pocketmine\world\Position;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\block\utils\StairShape;
use pocketmine\math\Facing;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Location;

class Calculations {

	public static Vector3 $zero;

	public function __construct() {
		self::$zero = new Vector3(0, 0, 0);
	}

	public static function fakeLookAt(\pocketmine\entity\Entity $from, Vector3 $target): Location {
		$location = new Location($target->x, $target->y, $target->z, $from->getLocation()->world, $from->getLocation()->yaw, $from->getLocation()->pitch);
		$horizontal = sqrt(($target->x - $from->getLocation()->x) ** 2 + ($target->z - $from->getLocation()->z) ** 2);
		$vertical = $target->y - ($from->getLocation()->y + $from->getEyeHeight());
		$location->pitch = -atan2($vertical, $horizontal) / M_PI * 180;

		$xDist = $target->x - $from->getLocation()->x;
		$zDist = $target->z - $from->getLocation()->z;
		$location->yaw = atan2($zDist, $xDist) / M_PI * 180 - 90;
		if ($location->yaw < 0) {
			$location->yaw += 360.0;
		}
		return $location;
	}

	public static function fakeRotate(float $pitch = 0, float $yaw = 0, Location $current): Location
	{
		$clone = clone $current;
		$clone->pitch += $pitch;
		$clone->yaw += $yaw;
		return $clone;
	}

	public static function locationToDirectionVector(Location $location): Vector3 {
		$y = -sin(deg2rad($location->pitch));
		$xz = cos(deg2rad($location->pitch));
		$x = -$xz * sin(deg2rad($location->yaw));
		$z = $xz * cos(deg2rad($location->yaw));

		return (new Vector3($x, $y, $z))->normalize();
	}

	public static function isValidPosition(Position $pos): bool {
		$blockAt = $pos->getWorld()->getBlock($pos->asVector3()->floor());
		$headBlock = $pos->getWorld()->getBlock($pos->asVector3()->floor()->add(0, 1, 0));

		$semiSolids = [
			"stair",
			"slab",
			"fence",
			"gate"
		];
		$isSemiSolid = false;
		foreach ($semiSolids as $n) {
			$isSemiSolid = $isSemiSolid || strpos(strtolower($blockAt->getName()), $n) !== false || strpos(strtolower($headBlock->getName()), $n) !== false;
		}
		if ($isSemiSolid) {
			$blockIsStair = strpos(strtolower($blockAt->getName()), "stair") !== false;
			$headIsStair = strpos(strtolower($headBlock->getName()), "stair") !== false;
			$blockIsSlab = strpos(strtolower($blockAt->getName()), "slab") !== false;
			$headIsSlab = strpos(strtolower($headBlock->getName()), "slab") !== false;
			$blockIsFence = strpos(strtolower($blockAt->getName()), "fence") !== false;
			$headIsFence = strpos(strtolower($headBlock->getName()), "fence") !== false;

			if ($headIsStair || $headIsSlab) return false;

			if ($blockIsSlab) {
				$meta = $blockAt->getStateId();
				if ($meta >= 8 && $meta <= 15) {
					return false;
				} else {
					return $pos->asVector3()->y >= $blockAt->getPosition()->asVector3()->y + 0.5;
				}
			}
			if ($blockIsStair && $blockAt instanceof \pocketmine\block\Stair) {
				$meta = $blockAt->getStateId();
				$pVector = $pos->asVector3();
				$bVector = $blockAt->getPosition()->asVector3();
				/** @var AxisAlignedBB */
				$boundaries = self::calculateCollisionBoxes($blockAt)[1];
				$boundaries->offset($bVector->x, $bVector->y, $bVector->z);
				return self::isVectorInside($boundaries, $pVector);
			}
			if ($blockIsFence || $headIsFence) {
				$pVector = $pos->asVector3();
				$bVector = $blockAt->getPosition()->asVector3();
				return ($pVector->x <= ($bVector->x + 0.5) - 0.25 || $pVector->x >= ($bVector->x + 0.5) + 0.25) && ($pVector->z <= ($bVector->z + 0.5) - 0.25 || $pVector->z >= ($bVector->z + 0.5) + 0.25);
			}
		}
		return !(($blockAt->isSolid() && $blockAt->isFullCube()) || ($headBlock->isSolid() && $headBlock->isFullCube()));
	}

	public static function isVectorInside(AxisAlignedBB $bb, Vector3 $vector): bool {
		$offset = 0.5;
		if ($vector->x <= $bb->minX - $offset or $vector->x >= $bb->maxX + $offset) {
			return false;
		}
		if ($vector->y <= $bb->minY - $offset or $vector->y >= $bb->maxY + $offset) {
			return false;
		}

		return $vector->z >= $bb->minZ - $offset or $vector->z <= $bb->maxZ + $offset;
	}

	public static function calculateCollisionBoxes(\pocketmine\block\Stair $block): array {
		$topStepFace = $block->isUpsideDown() ? Facing::DOWN : Facing::UP;
		$bbs = [
			AxisAlignedBB::one()->trim($topStepFace, 0.5)
		];

		$topStep = AxisAlignedBB::one()
			->trim(Facing::opposite($topStepFace), 0.5)
			->trim(Facing::opposite($block->getFacing()), 0.5);

		if ($block->getShape()->equals(StairShape::OUTER_LEFT()) or $block->getShape()->equals(StairShape::OUTER_RIGHT())) {
			$topStep->trim(Facing::rotateY($block->getFacing(), $block->getShape()->equals(StairShape::OUTER_LEFT())), 0.5);
		} elseif ($block->getShape()->equals(StairShape::INNER_LEFT()) or $block->getShape()->equals(StairShape::INNER_RIGHT())) {
			//add an extra cube
			$bbs[] = AxisAlignedBB::one()
				->trim(Facing::opposite($topStepFace), 0.5)
				->trim($block->getFacing(), 0.5) //avoid overlapping with main step
				->trim(Facing::rotateY($block->getFacing(), $block->getShape()->equals(StairShape::INNER_LEFT())), 0.5);
		}

		$bbs[] = $topStep;

		return $bbs;
	}

	public static function findGround(Position $pos): Position {
		$block = VanillaBlocks::AIR();
		$originalY = $pos->y;
		$pos->y -= 1;
		while (!$block->isSolid() && $pos->y !== $originalY) {
			$block = $pos->getWorld()->getBlock($pos);
			$pos = new Position($pos->getX(), $pos->getY() - 1, $pos->getZ(), $pos->getWorld());
			if ($pos->y - 1 < 0) $pos->y = 255;
		}
		if ($pos->y == $originalY) $block = $pos->getWorld()->getBlock($pos);
		return new Position($pos->x, $block->getPosition()->add(0, 1, 0)->getY(), $pos->z, $block->getPosition()->getWorld());
	}
}
