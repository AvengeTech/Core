<?php

namespace core\chat\emoji\ui;

use core\AtPlayer as Player;
use core\chat\emoji\Emoji;
use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;

class EmojiInfoUi extends SimpleForm {

	public function __construct(
		public Emoji $emoji,
		public int $page,
		public int $pagesize
	) {
		parent::__construct(
			$emoji->getIcon() . " " . $emoji->getName() . " " . $emoji->getIcon(),
			TextFormat::GRAY . "Emoji name: " . $emoji->getName() . PHP_EOL .
				"Icon: " . $emoji->getIcon() . PHP_EOL .
				"Text code" . (count($emoji->getAlias()) > 0 ? "s" : "") . ": " . TextFormat::AQUA . $emoji->getTextCode() . (count($emoji->getAlias()) > 0 ? TextFormat::GRAY . ", " . TextFormat::AQUA . implode(TextFormat::GRAY . ", " . TextFormat::AQUA, $emoji->getAlias()) : "") . PHP_EOL . PHP_EOL .
				TextFormat::GRAY . "Type this emoji's text code in chat to use it!"
		);
		$this->addButton(new Button("Go back"));
	}

	public function handle($response, Player $player) {
		$player->showModal(new EmojiCategoryUi($this->emoji->getType(), $this->page, $this->pagesize));
	}
}
