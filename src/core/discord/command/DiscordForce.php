<?php

namespace core\discord\command;

use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\command\CommandSender;
use core\{
	Core,
	AtPlayer as Player
};
use core\network\server\ServerInstance;
use core\utils\TextFormat;

class DiscordForce extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
		$this->setAliases(["df"]);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args): void {
		$withSnowflake = function (ServerInstance $server, int $snowflake, string $command) use ($sender): void {
			Core::getInstance()->getDiscord()->getCommandManager()->forceCommandsTo($command, $identifier = $server->getIdentifier(), $snowflake);
			$sender->sendMessage(TextFormat::GI . "Command forced to " . TextFormat::YELLOW . $identifier . TextFormat::GRAY . " command manager!");
		};

		$identifier = strtolower(array_shift($args) ?? "");
		if (($server = Core::getInstance()->getNetwork()->getServerManager()->getServerById($identifier)) === null) {
			$sender->sendMessage(TextFormat::RI . "Invalid server ID provided.");
			return;
		}
		$command = implode(" ", $args);

		if ($sender instanceof Player) {
			$session = $sender->getSession()->getDiscord();
			if (!$session->isVerified()) {
				$sender->sendMessage(TextFormat::RI . "Must be verified to discord to do this!");
				return;
			}
			$snowflake = $session->getSnowflake();
		} elseif ($sender instanceof DiscordSender) {
			$snowflake = $sender->getSnowflake();
		} else {
			$snowflake = 0;
		}

		$withSnowflake($server, $snowflake, $command);
	}

	public function getPlugin(): \pocketmine\plugin\Plugin {
		return $this->plugin;
	}
}
