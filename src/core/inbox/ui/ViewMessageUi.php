<?php

namespace core\inbox\ui;

use core\AtPlayer as Player;
use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;
use core\inbox\inventory\MessageInventory;
use core\inbox\object\MessageInstance;
use core\inbox\Sort;
use core\utils\TextFormat;

class ViewMessageUi extends SimpleForm {

	public $message;

	public function __construct(MessageInstance $message, public ?Sort $sort = null) {
		$this->message = $message;
		$message->getSource()->setOpened();

		parent::__construct(
			"Sender: " . $message->getSender()->getGamertag(),
			"Subject: " . $message->getSubject() . PHP_EOL .

				"Body: " . $message->getBody() . TextFormat::RESET . TextFormat::WHITE . PHP_EOL . PHP_EOL .
				(($count = count($message->getItems())) > 0 ? "Attachment: " . $count . " item" . ($count > 1 ? "s" : "") . PHP_EOL . PHP_EOL : "") .
				"Select an option below!"
		);

		$this->addButton(new Button("Go back"));
		$this->addButton(new Button("Mark as unread"));
		$this->addButton(new Button("Delete"));
		if ($count > 0) {
			$this->addButton(new Button("View attachment"));
		}
	}

	public function handle($response, Player $player) {
		if ($response == 0) {
			$player->showModal(new InboxUi($player, $this->sort));
			return;
		}
		$source = $this->message->getSource();
		if ($source == null) {
			$player->sendMessage(TextFormat::RI . "This message no longer exists.");
			return;
		}
		if ($response == 1) {
			$source->setOpened(false);
			$player->showModal(new InboxUi($player, $this->sort));
			return;
		}
		if ($response == 2) {
			$player->showModal(new DeleteConfirmUi($player, $this->sort, $this->message));
			//$source->getInbox()->deleteMessage($source->getId(), true);
			//$player->showModal(new InboxUi($player, $this->sort));
			return;
		}
		if ($response == 3) {
			if (count($source->getItems()) == 0) {
				$player->sendMessage(TextFormat::RI . "This message has no attachments.");
				return;
			}
			$inv = new MessageInventory($source);
			$inv->doOpen($player);
		}
	}
}
