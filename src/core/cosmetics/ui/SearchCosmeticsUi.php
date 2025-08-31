<?php

namespace core\cosmetics\ui;

use core\AtPlayer as Player;
use core\cosmetics\CosmeticData;
use core\ui\elements\customForm\{
	Label,
	Input
};
use core\ui\windows\CustomForm;
use core\utils\TextFormat;

class SearchCosmeticsUi extends CustomForm {

	public array $cosmetics = [];

	public function __construct(Player $player, public int $listType, public bool $fromMenu, string $message = "", bool $error = true) {
		$name = match ($listType) {
			CosmeticData::TYPE_CAPE => "Cape",
			CosmeticData::TYPE_TRAIL_EFFECT => "Trail Effect",
			CosmeticData::TYPE_IDLE_EFFECT => "Idle Effect",
			CosmeticData::TYPE_DOUBLE_JUMP_EFFECT => "Double Jump Effect",
			CosmeticData::TYPE_ARROW_EFFECT => "Arrow Effect",
			CosmeticData::TYPE_SNOWBALL_EFFECT => "Snowball Effect",
			CosmeticData::TYPE_HAT => "Hat",
			CosmeticData::TYPE_BACK => "Back",
			CosmeticData::TYPE_SHOES => "Shoes",
			CosmeticData::TYPE_SUIT => "Suit",
			CosmeticData::TYPE_MORPH => "Morph",
			CosmeticData::TYPE_PET => "Pet",
		};
		parent::__construct("Search " . $name . "s");

		$this->cosmetics = $player->getSession()->getCosmetics()->getAvailableCosmetics($listType);

		$this->addElement(new Label(
			($message != "" ? ($error ? TextFormat::RED : TextFormat::GREEN) . $message . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") .
				"Type a keyword to search for matching " . $name . "s!"
		));
		$this->addElement(new Input("Search", $name . " name"));
	}

	public function handle($response, Player $player) {
		$name = match ($this->listType) {
			CosmeticData::TYPE_CAPE => "Cape",
			CosmeticData::TYPE_TRAIL_EFFECT => "Trail Effect",
			CosmeticData::TYPE_IDLE_EFFECT => "Idle Effect",
			CosmeticData::TYPE_DOUBLE_JUMP_EFFECT => "Double Jump Effect",
			CosmeticData::TYPE_ARROW_EFFECT => "Arrow Effect",
			CosmeticData::TYPE_SNOWBALL_EFFECT => "Snowball Effect",
			CosmeticData::TYPE_HAT => "Hat",
			CosmeticData::TYPE_BACK => "Back",
			CosmeticData::TYPE_SHOES => "Shoes",
			CosmeticData::TYPE_SUIT => "Suit",
			CosmeticData::TYPE_MORPH => "Morph",
			CosmeticData::TYPE_PET => "Pet",
		};

		$search = $response[1];
	}
}
