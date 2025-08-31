<?php

namespace core\utils;

use core\crafting\ColoredShulkerRecipe;
use pocketmine\block\utils\CopperOxidation;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\crafting\CraftingManager;
use pocketmine\crafting\ExactRecipeIngredient;
use pocketmine\crafting\FurnaceRecipe;
use pocketmine\crafting\FurnaceType;
use pocketmine\crafting\ShapedRecipe;
use pocketmine\crafting\ShapelessRecipe;
use pocketmine\data\bedrock\block\BlockTypeNames;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\Server;
use prison\crafting\AncientDebrisRecipe;
use prison\crafting\NetheriteIngotRecipe;
use ReflectionClass;

class CraftingRegistry {

	private static ?CraftingManager $manager = null;

	public static function setup(string $serverType = "core"): void {
		self::$manager = Server::getInstance()->getCraftingManager();

		self::$manager->registerShapedRecipe(new ShapedRecipe(
			[
				"AAA",
				"ABA",
				"AAA"
			],
			[
				'A' => new ExactRecipeIngredient(VanillaItems::STICK()),
				'B' => new ExactRecipeIngredient(VanillaItems::STRING())
			],
			[VanillaItems::PAINTING()]
		));

		foreach (DyeColor::getAll() as $color) {
			self::$manager->registerShapelessRecipe(new ColoredShulkerRecipe(
				[
					new ExactRecipeIngredient(VanillaBlocks::SHULKER_BOX()->asItem()),
					new ExactRecipeIngredient(VanillaItems::DYE()->setColor($color))
				],
				[VanillaBlocks::DYED_SHULKER_BOX()->setColor($color)->asItem()],
				$color
			));
			foreach (DyeColor::getAll() as $dyedShulkerColor) {
				self::$manager->registerShapelessRecipe(new ColoredShulkerRecipe(
					[
						new ExactRecipeIngredient(VanillaBlocks::DYED_SHULKER_BOX()->setColor($dyedShulkerColor)->asItem()),
						new ExactRecipeIngredient(VanillaItems::DYE()->setColor($color))
					],
					[VanillaBlocks::DYED_SHULKER_BOX()->setColor($color)->asItem()],
					$color
				));
			}
		}

		switch ($serverType) {
			case "prison":

				self::unregisterRecipesFor(VanillaItems::NETHERITE_INGOT());
				self::$manager->registerShapedRecipe(new ShapedRecipe(
					[
						"AA"
					],
					[
						"A" => new ExactRecipeIngredient(VanillaItems::NETHERITE_SCRAP())
					],
					[VanillaItems::NETHERITE_INGOT()]
				));

				self::$manager->registerShapedRecipe(new ShapedRecipe(
					[
						"AAA",
						"A A"
					],
					[
						"A" => new ExactRecipeIngredient(VanillaItems::NETHERITE_INGOT())
					],
					[VanillaItems::NETHERITE_HELMET()]
				));
				self::$manager->registerShapedRecipe(new ShapedRecipe(
					[
						"A A",
						"A A"
					],
					[
						"A" => new ExactRecipeIngredient(VanillaItems::NETHERITE_INGOT())
					],
					[VanillaItems::NETHERITE_BOOTS()]
				));
				self::$manager->registerShapedRecipe(new ShapedRecipe(
					[
						"A A",
						"AAA",
						"AAA"
					],
					[
						"A" => new ExactRecipeIngredient(VanillaItems::NETHERITE_INGOT())
					],
					[VanillaItems::NETHERITE_CHESTPLATE()]
				));
				self::$manager->registerShapedRecipe(new ShapedRecipe(
					[
						"AAA",
						"A A",
						"A A"
					],
					[
						"A" => new ExactRecipeIngredient(VanillaItems::NETHERITE_INGOT())
					],
					[VanillaItems::NETHERITE_LEGGINGS()]
				));

				self::unregisterFurnaceRecipesFor(VanillaBlocks::ANCIENT_DEBRIS()->asItem());
				self::registerBlastFurnaceRecipe(new AncientDebrisRecipe);

				break;
		}
	}


	private static function registerBlastFurnaceRecipe(FurnaceRecipe $recipe): void {
		self::$manager->getFurnaceRecipeManager(FurnaceType::FURNACE)->register($recipe);
		self::$manager->getFurnaceRecipeManager(FurnaceType::BLAST_FURNACE)->register($recipe);
	}

	private static function registerSmokerRecipe(FurnaceRecipe $recipe): void {
		self::$manager->getFurnaceRecipeManager(FurnaceType::FURNACE)->register($recipe);
		self::$manager->getFurnaceRecipeManager(FurnaceType::SMOKER)->register($recipe);
	}

	private static function unregisterRecipesFor(Item $resultItem): void {
		$r = new ReflectionClass(self::$manager);
		($shapeless = $r->getProperty('shapelessRecipes'))->setAccessible(true);
		($shaped = $r->getProperty('shapedRecipes'))->setAccessible(true);

		/** @var ShapelessRecipe[][] */
		$csl = $shapeless->getValue(self::$manager);
		/** @var ShapedRecipe[][] */
		$csp = $shaped->getValue(self::$manager);

		$ncsl = [];
		foreach ($csl as $csl2 => $csl2a) {
			if (!isset($ncsl[$csl2])) $ncsl[$csl2] = [];
			foreach ($csl2a as $recipe) if (!in_array($resultItem, $recipe->getResults())) $ncsl[$csl2][] = $recipe;
		}

		$ncsp = [];
		foreach ($csp as $csp2 => $csp2a) {
			if (!isset($ncsp[$csp2])) $ncsp[$csp2] = [];
			foreach ($csp2a as $recipe) if (!in_array($resultItem, $recipe->getResults())) $ncsp[$csp2][] = $recipe;
		}

		$shapeless->setValue(self::$manager, $ncsl);
		$shaped->setValue(self::$manager, $ncsp);
	}

	private static function unregisterFurnaceRecipesFor(Item $inputItem): void {
		$fms = [self::$manager->getFurnaceRecipeManager(FurnaceType::FURNACE), self::$manager->getFurnaceRecipeManager(FurnaceType::BLAST_FURNACE), self::$manager->getFurnaceRecipeManager(FurnaceType::SMOKER)];

		foreach ($fms as $fm) {
			$r = new ReflectionClass($fm);
			($prop = $r->getProperty('furnaceRecipes'))->setAccessible(true);
			/** @var FurnaceRecipe[] */
			$current = $prop->getValue($fm);
			$new = [];
			foreach ($current as $recipe) {
				if (!$recipe->getInput()->accepts($inputItem)) $new[] = $recipe;
			}
			$prop->setValue($fm, $new);
		}
	}
}
