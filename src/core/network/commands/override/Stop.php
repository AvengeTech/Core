<?php

namespace core\network\commands\override;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\{
	Core,
	AtPlayer as Player
};
use core\command\type\CoreCommand;
use core\discord\objects\{
	Post,
};
use core\rank\Rank;
use core\utils\TextFormat;

use lobby\Lobby;
use skyblock\SkyBlock;
use prison\Prison;
use pvp\PvP;

class Stop extends CoreCommand {

	public function __construct(public Core $plugin, $name, $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
		$this->setAliases(["s"]);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args) {
		$network = Core::getInstance()->getNetwork();

		$confirmed = strtolower((string)array_shift($args)) == "confirm" || !($sender instanceof Player) || $sender->isSn3ak();

		if (!$confirmed) {
			$sender->sendMessage(TextFormat::RN . "Please use \"/" . $commandLabel . " confirm\" to force stop the server. (smh guys)");
			return;
		}

		$post = new Post("Server has been force shutdown by " . $sender->getName() . "!", "Network - " . $this->plugin->getNetwork()->getIdentifier());
		$post->setWebhook($this->plugin->getDiscord()->getConsoleWebhook());
		$post->send();

		$network->scheduleShutdown();

		$sm = $network->getServerManager();
		switch ($sm->getThisServer()->getType()) {
			case "lobby":
				($ssm = Lobby::getInstance()->getSessionManager())->saveAll();
				$ssm->saveOnLeave = false;
				break;

			case "prison":
				foreach (Prison::getInstance()->getBlockTournament()->getGameManager()->getActiveGames() as $game) {
					$game->end(true);
				}
				Prison::getInstance()->getGangs()->getGangManager()->getBattleManager()->cancelAllBattles("Server restarting!");
				Prison::getInstance()->getCombat()->close();
				Prison::getInstance()->getTrade()->close();

				($ssm = Prison::getInstance()->getSessionManager())->saveAll();
				$ssm->saveOnLeave = false;
				break;
			case "skyblock":
				SkyBlock::getInstance()->getCombat()->removeLogs();
				SkyBlock::getInstance()->getGames()->close();
				SkyBlock::getInstance()->getIslands()->getIslandManager()->saveAll();
				SkyBlock::getInstance()->getTrade()->close();
				SkyBlock::getInstance()->getAuctionHouse()->getAuctionManager()->save();

				($ssm = SkyBlock::getInstance()->getSessionManager())->saveAll();
				$ssm->saveOnLeave = false;
				break;
			case "pvp":
				($ssm = PvP::getInstance()->getSessionManager())->saveAll();
				$ssm->saveOnLeave = false;
				break;
		}

		($ssm = $this->plugin->getSessionManager())->saveAll();
		$ssm->saveOnLeave = false;

		$sm->getThisServer()->reconnectAll();
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
