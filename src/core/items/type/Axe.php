<?php

namespace core\items\type;

use core\Core;
use pocketmine\item\Axe as PMAxe;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\ToolTier;
use prison\enchantments\Enchantments as PrisonEnchantments;
use skyblock\enchantments\EnchantmentRegistry as SkyBlockEnchantments;

class Axe extends PMAxe {

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

	#region OVERRIDES

	public function setTier(ToolTier $tier): void {
		$this->tier = $tier;
	}

	#endregion


}