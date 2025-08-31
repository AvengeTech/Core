<?php

namespace core\utils\profile;

use core\AtPlayer as Player;
use core\Core;
use core\session\CoreSession;
use core\ui\elements\customForm\{
	Label,
	Input
};
use core\ui\windows\CustomForm;
use core\user\User;
use core\utils\TextFormat;

class ProfileSearchUi extends CustomForm {

	public function __construct(string $error = "") {
		parent::__construct("Profile search");
		$this->addElement(new Label(($error !== "" ? TextFormat::RED . $error . TextFormat::WHITE . PHP_EOL . PHP_EOL : "") . "Enter a player's gamertag to view their profile!"));
		$this->addElement(new Input("Gamertag", "sn3akrr"));
	}

	public function handle($response, Player $player) {
		$gamertag = $response[1];
		Core::getInstance()->getUserPool()->useUser($gamertag, function (User $user) use ($player): void {
			if (!$player->isConnected()) return;
			if (!$user->valid()) {
				$player->showModal(new ProfileSearchUi("Player never seen!"));
				return;
			}
			Core::getInstance()->getSessionManager()->useSession($user, function (CoreSession $session) use ($player): void {
				if (!$player->isConnected()) return;
				$player->showModal(new ProfileUi($session));
			});
		});
	}
}
