<?php

namespace core\staff\uis\actions\mute;

use core\AtPlayer;
use core\Core;
use core\session\CoreSession;
use core\ui\elements\customForm\Input;
use core\ui\elements\customForm\Label;
use core\ui\windows\CustomForm;
use core\user\User;
use core\utils\TextFormat;

class ViewMutesUi extends CustomForm {

	public int $offset = 0;

	public function __construct(string $message = "", string $username = "") {
		parent::__construct("View Mutes");

		if ($message != "") {
			$this->addElement(new Label(TextFormat::RED . $message . TextFormat::RESET . PHP_EOL));
			$this->offset = 1;
		}

		$this->addElement(new Input("Username", "sn3akrr", $username));
	}

	public function handle($response, AtPlayer $player) {
		$username = $response[0 + $this->offset];
		if ($username == "") {
			$player->showModal(new ViewMutesUi("Must provide username!"));
			return;
		}

		Core::getInstance()->getUserPool()->useUser($username, function (User $user) use ($player, $username) {
			if (!$player->isConnected()) return;
			if (!$user->valid()) {
				$player->showModal(new ViewMutesUi("Invalid user!", $username));
				return;
			}

			Core::getInstance()->getSessionManager()->useSession($user, function (CoreSession $session) use ($player, $user) {
				if (!$player->isConnected()) return;
				$muteManager = $session->getStaff()->getMuteManager();
				$mutes = $muteManager->getMutes();

				if (empty($mutes)) {
					$player->showModal(new ViewMutesUi("No mutes found for user: " . $user->getGamertag()));
					return;
				}

				$player->showModal(new MutesListUi($player, $mutes, $user));
			});
		});
	}
}
