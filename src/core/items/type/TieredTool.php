<?php

namespace core\items\type;

use core\Core;
use pocketmine\block\Block;
use pocketmine\block\BlockToolType;
use pocketmine\entity\Entity;
use pocketmine\item\Axe;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Hoe;
use pocketmine\item\Item;
use pocketmine\item\Pickaxe;
use pocketmine\item\Shovel;
use pocketmine\item\Sword;
use pocketmine\item\TieredTool as PMTieredTool;
use pocketmine\item\ToolTier;
use pocketmine\item\VanillaItems;
use prison\enchantments\Enchantments as PrisonEnchantments;
use skyblock\enchantments\EnchantmentRegistry as SkyBlockEnchantments;

class TieredTool extends PMTieredTool {

	private int $toolType = 0;
	private int $harvestLevel = 0;

	private ?ToolTier $tierOverride = null;

	public function __construct(...$args)
	{
		parent::__construct(...$args);

		if (in_array($this->getTypeId(), [
			VanillaItems::WOODEN_SWORD()->getTypeId(),
			VanillaItems::GOLDEN_SWORD()->getTypeId(),
			VanillaItems::STONE_SWORD()->getTypeId(),
			VanillaItems::IRON_SWORD()->getTypeId(),
			VanillaItems::DIAMOND_SWORD()->getTypeId(),
			VanillaItems::NETHERITE_SWORD()->getTypeId(),
		])){
			$this->tierOverride = $this->tier = match ($this->getTypeId()) {
				VanillaItems::WOODEN_SWORD()->getTypeId() => ToolTier::WOOD,
				VanillaItems::GOLDEN_SWORD()->getTypeId() => ToolTier::GOLD,
				VanillaItems::STONE_SWORD()->getTypeId() => ToolTier::STONE,
				VanillaItems::IRON_SWORD()->getTypeId() => ToolTier::IRON,
				VanillaItems::DIAMOND_SWORD()->getTypeId() => ToolTier::DIAMOND,
				VanillaItems::NETHERITE_SWORD()->getTypeId() => ToolTier::NETHERITE,
				default => ToolTier::WOOD, // Fallback to wood if something goes wrong
			};
		}
	}

	#region EFFICIENCY SHIT
	public function getMiningEfficiency(bool $isCorrectTool): float {
		switch (Core::thisServer()->getType()) {
			case "prison":
				return $this->prisonMiningEfficiency($isCorrectTool);
				break;
			case "skyblock":
				return $this->skyblockMiningEfficiency($isCorrectTool);
				break;
			default:
				return $this->coreMiningEfficiency($isCorrectTool);
		}
		return $this->coreMiningEfficiency($isCorrectTool);
	}

	private function coreMiningEfficiency(bool $isCorrectTool): float {
		$efficiency = 1;
		if ($isCorrectTool) {
			$efficiency = $this->getBaseMiningEfficiency();
			if (($enchantmentLevel = $this->getEnchantmentLevel(VanillaEnchantments::EFFICIENCY())) > 0) {
				$efficiency += ($enchantmentLevel ** 2 + 1);
			}
		}

		return $efficiency;
	}

	private function prisonMiningEfficiency(bool $isCorrectTool): float {
		$efficiency = 1;
		if ($isCorrectTool) {
			$efficiency = $this->getBaseMiningEfficiency();
			if (($enchantmentLevel = $this->getEnchantmentLevel(PrisonEnchantments::EFFICIENCY()->getEnchantment())) > 0) {
				$efficiency += ($enchantmentLevel ** 2 + 1);
			}
		}

		return $efficiency;
	}

	private function skyblockMiningEfficiency(bool $isCorrectTool): float {
		$efficiency = 1;
		if ($isCorrectTool) {
			$efficiency = $this->getBaseMiningEfficiency();
			if (($enchantmentLevel = $this->getEnchantmentLevel(SkyBlockEnchantments::EFFICIENCY()->getEnchantment())) > 0) {
				$efficiency += ($enchantmentLevel ** 2 + 1);
			}
		}

		return $efficiency;
	}
	#endregion



	#region UNBREAKING SHIT

	protected function getUnbreakingDamageReduction(int $amount): int {
		switch (Core::thisServer()->getType()) {
			case "prison":
				return $this->prisonUnbreakingDamageReduction($amount);
				break;
			case "skyblock":
				return $this->skyblockUnbreakingDamageReduction($amount);
				break;
			default:
				return $this->coreUnbreakingDamageReduction($amount);
		}
		return $this->coreUnbreakingDamageReduction($amount);
	}

	protected function coreUnbreakingDamageReduction(int $amount): int {
		if (($unbreakingLevel = $this->getEnchantmentLevel(VanillaEnchantments::UNBREAKING())) > 0) {
			$negated = 0;

			$chance = 1 / ($unbreakingLevel + 1);
			for ($i = 0; $i < $amount; ++$i) {
				if (lcg_value() > $chance) {
					$negated++;
				}
			}

			return $negated;
		}

		return 0;
	}

