<?php

namespace core\rank\uis;

use core\AtPlayer as Player;
use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;

class RanksUi extends SimpleForm {

	public function __construct() {
		parent::__construct(
			"Ranks",
			"Here is a list of ranks you can purchase from our store! Each rank has it's own benefits ingame. Purchase them at " . TextFormat::YELLOW . "store.avengetech.net" . TextFormat::WHITE . PHP_EOL . PHP_EOL .
				"Select a rank below to find out details about it!"
		);

		$this->addButton(new Button(TextFormat::ICON_ENDERMITE . " Endermite"));
		$this->addButton(new Button(TextFormat::ICON_BLAZE . " Blaze"));
		$this->addButton(new Button(TextFormat::ICON_GHAST . " Ghast"));
		$this->addButton(new Button(TextFormat::ICON_ENDERMAN . " Enderman"));
		$this->addButton(new Button(TextFormat::ICON_WITHER . " Wither"));
		$this->addButton(new Button(TextFormat::ICON_ENDERDRAGON . " Enderdragon"));
		$this->addButton(new Button(TextFormat::ICON_WARDEN . " Warden" . PHP_EOL . "(Subscription)"));
	}

	public function handle($response, Player $player) {
	}
}
