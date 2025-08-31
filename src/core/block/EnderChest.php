<?php

namespace core\block;

use core\block\inventory\EnderChestInventory;
use core\block\tile\EnderChest as TileEnderChest;
use pocketmine\block\Transparent;
use pocketmine\block\utils\FacesOppositePlacingPlayerTrait;
use pocketmine\block\utils\SupportType;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class EnderChest extends Transparent {
	use FacesOppositePlacingPlayerTrait;

	public function getLightLevel(): int {
		return 7;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes(): array {
		//these are slightly bigger than in PC
		return [AxisAlignedBB::one()->contract(0.025, 0, 0.025)->trim(Facing::UP, 0.05)];
	}

	public function getSupportType(int $facing): SupportType {
		return match ($facing) {
			Facing::UP => SupportType::NONE,
			Facing::DOWN => SupportType::NONE,
			default => SupportType::FULL
		};
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []): bool {
		if ($player instanceof Player) {
			$enderChest = $this->position->getWorld()->getTile($this->position);
			if ($enderChest instanceof TileEnderChest && $this->getSide(Facing::UP)->isTransparent()) {
				$enderChest->setViewerCount($enderChest->getViewerCount() + 1);
				$player->setCurrentWindow(new EnderChestInventory($this->position, $player->getEnderInventory()));
			}
		}

		return true;
	}

	public function getDropsForCompatibleTool(Item $item): array {
		return [
			VanillaBlocks::OBSIDIAN()->asItem()->setCount(8)
		];
	}

	public function isAffectedBySilkTouch(): bool {
		return true;
	}
}