	protected function prisonUnbreakingDamageReduction(int $amount): int {
		if (($unbreakingLevel = $this->getEnchantmentLevel(PrisonEnchantments::UNBREAKING()->getEnchantment())) > 0) {
			$negated = 0;

			$chance = 1 / ($unbreakingLevel + 1);
			for ($i = 0; $i < $amount; ++$i) {
				if (lcg_value() > $chance) {
					$negated++;
				}
			}

			return $negated;
		}

		return 0;
	}

	protected function skyblockUnbreakingDamageReduction(int $amount): int {
		if (($unbreakingLevel = $this->getEnchantmentLevel(SkyBlockEnchantments::UNBREAKING()->getEnchantment())) > 0) {
			$negated = 0;

			$chance = 1 / ($unbreakingLevel + 1);
			for ($i = 0; $i < $amount; ++$i) {
				if (lcg_value() > $chance) {
					$negated++;
				}
			}

			return $negated;
		}

		return 0;
	}

	#endregion

	#region NEW
	public static function isSword(Item $item) : bool{
		return ($item instanceof TieredTool && $item->getBlockToolType() == BlockToolType::SWORD) || $item instanceof Sword;
	}

	public static function isPickaxe(Item $item) : bool{
		return ($item instanceof TieredTool && $item->getBlockToolType() == BlockToolType::PICKAXE) || $item instanceof Pickaxe;
	}

	public static function isAxe(Item $item) : bool{
		return ($item instanceof TieredTool && $item->getBlockToolType() == BlockToolType::AXE) || $item instanceof Axe;
	}

	public static function isShovel(Item $item) : bool{
		return ($item instanceof TieredTool && $item->getBlockToolType() == BlockToolType::SHOVEL) || $item instanceof Shovel;
	}

	public static function isHoe(Item $item) : bool{
		return ($item instanceof TieredTool && $item->getBlockToolType() == BlockToolType::HOE) || $item instanceof Hoe;
	}

	#endregion


	#region OVERRIDES
	public function setToolType(int $type) {
		$this->toolType = $type;
	}

	public function setHarvestLevel(int $level) {
		$this->harvestLevel = $level;
	}

	public function getBlockToolType(): int {
		return $this->toolType;
	}

	public function getBlockToolHarvestLevel(): int {
		return $this->harvestLevel;
	}

	public function setTier(ToolTier $tier): void {
		$this->tier = $tier;
	}

	public function onDestroyBlock(Block $block, array &$returnedItems): bool {
		$damage = 1;
		if ($this->getBlockToolType() == BlockToolType::SWORD) $damage = 2;
		if (!$block->getBreakInfo()->breaksInstantly()) {
			return $this->applyDamage($damage);
		}
		return false;
	}

	public function onAttackEntity(Entity $victim, array &$returnedItems): bool
	{
		$damage = 2;
		if ($this->getBlockToolType() == BlockToolType::SWORD) $damage = 1;
		return $this->applyDamage($damage);
	}
	#endregion



	#region VANILLA SHIT
	protected int $damage = 0;
	private bool $unbreakable = false;

	public function getMaxDurability(): int {
		return $this->tier->getMaxDurability();
	}

	protected function getBaseMiningEfficiency(): float {
		return 1;
	}

	public function getMaxStackSize(): int {
		return 1;
	}

	public function isUnbreakable(): bool {
		return $this->unbreakable;
	}

	public function setUnbreakable(bool $value = true): self {
		$this->unbreakable = $value;
		return $this;
	}

	public function applyDamage(int $amount): bool {
		if ($this->isUnbreakable() || $this->isBroken()) {
			return false;
		}

		$amount -= $this->getUnbreakingDamageReduction($amount);

		$this->damage = min($this->damage + $amount, $this->getMaxDurability());
		if ($this->isBroken()) {
			$this->onBroken();
		}

		return true;
	}

	public function getDamage(): int {
		return $this->damage;
	}

	public function setDamage(int $damage): Item {
		if ($damage < 0 || $damage > $this->getMaxDurability()) {
			throw new \InvalidArgumentException("Damage must be in range 0 - " . $this->getMaxDurability());
		}
		$this->damage = $damage;
		return $this;
	}

	protected function onBroken(): void {
		$this->pop();
		$this->setDamage(0);
	}

	public function isBroken(): bool {
		return $this->damage >= $this->getMaxDurability() || $this->isNull();
	}

	public function getAttackPoints(): int {
		if (!is_null($this->tierOverride)) {
			return $this->tierOverride->getBaseAttackPoints();
		}
		return $this->tier->getBaseAttackPoints();
	}
	#region
}
