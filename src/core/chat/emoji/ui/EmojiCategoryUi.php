<?php

namespace core\chat\emoji\ui;


use core\{
	Core,
	AtPlayer as Player
};
use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;

class EmojiCategoryUi extends SimpleForm {

	public array $emojis = [];

	public bool $hasPrev = false;
	public bool $hasNext = false;

	public function __construct(
		public int $type,
		public int $page = 1,
		public int $pagesize = 8
	) {
		$category = Core::getInstance()->getChat()->getEmojiLibrary()->getCategory($type);
		$emojis = $category->getEmojis();
		$chunked = array_chunk($emojis, $pagesize);

		parent::__construct(
			$category->getName() . " Emojis (" . $page . "/" . count($chunked) . ")",
			TextFormat::GRAY . "To send an emoji, type it's " . TextFormat::AQUA . ":text code:" . TextFormat::GRAY . " in chat! They can also be used in private messages, item messages, etc..." . PHP_EOL . PHP_EOL .
				"Tap any emoji below for more information!"
		);

		if ($page < 0) $page = 1;
		$poe = $chunked[$page - 1] ?? [];
		$this->hasPrev = $page > 1;
		$this->hasNext = count($poe) == $pagesize && count($emojis) > $page * $pagesize;

		foreach ($poe as $emoji) {
			$this->addButton(new Button($emoji->getName() . PHP_EOL . $emoji->getIcon() . " " . $emoji->getTextCode()));
			$this->emojis[] = $emoji;
		}
		if ($this->hasPrev) $this->addButton(new Button("Previous page (" . ($page - 1) . "/" . count($chunked) . ")"));
		if ($this->hasNext) $this->addButton(new Button("Next page (" . ($page + 1) . "/" . count($chunked) . ")"));
		$this->addButton(new Button("Go back"));
	}

	public function handle($response, Player $player) {
		$emoji = $this->emojis[$response] ?? null;
		if ($emoji !== null) {
			$player->showModal(new EmojiInfoUi($emoji, $this->page, $this->pagesize));
			return;
		}
		if ($response == count($this->emojis)) {
			if ($this->hasPrev) {
				$player->showModal(new EmojiCategoryUi($this->type, $this->page - 1, $this->pagesize));
			} elseif ($this->hasNext) {
				$player->showModal(new EmojiCategoryUi($this->type, $this->page + 1, $this->pagesize));
			} else {
				$player->showModal(new EmojisUi());
			}
		} elseif ($response == count($this->emojis) + 1) {
			if ($this->hasPrev && $this->hasNext) {
				$player->showModal(new EmojiCategoryUi($this->type, $this->page + 1, $this->pagesize));
			} else {
				$player->showModal(new EmojisUi());
			}
		} else {
			$player->showModal(new EmojisUi());
		}
	}
}
