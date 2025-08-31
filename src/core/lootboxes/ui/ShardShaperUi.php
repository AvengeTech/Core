<?php

namespace core\lootboxes\ui;

use core\AtPlayer as Player;
use core\lootboxes\entity\LootBox;
use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;

class ShardShaperUi extends SimpleForm {

	public function __construct(Player $player, public LootBox $lootBox, string $message = "", bool $error = true) {
		parent::__construct(
			"Craft Loot Boxes",
			($message !== "" ? ($error ? TextFormat::RED : TextFormat::GREEN) . $message . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") .
				"You have " . TextFormat::AQUA . number_format($player->getSession()->getLootBoxes()->getShards()) . TextFormat::WHITE . " shards available" . PHP_EOL . PHP_EOL .
				"What would you like to do?"
		);

		$this->addButton(new Button("Gift shards"));

		$this->addButton(new Button("Craft 1 loot box" . PHP_EOL . TextFormat::AQUA . "1,000 shards"));
		$this->addButton(new Button("Craft 5 loot boxes" . PHP_EOL . TextFormat::AQUA . "4,000 shards"));
		$this->addButton(new Button("Craft 10 loot boxes" . PHP_EOL . TextFormat::AQUA . "8,000 shards"));
		$this->addButton(new Button("Craft 25 loot boxes" . PHP_EOL . TextFormat::AQUA . "20,000 shards"));
		$this->addButton(new Button("Craft 50 loot boxes" . PHP_EOL . TextFormat::AQUA . "40,000 shards"));
		$this->addButton(new Button("Craft 100 loot boxes" . PHP_EOL . TextFormat::AQUA . "70,000 shards"));
		$this->addButton(new Button("Go back"));
	}

	public function handle($response, Player $player) {
		if ($response == 0) {
			$player->showModal(new ShardGifterUi($player, $this->lootBox));
			return;
		}
		$shards = ($lbs = $player->getSession()->getLootBoxes())->getShards();
		if ($response == 1) {
			if ($shards < 1000) {
				$player->showModal(new ShardShaperUi($player, $this->lootBox, "You do not have enough shards!"));
				return;
			}
			$lbs->takeShards(1000);
			$lbs->addLootBoxes(1);
			$player->showModal(new ShardShaperUi($player, $this->lootBox, "Successfully crafted 1 loot box!", false));
			return;
		}
		if ($response == 2) {
			if ($shards < 4000) {
				$player->showModal(new ShardShaperUi($player, $this->lootBox, "You do not have enough shards!"));
				return;
			}
			$lbs->takeShards(4000);
			$lbs->addLootBoxes(5);
			$player->showModal(new ShardShaperUi($player, $this->lootBox, "Successfully crafted 5 loot boxes!", false));
			return;
		}
		if ($response == 3) {
			if ($shards < 8000) {
				$player->showModal(new ShardShaperUi($player, $this->lootBox, "You do not have enough shards!"));
				return;
			}
			$lbs->takeShards(8000);
			$lbs->addLootBoxes(10);
			$player->showModal(new ShardShaperUi($player, $this->lootBox, "Successfully crafted 10 loot boxes!", false));
			return;
		}
		if ($response == 4) {
			if ($shards < 20000) {
				$player->showModal(new ShardShaperUi($player, $this->lootBox, "You do not have enough shards!"));
				return;
			}
			$lbs->takeShards(20000);
			$lbs->addLootBoxes(25);
			$player->showModal(new ShardShaperUi($player, $this->lootBox, "Successfully crafted 25 loot boxes!", false));
			return;
		}
		if ($response == 5) {
			if ($shards < 40000) {
				$player->showModal(new ShardShaperUi($player, $this->lootBox, "You do not have enough shards!"));
				return;
			}
			$lbs->takeShards(40000);
			$lbs->addLootBoxes(50);
			$player->showModal(new ShardShaperUi($player, $this->lootBox, "Successfully crafted 50 loot boxes!", false));
			return;
		}
		if ($response == 6) {
			if ($shards < 70000) {
				$player->showModal(new ShardShaperUi($player, $this->lootBox, "You do not have enough shards!"));
				return;
			}
			$lbs->takeShards(70000);
			$lbs->addLootBoxes(100);
			$player->showModal(new ShardShaperUi($player, $this->lootBox, "Successfully crafted 100 loot boxes!", false));
			return;
		}
		$player->showModal(new LootBoxUi($player, $this->lootBox));
	}
}
