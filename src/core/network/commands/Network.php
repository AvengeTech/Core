<?php

namespace core\network\commands;

use core\AtPlayer;
use core\command\type\CoreCommand;
use core\Core;
use core\network\server\ServerInstance;
use core\network\server\SubServer;
use core\rank\Rank;
use core\utils\TextFormat;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class Network extends CoreCommand {

	public function __construct(public Core $plugin, $name, $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_STAFF);
		$this->setAliases(["servers"]);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args) {
		$serverstring = "";
		/** @var array<string, ServerInstance[]> */
		$servers = [];
		/** @var string[] */
		$online = [];
		$longest = 0;
		foreach (Core::getInstance()->getNetwork()->getServerManager()->getServers() as $server) {
			$servers[$server->getType()] ??= [];
			$servers[$server->getType()][] = $server;
			if ($server->isOnline()) {
				if (!in_array($server->getType(), $online)) $online[] = $server->getType();
			}
		}

		foreach ($servers as $type => $ssl) {
			if (in_array($type, $online)) {
				$serverstring .= TextFormat::GREEN . $type . TextFormat::RESET . PHP_EOL;
				foreach ($ssl as $ss) {
					$id = ($ss instanceof SubServer ? $ss->getSubId(true) : $ss->getTypeId());
					$buffer = max(1, $longest - strlen($id));
					$serverstring .= ($ss->isOnline() ? TextFormat::GREEN : TextFormat::RED) . "  " . $id . TextFormat::GRAY . ":" . str_repeat(" ", $buffer) . ($ss->isOnline() ? TextFormat::GREEN . count($ss->getCluster()->getPlayers()) . TextFormat::GRAY . " online" : TextFormat::GRAY . "offline") . PHP_EOL;
				}
			} else {
				$serverstring .= TextFormat::RED . $type . TextFormat::GRAY . ": offline" . TextFormat::RESET . PHP_EOL;
			}
		}
		$sender->sendMessage(
			TextFormat::GOLD . str_repeat("=", 16) . TextFormat::RESET . PHP_EOL .
				TextFormat::BOLD . TextFormat::AQUA . "Avenge" . TextFormat::GOLD . "Tech" . TextFormat::AQUA . " Network Status" . TextFormat::RESET . PHP_EOL .
				TextFormat::RESET . trim($serverstring) . PHP_EOL .
				TextFormat::GOLD . str_repeat("=", 16)
		);
	}
}
