<?php

namespace core\cosmetics\ui;

use core\AtPlayer as Player;
use core\cosmetics\CosmeticData;
use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;

class EffectCosmeticsUi extends SimpleForm {

	public function __construct(Player $player, string $message = "", bool $error = true) {
		parent::__construct(
			"Effects",
			($message != "" ? ($error ? TextFormat::RED : TextFormat::GREEN) . $message . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") .
				"Select which type of effect you'd like to view below!"
		);

		$cs = $player->getSession()->getCosmetics();

		$this->addButton(new Button("Trail effects" . PHP_EOL . "Equipped: " . ($cs->hasEquippedTrail() ? $cs->getEquippedTrail()->getName() : "None")));
		$this->addButton(new Button("Idle effects" . PHP_EOL . "Equipped: " . ($cs->hasEquippedIdle() ? $cs->getEquippedIdle()->getName() : "None")));
		$this->addButton(new Button("Double jump effects" . PHP_EOL . "Equipped: " . ($cs->hasEquippedDoubleJump() ? $cs->getEquippedDoubleJump()->getName() : "None")));
		$this->addButton(new Button("Arrow effects" . PHP_EOL . "Equipped: " . ($cs->hasEquippedArrow() ? $cs->getEquippedArrow()->getName() : "None")));
		$this->addButton(new Button("Snowball effects" . PHP_EOL . "Equipped: " . ($cs->hasEquippedSnowball() ? $cs->getEquippedSnowball()->getName() : "None")));

		$this->addButton(new Button("Go back"));
	}

	public function handle($response, Player $player) {
		if ($response == 0) {
			$player->showModal(new CosmeticListUi($player, CosmeticData::TYPE_TRAIL_EFFECT));
			return;
		}
		if ($response == 1) {
			$player->showModal(new CosmeticListUi($player, CosmeticData::TYPE_IDLE_EFFECT));
			return;
		}
		if ($response == 2) {
			$player->showModal(new CosmeticListUi($player, CosmeticData::TYPE_DOUBLE_JUMP_EFFECT));
			return;
		}
		if ($response == 3) {
			$player->showModal(new CosmeticListUi($player, CosmeticData::TYPE_ARROW_EFFECT));
			return;
		}
		if ($response == 4) {
			$player->showModal(new CosmeticListUi($player, CosmeticData::TYPE_SNOWBALL_EFFECT));
			return;
		}
		$player->showModal(new CosmeticsUi($player));
	}
}
