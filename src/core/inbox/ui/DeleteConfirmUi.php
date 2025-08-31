<?php

namespace core\inbox\ui;

use core\ui\windows\ModalWindow;

use core\AtPlayer as Player;
use core\inbox\Sort;
use core\inbox\object\MessageInstance;

class DeleteConfirmUi extends ModalWindow {

	public function __construct(Player $player, public Sort $sort, public MessageInstance $message) {
		parent::__construct("Delete message?", "Are you sure you want to delete this message?", "Delete", "Go back");
	}

	public function handle($response, Player $player) {
		if($response){
			$source = $this->message->getSource();
			$source->getInbox()->deleteMessage($source->getId(), true);
			$player->showModal(new InboxUi($player, $this->sort, "Message has been deleted!"));
		}else{
			$player->showModal(new ViewMessageUi($this->message, $this->sort));
		}
	}
}
