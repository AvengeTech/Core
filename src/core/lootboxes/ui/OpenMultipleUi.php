<?php

namespace core\lootboxes\ui;

use core\{
	Core,
	AtPlayer as Player
};
use core\lootboxes\entity\LootBox;
use core\ui\elements\customForm\{
	Label,
	Input
};
use core\ui\windows\CustomForm;
use core\utils\TextFormat;

class OpenMultipleUi extends CustomForm {

	public function __construct(Player $player, public LootBox $lootBox, string $message = "", bool $error = true) {
		parent::__construct("Open multiple");

		$this->addElement(new Label(
			($message !== "" ? ($error ? TextFormat::RED : TextFormat::GREEN) . $message . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") .
				"How many loot boxes would you like to open? You have " . TextFormat::YELLOW . number_format($player->getSession()->getLootBoxes()->getLootBoxes()) . TextFormat::WHITE . " available"
		));
		$this->addElement(new Input("Amount", 1));
	}

	public function close(Player $player) {
		$player->showModal(new LootBoxUi($player, $this->lootBox));
	}

	public function handle($response, Player $player) {
		$amount = (int) $response[1];

		if ($amount <= 0) {
			$player->showModal(new OpenMultipleUi($player, $this->lootBox, "Enter an amount higher than 0!"));
			return;
		}
		if ($amount > $player->getSession()->getLootBoxes()->getLootBoxes()) {
			$player->showModal(new OpenMultipleUi($player, $this->lootBox, "You don't have enough loot boxes!"));
			return;
		}

		Core::getInstance()->getLootBoxes()->openMultiple($player, $amount);
	}
}
