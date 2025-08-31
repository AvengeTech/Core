<?php

namespace core\cosmetics\ui;

use core\AtPlayer as Player;
use core\cosmetics\CosmeticData;
use core\gadgets\ui\GadgetUi;
use core\rank\uis\ChatEffectsUi;
use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;

class CosmeticsUi extends SimpleForm {

	public function __construct(Player $player, string $message = "", bool $error = true) {
		parent::__construct(
			"Cosmetics",
			($message != "" ? ($error ? TextFormat::RED : TextFormat::GREEN) . $message . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") .
				"Select which cosmetic you'd like to view below!"
		);

		$gs = $player->getSession()->getGadgets();
		$cs = $player->getSession()->getCosmetics();

		$this->addButton(new Button(TextFormat::ICON_WARDEN . " Chat effects"));
		$this->addButton(new Button("Lobby gadgets" . PHP_EOL . "Equipped: " . ($gs->hasDefaultGadget() ? $gs->getDefaultGadget(true)->getName() : "None")));
		$this->addButton(new Button("Capes" . PHP_EOL . "Equipped: " . ($cs->hasEquippedCape() ? $cs->getEquippedCape()->getName() : "None")));
		$this->addButton(new Button("Effects"));
		$this->addButton(new Button("Clothing"));
	}

	public function handle($response, Player $player) {
		if ($response == 0) {
			if (!$player->getSession()->getRank()->hasSub()) {
				$player->showModal(new CosmeticsUi($player, "You must have the Warden subscription to enable chat effects. Purchase one at " . TextFormat::YELLOW . "store.avengetech.net"));
				return;
			}
			$player->showModal(new ChatEffectsUi($player, "", true));
			return;
		}
		if ($response == 1) {
			$player->showModal(new GadgetUi($player));
			return;
		}
		if ($response == 2) {
			$player->showModal(new CosmeticListUi($player, CosmeticData::TYPE_CAPE));
			return;
		}
		if ($response == 3) {
			$player->showModal(new EffectCosmeticsUi($player));
			return;
		}
		if ($response == 4) {
			$player->showModal(new ClothingCosmeticsUi($player));
			return;
		}
	}
}
