<?php

namespace core\vote\uis;

use pocketmine\utils\TextFormat;

use core\Core;
use core\AtPlayer as Player;
use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;

class VoteErrorUi extends SimpleForm {

	public $error = 0;

	public function __construct(int $error = 0) {
		$this->error = $error;
		parent::__construct(
			"You haven't voted!",
			($error == 0 ?
				"Whoops... It looks like you haven't voted yet! Visit the link below and vote with your username, then return to this page to claim your free vote rewards!" . PHP_EOL . PHP_EOL .
				TextFormat::YELLOW . "avengetech.net/vote"
				:
				"It looks like you've already voted today! Make sure you visit this page again tomorrow to keep your vote streak going!"
			)
		);

		$this->addButton(new Button("Go back"));
	}

	public function handle($response, Player $player) {
		if ($this->error !== 0) Core::getInstance()->getVote()->setVotedToday($player);
		$player->showModal(new VoteRewardsUi($player));
	}
}
