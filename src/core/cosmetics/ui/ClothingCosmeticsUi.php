<?php

namespace core\cosmetics\ui;

use core\AtPlayer as Player;
use core\cosmetics\CosmeticData;
use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;

class ClothingCosmeticsUi extends SimpleForm {

	public function __construct(Player $player, string $message = "", bool $error = true) {
		parent::__construct(
			"Clothing",
			($message != "" ? ($error ? TextFormat::RED : TextFormat::GREEN) . $message . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") .
				"Select which type of clothing you'd like to view below!"
		);

		$cs = $player->getSession()->getCosmetics();

		$this->addButton(new Button("Hats" . PHP_EOL . "Equipped: " . ($cs->hasEquippedHat() ? $cs->getEquippedHat()->getName() : "None")));
		$this->addButton(new Button("Backs" . PHP_EOL . "Equipped: " . ($cs->hasEquippedBack() ? $cs->getEquippedBack()->getName() : "None")));
		$this->addButton(new Button("Shoes" . PHP_EOL . "Equipped: " . ($cs->hasEquippedShoes() ? $cs->getEquippedShoes()->getName() : "None")));
		$this->addButton(new Button("Suits" . PHP_EOL . "Equipped: " . ($cs->hasEquippedSuit() ? $cs->getEquippedSuit()->getName() : "None")));

		$this->addButton(new Button("Go back"));
	}

	public function handle($response, Player $player) {
		if ($response == 0) {
			$player->showModal(new CosmeticListUi($player, CosmeticData::TYPE_HAT));
			return;
		}
		if ($response == 1) {
			$player->showModal(new CosmeticListUi($player, CosmeticData::TYPE_BACK));
			return;
		}
		if ($response == 2) {
			$player->showModal(new CosmeticListUi($player, CosmeticData::TYPE_SHOES));
			return;
		}
		if ($response == 3) {
			$player->showModal(new CosmeticListUi($player, CosmeticData::TYPE_SUIT));
			return;
		}
		$player->showModal(new CosmeticsUi($player));
	}
}
