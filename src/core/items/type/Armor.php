<?php

namespace core\items\type;

use core\Core;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Armor as PMArmor;
use pocketmine\item\enchantment\ProtectionEnchantment;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\nbt\tag\CompoundTag;
use prison\enchantments\Enchantments as PrisonE;
#use prison\enchantments\type\armor\ProtectionEnchantment as PrisonProtectionEnchantment;

use skyblock\enchantments\EnchantmentData as SkyBlockED;
use skyblock\enchantments\EnchantmentRegistry as SkyBlockE;
use skyblock\enchantments\type\armor\ProtectionEnchantment as SkyBlockProtectionEnchantment;

class Armor extends PMArmor {

	public const MATERIAL_AMETHYST = "amethyst";
	public const MATERIAL_COPPER = "copper";
	public const MATERIAL_DIAMOND = "diamond";
	public const MATERIAL_EMERALD = "emerald";
	public const MATERIAL_GOLD = "gold";
	public const MATERIAL_IRON = "iron";
	public const MATERIAL_LAPIS = "lapis";
	public const MATERIAL_NETHERITE = "netherite";
	public const MATERIAL_REDSTONE = "redstone";
	public const MATERIAL_QUARTZ = "quartz";

	public const MATERIALS = [
		self::MATERIAL_AMETHYST,
		self::MATERIAL_COPPER,
		self::MATERIAL_DIAMOND,
		self::MATERIAL_EMERALD,
		self::MATERIAL_GOLD,
		self::MATERIAL_IRON,
		self::MATERIAL_LAPIS,
		self::MATERIAL_NETHERITE,
		self::MATERIAL_REDSTONE,
		self::MATERIAL_QUARTZ
	];

	public const PATTERN_COAST = "coast";
	public const PATTERN_DUNE = "dune";
	public const PATTERN_EYE = "eye";
	public const PATTERN_HOST = "host";
	public const PATTERN_RAISER = "raiser";
	public const PATTERN_RIB = "rib";
	public const PATTERN_SENTRY = "sentry";
	public const PATTERN_SHAPER = "shaper";
	public const PATTERN_SILENCE = "silence";
	public const PATTERN_SNOUT = "snout";
	public const PATTERN_SPIRE = "spire";
	public const PATTERN_TIDE = "tide";
	public const PATTERN_VEX = "vex";
	public const PATTERN_WARD = "ward";
	public const PATTERN_WAYFINDER = "wayfinder";
	public const PATTERN_WILD = "wild";

	public const PATTERNS = [
		self::PATTERN_COAST,
		self::PATTERN_DUNE,
		self::PATTERN_EYE,
		self::PATTERN_HOST,
		self::PATTERN_RAISER,
		self::PATTERN_RIB,
		self::PATTERN_SENTRY,
		self::PATTERN_SHAPER,
		self::PATTERN_SILENCE,
		self::PATTERN_SNOUT,
		self::PATTERN_SPIRE,
		self::PATTERN_TIDE,
		self::PATTERN_VEX,
		self::PATTERN_WARD,
		self::PATTERN_WAYFINDER,
		self::PATTERN_WILD
	];

	#region PROTECTION
	private const PROTECTION_MODS = [
		"fire" => [
			EntityDamageEvent::CAUSE_FIRE,
			EntityDamageEvent::CAUSE_FIRE_TICK,
			EntityDamageEvent::CAUSE_LAVA
			//TODO: check fireballs
		],
		"proj" => [
			EntityDamageEvent::CAUSE_PROJECTILE
		],
		"blast" => [
			EntityDamageEvent::CAUSE_BLOCK_EXPLOSION,
			EntityDamageEvent::CAUSE_ENTITY_EXPLOSION
		]
	];

	public function getEnchantmentProtectionFactor(EntityDamageEvent $event): int {
		switch (Core::thisServer()->getType()) {
			case "prison":
				#return $this->prisonEnchantmentProtectionFactor($event);
				break;
			case "skyblock":
				return $this->skyblockEnchantmentProtectionFactor($event);
				break;
			default:
				return $this->coreEnchantmentProtectionFactor($event);
		}
		return $this->coreEnchantmentProtectionFactor($event);
	}

	public function coreEnchantmentProtectionFactor(EntityDamageEvent $event): int {
		$epf = 0;

		foreach ($this->getEnchantments() as $enchantment) {
			$type = $enchantment->getType();
			if ($type instanceof ProtectionEnchantment && $type->isApplicable($event)) {
				$epf += $type->getProtectionFactor($enchantment->getLevel());
			}
		}

		return $epf;
	}
	public function prisonEnchantmentProtectionFactor(EntityDamageEvent $event): int {
		$epf = 0;

		foreach ($this->getEnchantments() as $enchantment) {
			$type = $enchantment->getType();
			#if ($type instanceof PrisonProtectionEnchantment && $type->isApplicable($event)) {
			#	$epf += $type->getProtectionFactor($enchantment->getLevel());
			#}
		}

		return $epf;
	}

	public function skyblockEnchantmentProtectionFactor(EntityDamageEvent $event): int {
		$epf = 0;

		foreach ($this->getEnchantments() as $enchantment) {
			$type = $enchantment->getType();
			if ($type instanceof SkyBlockProtectionEnchantment && $type->isApplicable($event)) {
				$epf += $type->getProtectionFactor($enchantment->getLevel());
			}
		}

		return $epf;
	}

	protected function getProtectionFactor(int $level, float $mod): int {
		return (int) floor((6 + $level ** 2) * $mod / 3);
	}
	#endregion

	#region UNBREAKING
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
				if (mt_rand(1, 100) > 60 && lcg_value() > $chance) {
					$negated++;
				}
			}

			return $negated;
		}

		return 0;
	}

	protected function prisonUnbreakingDamageReduction(int $amount): int {
		if (($unbreakingLevel = $this->getEnchantmentLevel(PrisonE::UNBREAKING()->getEnchantment())) > 0) {
			$negated = 0;

			$chance = 1 / ($unbreakingLevel + 1);
			for ($i = 0; $i < $amount; ++$i) {
				if (mt_rand(1, 100) > 60 && lcg_value() > $chance) {
					$negated++;
				}
			}

			return $negated;
		}

		return 0;
	}

	protected function skyblockUnbreakingDamageReduction(int $amount): int {
		if (($unbreakingLevel = $this->getEnchantmentLevel(SkyBlockE::UNBREAKING()->getEnchantment())) > 0) {
			$negated = 0;

			$chance = 1 / ($unbreakingLevel + 1);
			for ($i = 0; $i < $amount; ++$i) {
				if (mt_rand(1, 100) > 60 && lcg_value() > $chance) {
					$negated++;
				}
			}

			return $negated;
		}

		return 0;
	}
	#endregion

	public function setTrim(string $material, string $pattern) : self{
		$tag = $this->getNamedTag()->getCompoundTag("Trim");

		if(is_null($tag)) $tag = new CompoundTag();

		$tag->setString("Material", $material);
		$tag->setString("Pattern", $pattern);

		$this->getNamedTag()->setTag("Trim", $tag);
		return $this;
	}
}
