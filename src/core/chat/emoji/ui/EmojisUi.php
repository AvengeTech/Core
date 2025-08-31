<?php

namespace core\chat\emoji\ui;

use core\{
	Core,
	AtPlayer as Player
};
use core\network\Links;
use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;

class EmojisUi extends SimpleForm {

	public function __construct() {
		parent::__construct(
			"Emoji List",
			TextFormat::GRAY . "Purchase any rank at " . TextFormat::YELLOW . Links::SHOP . TextFormat::GRAY . " to access all of our emojis in chat!" . PHP_EOL . PHP_EOL .
				"Tap any category below to see it's available emojis"
		);
		foreach (Core::getInstance()->getChat()->getEmojiLibrary()->getCategories() as $category) {
			$this->addButton(new Button($category->getName()));
		}
	}

	public function handle($response, Player $player) {
		$player->showModal(new EmojiCategoryUi($response));
	}
}
