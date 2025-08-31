<?php

namespace core\staff\uis\actions\ban;

use core\AtPlayer;
use core\Core;
use core\session\CoreSession;
use core\staff\entry\BanManager;
use core\ui\elements\customForm\Dropdown;
use core\ui\elements\customForm\Input;
use core\ui\elements\customForm\Label;
use core\ui\elements\customForm\Toggle;
use core\ui\windows\CustomForm;
use core\user\User;
use core\utils\TextFormat;

class AddPlayerBanUi extends CustomForm {

	public int $offset = 0;

	public function __construct(string $message = "", string $username = "") {
		parent::__construct("Ban Player");

		if ($message != "") {
			$this->addElement(new Label(TextFormat::RED . $message . TextFormat::RESET . PHP_EOL));
			$this->offset = 1;
		}

		$this->addElement(new Input("Username", "sn3akrr", $username));
		$this->addElement(new Dropdown("Reason", array_keys(BanManager::STIMEMAP)));
		$this->addElement(new Dropdown("Duration", ["Automatic", "7 Days", "31 Days", "Permanent"]));
		$this->addElement(new Toggle("Silent", true));
	}

	public function handle($response, AtPlayer $player) {
		$username = $response[0 + $this->offset];
		$reason = array_keys(BanManager::STIMEMAP)[$response[1 + $this->offset]];
		$dkey = $response[2 + $this->offset];
		$silent = $response[3 + $this->offset] ?? true;

		if ($username == "") {
			$player->showModal(new AddPlayerBanUi("Must provide username!", $username));
			return;
		}

		Core::getInstance()->getUserPool()->useUser($username, function (User $user) use ($player, $username, $dkey, $reason, $silent) {
			if (!$user->valid()) {
				$player->showModal(new AddPlayerBanUi("Invalid user!", $username));
				return;
			}

			Core::getInstance()->getSessionManager()->useSession($user, function (CoreSession $session) use ($player, $user, $dkey, $reason, $silent) {
				$banManager = $session->getStaff()->getBanManager();
				if ($banManager->isBanned()) {
					$player->showModal(new AddPlayerBanUi("User is already muted!", $user->getGamertag()));
					return;
				}

				switch ($dkey) {
					case 0: // Automatic
						$duration = $banManager->getNextDuration($reason);
						break;
					case 1: // 7 Days
						$duration = 7 * 24 * 60 * 60;
						break;
					case 2: // 31 Days
						$duration = 31 * 24 * 60 * 60;
						break;
					case 3:
						$duration = -1;
						break;
					default:
						$player->showModal(new AddPlayerBanUi("Invalid duration selected!", $user->getGamertag()));
						return;
				}

				Core::getInstance()->getStaff()->ban($user, $player, $reason, $duration);
				if (!$silent) {
					Core::announceToSS(TextFormat::RI . TextFormat::YELLOW . $user->getGamertag() . TextFormat::RED . " has been " . TextFormat::BOLD . TextFormat::DARK_RED . "BANNED " . TextFormat::RESET . TextFormat::RED . "for: " . $reason, "random.anvil_land");
				}
			});
		});
	}
}
