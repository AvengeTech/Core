<?php

namespace core\lootboxes\ui;

use core\AtPlayer as Player;
use core\lootboxes\entity\LootBox;
use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;

class LootBoxUi extends SimpleForm {

	public function __construct(Player $player, public LootBox $lootBox, string $message = "", bool $error = true) {
		parent::__construct(
			"Loot Box",
			($message !== "" ? ($error ? TextFormat::RED : TextFormat::GREEN) . $message . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") .
				"You have " . TextFormat::YELLOW . number_format($player->getSession()->getLootBoxes()->getLootBoxes()) . TextFormat::WHITE . " loot boxes available" . PHP_EOL . PHP_EOL .
				"What would you like to do?"
		);

		$this->addButton(new Button("Open loot box"));
		$this->addButton(new Button(TextFormat::ICON_ENDERMITE . " Open multiple"));
		$this->addButton(new Button("Gift loot boxes"));
		$this->addButton(new Button("Craft loot boxes"));
	}

	public function handle($response, Player $player) {
		if ($response == 0) {
			if ($player->getSession()->getLootBoxes()->getLootBoxes() <= 0) {
				$player->showModal(new LootBoxUi($player, $this->lootBox, "You don't have any loot boxes to open! Get more by keeping a vote streak, collecting shards, or purchase them at " . TextFormat::YELLOW . "store.avengetech.net"));
				return;
			}
			if ($this->lootBox->isOccupied()) {
				$player->showModal(new LootBoxUi($player, $this->lootBox, "This loot box is currently being used!"));
				return;
			}
			$this->lootBox->open($player);
			return;
		}
		if ($response == 1) {
			if (!$player->hasRank()) {
				$player->showModal(new LootBoxUi($player, $this->lootBox, "You must have a rank to open multiple loot boxes at once! Purchase one at " . TextFormat::YELLOW . "store.avengetech.net"));
				return;
			}
			if ($player->getSession()->getLootBoxes()->getLootBoxes() <= 0) {
				$player->showModal(new LootBoxUi($player, $this->lootBox, "You don't have any loot boxes to open!" . PHP_EOL . PHP_EOL . "Get more by keeping a vote streak, collecting shards, or purchase them at " . TextFormat::YELLOW . "store.avengetech.net"));
				return;
			}
			$player->showModal(new OpenMultipleUi($player, $this->lootBox));
			//multiple loot boxes ui
			return;
		}
		if ($response == 2) {
			$player->showModal(new GiftLootBoxesUi($player, $this->lootBox));
			return;
		}
		if ($response == 3) {
			$player->showModal(new ShardShaperUi($player, $this->lootBox));
			return;
		}
	}
}
