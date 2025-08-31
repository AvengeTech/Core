<?php

namespace core\scoreboards\commands;

use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use core\{
	Core,
	AtPlayer as Player
};
use core\utils\TextFormat;
use lobby\LobbyPlayer;
use prison\PrisonPlayer;
use skyblock\SkyBlockPlayer;

class ToggleCommand extends CoreCommand {

	public function __construct(public \core\Core $plugin, $name, $description) {
		parent::__construct($name, $description);
		$this->setInGameOnly();
		$this->setAliases(["ts", "toggles"]);
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args) {
		$scoreboards = Core::getInstance()->getScoreboards();
		if ($scoreboards->getPlayerScoreboard($sender) !== null) {
			$scoreboards->removeScoreboard($sender, true);
		} else {
			if (!Core::thisServer()->isTestServer()) {
				$scoreboards->addScoreboard($sender);
				return;
			}
			switch (Core::thisServer()->getType()) {
				case "skyblock":
					/** @var SkyBlockPlayer $sender */
					if ($sender->getGameSession()->getParkour()->hasCourseAttempt()) {
						$sender->sendMessage(TextFormat::RI . "You cannot disable your scoreboard during a parkour attempt!");
						return;
					}
					if (($is = $sender->getGameSession()->getIslands())->atIsland()) {
						if (($ia = $is->getIslandAt())->getScoreboard($sender) === null) {
							$ia->addScoreboard($sender);
							return;
						} else {
							$ia->removeScoreboard($sender);
						}
					}
					if (($ks = $sender->getGameSession()->getKoth())->inGame()) {
						if (($game = $ks->getGame())->getScoreboard($sender) === null) {
							$game->addScoreboard($sender);
							return;
						} else {
							$game->removeScoreboard($sender);
						}
					}
					break;
				case "prison":
					/** @var PrisonPlayer $sender */
					//check bt
					break;
				case "lobby":
					/** @var LobbyPlayer $sender */
					if ($sender->getGameSession()->getParkour()->hasCourseAttempt()) {
						$sender->sendMessage(TextFormat::RI . "You cannot disable your scoreboard during a parkour attempt!");
						return;
					}
					break;
			}
			$scoreboards->addScoreboard($sender);
		}
		$sender->sendMessage(TextFormat::GI . "Toggled Scoreboard View.");
	}
}
