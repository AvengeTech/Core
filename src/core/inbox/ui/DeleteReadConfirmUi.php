<?php

namespace core\inbox\ui;

use core\ui\windows\ModalWindow;

use core\AtPlayer as Player;
use core\inbox\Sort;

class DeleteReadConfirmUi extends ModalWindow {

	public function __construct(Player $player, public Sort $sort, public array $messages = []) {
		parent::__construct(
			"Delete message?", 
			"Are you sure you want to delete all read messages?",
			"Delete",
			"Go back"
		);
	}

	public function handle($response, Player $player) {
		if($response){
			foreach($this->messages as $message){
				if($message->hasOpened()){
					$source = $message->getSource();
					$source->getInbox()->deleteMessage($source->getId(), true);
				}
			}
			$player->showModal(new InboxUi($player, $this->sort, "Deleted all read messages!"));
		}else{
			$player->showModal(new InboxUi($player, $this->sort));
		}
	}
}
