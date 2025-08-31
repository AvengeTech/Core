<?php

namespace core\crafting;

use core\block\ShulkerBox;
use core\Core;
use core\utils\BlockRegistry;
use core\utils\ItemRegistry;
use pocketmine\block\ShulkerBox as PMShulkerBox;
use pocketmine\crafting\ShapelessRecipe;
use pocketmine\block\utils\ColoredTrait;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\crafting\CraftingGrid;
use pocketmine\crafting\ShapelessRecipeType;
use pocketmine\item\Dye;
use pocketmine\item\ItemBlock;
use pocketmine\item\Item;

class ColoredShulkerRecipe extends ShapelessRecipe {
	use ColoredTrait;

	/**
	 * @param RecipeIngredient[] $ingredients No more than 9 total. This applies to sum of item stack counts, not count of array.
	 * @param Item[]             $results     List of result items created by this recipe.
	 */
	public function __construct(array $ingredients, array $results, DyeColor $color) {
		parent::__construct($ingredients, $results, ShapelessRecipeType::CRAFTING());
		$this->setColor($color);
	}

	/**
	 * Workaround for Shulkers losing data when dyed
	 * @return Item[]
	 */
	public function getResultsFor(CraftingGrid $grid): array {
		$shulker = null;
		foreach ($grid->getContents() as $input) {
			if ($input instanceof ItemBlock && $input->getBlock() instanceof PMShulkerBox) $shulker = $input;
		}
		if (is_null($shulker)) return [];
		$item = VanillaBlocks::DYED_SHULKER_BOX()->setColor($this->getColor())->asItem();
		$oldNBT = $shulker->getNamedTag();
		$item->setNamedTag($oldNBT);
		return [$item];
	}
}
