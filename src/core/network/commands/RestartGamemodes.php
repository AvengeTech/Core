<?php

namespace core\network\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\{
	Core,
};
use core\command\type\CoreCommand;
use core\discord\objects\{
	Post,
};
use core\rank\Rank;
use core\utils\TextFormat;

class RestartGamemodes extends CoreCommand {

	public $plugin;

	public function __construct(Core $plugin, $name, $description) {
		$this->plugin = $plugin;
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
		$this->setAliases(["rg"]);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args) {
		$sm = $this->plugin->getNetwork()->getServerManager();
		foreach ($sm->getServers() as $server) {
			if (in_array($server->getType(), ["prison", "skyblock", "creative", "build"])) {
				$post = new Post("^command s", "Network - " . $this->plugin->getNetwork()->getIdentifier());
				$post->setWebhook($this->plugin->getDiscord()->getConsoleWebhook($server->getIdentifier()));
				$post->send();
			}
		}
		$sender->sendMessage(TextFormat::GI . "Scheduled a force shutdown to all gamemodes!");
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
