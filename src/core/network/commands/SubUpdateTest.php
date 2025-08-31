<?php

namespace core\network\commands;

use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\{
	Core,
};
use core\command\type\CoreCommand;
use core\network\protocol\ServerSubUpdatePacket;
use core\rank\Rank;
use core\utils\TextFormat;

class SubUpdateTest extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args): void {
		if (count($args) === 0) {
			$sender->sendMessage(TextFormat::RI . "Usage: /su <server> <type> [data]");
			return;
		}

		$server = array_shift($args);
		$type = array_shift($args);
		$data = array_shift($args) ?? ""; //todo parse idc rn

		(new ServerSubUpdatePacket([
			"server" => $server,
			"type" => $type
		]))->queue();
		$sender->sendMessage(TextFormat::GI . "Sub update packet queued!");
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
