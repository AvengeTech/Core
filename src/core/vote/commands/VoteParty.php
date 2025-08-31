<?php

namespace core\vote\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\Core;
use core\AtPlayer as Player;
use core\command\type\CoreCommand;
use core\network\protocol\PlayerLoadActionPacket;
use core\rank\Rank;
use core\staff\anticheat\session\SessionManager;
use core\utils\TextFormat;
use prison\PrisonPlayer;
use skyblock\SkyBlockPlayer;

class VoteParty extends CoreCommand{

	public $plugin;

	public function __construct(Core $plugin, $name, $description) {
		$this->plugin = $plugin;
		parent::__construct($name, $description);
		$this->setInGameOnly();
		$this->setAliases(["vp"]);
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args)
	{
		if($sender->getRankHierarchy() >= Rank::HIERARCHY_HEAD_MOD){
			if(count($args) !== 0){
				switch(array_shift($args)){
					case "start":
						$sender->sendMessage(TextFormat::GI . "Vote party force started!");
						Core::getInstance()->getVote()->startParty();
						return;
					case "set":
						$num = (int) array_shift($args);
						$sender->sendMessage(TextFormat::GI . "Vote party set to " . $num . "!");
						Core::getInstance()->getVote()->setVoteCount($num, true);
						return;
				}
			}
		}

		$ts = Core::thisServer();

		switch($ts->getType()){
			case "skyblock":
				/** @var SkyBlockPlayer $sender */
				if($ts->isSubServer()){
					($pk = new PlayerLoadActionPacket([
						"player" => $sender->getName(),
						"server" => $ts->getParentServer()->getIdentifier(),
						"action" => "voteparty",
					]))->queue();
					$sender->gotoSpawn(TextFormat::GN . "Teleported to vote party!");
					return;
				}

				$isession = $sender->getGameSession()->getIslands();
				if($isession->atIsland()){
					$isession->setIslandAt(null);
				}

				$ps = $sender->getGameSession()->getParkour();
				if($ps->hasCourseAttempt()){
					$ps->getCourseAttempt()->removeScoreboard();
					$ps->setCourseAttempt();
				}

				$ksession = $sender->getGameSession()->getKoth();
				if($ksession->inGame()){
					$ksession->setGame();
				}
				break;

			case "prison":
				/** @var PrisonPlayer $sender */
				if($ts->isSubServer()){
					($pk = new PlayerLoadActionPacket([
						"player" => $sender->getName(),
						"server" => $ts->getParentServer()->getIdentifier(),
						"action" => "voteparty",
					]))->queue();
					$sender->gotoSpawn(TextFormat::GN . "Teleported to vote party!");
					return;
				}
				break;
			default:
				$sender->sendMessage(TextFormat::GN . "No vote party on this server!");
				return;
		}

		$sender->teleport(Core::getInstance()->getVote()->getPartySpawn());
		$sender->setAllowFlight(true);
		$sender->sendMessage(TextFormat::GN . "Teleported to vote party!");

		return true;
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}

}
