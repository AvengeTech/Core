<?php

namespace core\staff\uis\actions\warn;

use pocketmine\Server;

use core\AtPlayer as Player;
use core\Core;
use core\session\CoreSession;
use core\staff\entry\WarnManager;
use core\staff\uis\player\ViewWarnUi;
use core\ui\windows\CustomForm;
use core\ui\elements\customForm\{
	Label,
	Input,
};
use core\user\User;
use core\utils\TextFormat;

class ViewWarnsUi extends CustomForm {

	public int $offset = 0;

	public function __construct(string $error = "") {
		parent::__construct("View Warns");
		if ($error != "") {
			$this->addElement(new Label(TextFormat::RED . $error . TextFormat::RESET . PHP_EOL . PHP_EOL));
			$this->offset = 1;
		}
		$this->addElement(new Label("Type the name of the user you are viewing warns for."));
		$this->addElement(new Input("Username", "sn3akrr"));
	}

	public function handle($response, Player $player) {
		$username = $response[1 + $this->offset];
		$pl = Server::getInstance()->getPlayerByPrefix($username);
		if ($pl instanceof Player) $username = $pl->getName();

		Core::getInstance()->getUserPool()->useUser($username, function (User $user) use ($player): void {
			if (!$player->isConnected()) return;
			if (!$user->valid()) {
				$player->showModal(new ViewWarnsUi("Player never seen!"));
				return;
			}
			
			Core::getInstance()->getSessionManager()->useSession($user, function (CoreSession $session) use ($player, $user): void {
				if (!$player->isConnected()) return;
				$warnManager = $session->getStaff()->getWarnManager();
				$warns = $warnManager->getWarns();

				if (empty($warns)) {
					$player->showModal(new ViewWarnsUi("No warns found for user: " . $user->getGamertag()));
					return;
				}

				$player->showModal(new WarnsListUi($player, $warns, $user));
			});
		});
	}
}
