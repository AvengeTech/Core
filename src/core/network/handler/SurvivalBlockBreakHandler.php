<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
 */

declare(strict_types=1);

namespace core\network\handler;

use core\AtPlayer;
use pocketmine\block\Block;
use pocketmine\block\BlockBreakInfo;
use pocketmine\entity\animation\ArmSwingAnimation;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\Pickaxe;
use pocketmine\item\TieredTool;
use pocketmine\item\Tool;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelEvent;
use pocketmine\world\particle\BlockPunchParticle;
use pocketmine\world\sound\BlockPunchSound;
use function abs;

final class SurvivalBlockBreakHandler {

	public const DEFAULT_FX_INTERVAL_TICKS = 5;

	private int $fxTicker = 0;
	private float $breakSpeed;
	private float $breakProgress = 0;

	public function __construct(
		private AtPlayer $player,
		private Vector3 $blockPos,
		private Block $block,
		private int $targetedFace,
		private int $maxPlayerDistance,
		private int $fxTickInterval = self::DEFAULT_FX_INTERVAL_TICKS
	) {
		$this->breakSpeed = $this->calculateBreakProgressPerTick();
		if ($this->breakSpeed > 0) {
			$this->player->getWorld()->broadcastPacketToViewers(
				$this->blockPos,
				LevelEventPacket::create(LevelEvent::BLOCK_START_BREAK, (int) (65535 * $this->breakSpeed), $this->blockPos)
			);
		}
		if ($this->breakSpeed >= 1) $this->breakProgress = 1;
	}

	/**
	 * Returns the calculated break speed as percentage progress per game tick.
	 */
	private function calculateBreakProgressPerTick(): float {
		if (!$this->block->getBreakInfo()->isBreakable()) {
			return 0.0;
		}
		//TODO: improve this to take stuff like swimming, ladders, enchanted tools into account, fix wrong tool break time calculations for bad tools (pmmp/PocketMine-MP#211)
		$breakTimePerTick = $this->getBreakTime($this->block, $this->player->getInventory()->getItemInHand()) * 20;

		if ($breakTimePerTick > 0) {
			return 1 / $breakTimePerTick;
		}
		return 1;
	}

	public function update(): bool {
		if ($this->player->getPosition()->distanceSquared($this->blockPos->add(0.5, 0.5, 0.5)) > $this->maxPlayerDistance ** 2) {
			return false;
		}

		$newBreakSpeed = $this->calculateBreakProgressPerTick();
		if (abs($newBreakSpeed - $this->breakSpeed) > 0.0001) {
			$this->breakSpeed = $newBreakSpeed;

			$this->player->getWorld()->broadcastPacketToViewers(
				$this->blockPos,
				LevelEventPacket::create(LevelEvent::BLOCK_BREAK_SPEED, (int) (65535 * $this->breakSpeed), $this->blockPos)
			);
		}

		$this->breakProgress += $this->breakSpeed;

		if (($this->fxTicker++ % $this->fxTickInterval) === 0 && $this->breakProgress < 1) {
			$this->player->getWorld()->addParticle($this->blockPos, new BlockPunchParticle($this->block, $this->targetedFace));
			$this->player->getWorld()->addSound($this->blockPos, new BlockPunchSound($this->block));
			$this->player->broadcastAnimation(new ArmSwingAnimation($this->player), $this->player->getViewers());
		}

		return $this->breakProgress < 1;
	}

	public function getBlockPos(): Vector3 {
		return $this->blockPos;
	}

	public function getTargetedFace(): int {
		return $this->targetedFace;
	}

	public function setTargetedFace(int $face): void {
		Facing::validate($face);
		$this->targetedFace = $face;
	}

	public function getBreakSpeed(): float {
		return $this->breakSpeed;
	}

	public function getBreakProgress(): float {
		return $this->breakProgress;
	}

	/**
	 * Returns the seconds that this block takes to be broken using an specific Item
	 *
	 * @throws \InvalidArgumentException if the item efficiency is not a positive number
	 */
	protected function getBreakTime(Block $block, Item $item): float {
		$breakInfo = $block->getBreakInfo();
		$base = $breakInfo->getHardness();
		if ($breakInfo->isToolCompatible($item)) {
			$base *= BlockBreakInfo::COMPATIBLE_TOOL_MULTIPLIER;
		} else {
			$base *= BlockBreakInfo::INCOMPATIBLE_TOOL_MULTIPLIER;
		}

		$efficiency = $item->getMiningEfficiency(($breakInfo->getToolType() & $item->getBlockToolType()) !== 0) * $this->getHasteMultiplier();
		if ($efficiency <= 0) {
			throw new \InvalidArgumentException(get_class($item) . " has invalid mining efficiency: expected >= 0, got $efficiency");
		}

		$base /= $efficiency;

		return $base;
	}

	protected function getHasteMultiplier(): float {
		$hasteLevel = $this->player->getEffects()->get(VanillaEffects::HASTE())?->getEffectLevel() ?? 0;

		return 1 + $hasteLevel;
	}

	public function __destruct() {
		if ($this->player->getWorld()->isInLoadedTerrain($this->blockPos)) {
			$this->player->getWorld()->broadcastPacketToViewers(
				$this->blockPos,
				LevelEventPacket::create(LevelEvent::BLOCK_STOP_BREAK, 0, $this->blockPos)
			);
		}
	}
}
