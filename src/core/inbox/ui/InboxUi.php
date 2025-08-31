<?php

namespace core\inbox\ui;

use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;

use core\AtPlayer as Player;
use core\inbox\Sort;
use core\utils\TextFormat;

class InboxUi extends SimpleForm {

	public $sort;
	public $messages = [];

	public function __construct(Player $player, ?Sort $sort = null, string $message = "") {
		$sort = $this->sort = ($sort == null ? new Sort() : $sort);
		parent::__construct("Inbox", ($message != "" ? TextFormat::GREEN . $message . TextFormat::RESET . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") . $sort->getSortMessage() . "\n" . "Tap a message to open it!");
		$this->addButton(new Button("Sort..."));
		$this->addButton(new Button(TextFormat::RED . "Delete all read"));
		$messages = $sort->getMessages($player);
		foreach ($messages as $message) {
			$this->messages[] = $message;
			$this->addButton(new Button(
				($message->hasOpened() ? TextFormat::EMOJI_MAIL_OPEN : TextFormat::EMOJI_MAIL_CLOSED) . " " .
					"Subject: " . $message->getSubject() . "\n" .
					"Received: " . $message->getFormattedTime()
			));
		}
	}

	public function handle($response, Player $player) {
		if ($response == 0) {
			$player->showModal(new SortInboxUi($player, $this->sort));
			return;
		}
		if ($response == 1) {
			$player->showModal(new DeleteReadConfirmUi($player, $this->sort, $this->messages));
			return;
		}
		$src = null;
		$message = $this->messages[$response - 2] ?? null;
		if ($message == null || !$message->verify($src)) {
			$player->sendMessage(TextFormat::RI . "Unknown message selected.");
			return;
		}
		$player->showModal(new ViewMessageUi($message, $this->sort));
	}
}
