<?php

namespace core\staff\uis\actions\mute;

use core\AtPlayer;
use core\Core;
use core\session\CoreSession;
use core\staff\entry\MuteManager;
use core\ui\elements\customForm\Dropdown;
use core\ui\elements\customForm\Input;
use core\ui\elements\customForm\Label;
use core\ui\elements\customForm\Toggle;
use core\ui\windows\CustomForm;
use core\user\User;
use core\utils\TextFormat;

class AddMuteUi extends CustomForm {

	public int $offset = 0;

	public function __construct(string $message = "", string $username = "") {
		parent::__construct("Mute Player");

		if ($message != "") {
			$this->addElement(new Label(TextFormat::RED . $message . TextFormat::RESET . PHP_EOL));
			$this->offset = 1;
		}

		$this->addElement(new Input("Username", "sn3akrr", $username));
		$this->addElement(new Dropdown("Reason", array_keys(MuteManager::STIMEMAP)));
		$this->addElement(new Dropdown("Duration", ["Automatic", "3 Days", "7 Days", "31 Days", "Permanent"]));
		$this->addElement(new Toggle("Silent", true));
	}

	public function handle($response, AtPlayer $player) {
		$username = $response[0 + $this->offset];
		$reason = array_keys(MuteManager::STIMEMAP)[$response[1 + $this->offset]];
		$dkey = $response[2 + $this->offset];
		$silent = $response[3 + $this->offset] ?? true;

		if ($username == "") {
			$player->showModal(new AddMuteUi("Must provide username!", $username));
			return;
		}

		Core::getInstance()->getUserPool()->useUser($username, function (User $user) use ($player, $username, $dkey, $reason, $silent) {
			if (!$user->valid()) {
				$player->showModal(new AddMuteUi("Invalid user!", $username));
				return;
			}

			Core::getInstance()->getSessionManager()->useSession($user, function (CoreSession $session) use ($player, $user, $dkey, $reason, $silent) {
				$muteManager = $session->getStaff()->getMuteManager();
				if ($muteManager->isMuted()) {
					$player->showModal(new AddMuteUi("User is already muted!", $user->getGamertag()));
					return;
				}

				switch ($dkey) {
					case 0: // Automatic (Normal)
						$duration = $muteManager->getNextDuration($reason);
						break;
					case 1: // 3 Days
						$duration = 3 * 24 * 60 * 60; // 3 days in seconds
						break;
					case 2: // 7 Days
						$duration = 7 * 24 * 60 * 60; // 7 days in seconds
						break;
					case 3: // 31 Days
						$duration = 31 * 24 * 60 * 60; // 31 days in seconds
						break;
					case 4: // Permanent
						$duration = -1; // Permanent mute
						break;
					default:
						$player->showModal(new AddMuteUi("Invalid duration selected!", $user->getGamertag()));
						return;
				}

				Core::getInstance()->getStaff()->mute($user, $player, $reason, $duration);
				if (!$silent) {
					Core::announceToSS(TextFormat::RI . TextFormat::YELLOW . $user->getGamertag() . TextFormat::RED . " has been " . TextFormat::BOLD . TextFormat::DARK_RED . "MUTED " . TextFormat::RESET . TextFormat::RED . "for: " . $reason, "random.anvil_land");
				}
			});
		});
	}
}
