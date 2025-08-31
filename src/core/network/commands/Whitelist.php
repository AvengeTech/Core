<?php

namespace core\network\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\{
	Core,
	AtPlayer as Player
};
use core\command\type\CoreCommand;
use core\rank\Rank;
use core\user\User;
use core\utils\TextFormat;

class Whitelist extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
		$this->setAliases(["wl"]);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args) {
		$network = Core::getInstance()->getNetwork();
		$manager = $network->getServerManager();
		if (count($args) < 1) {
			$sender->sendMessage(TextFormat::RI . "Usage: /whitelist <player> <true:false> [identifier=HERE] OR /whitelist <on:off> [rank]");
			return;
		}
		$name = array_shift($args);

		$ts = $manager->getThisServer();
		switch ($name) {
			case "on":
				if (count($args) == 0) {
					$sender->sendMessage(TextFormat::RI . "Must specify minimum rank allowed to join!");
					return;
				}
				$rank = array_shift($args);
				if (!Rank::validRank($rank)) {
					$sender->sendMessage(TextFormat::RI . "Invalid rank specified!");
					return;
				}
				$ts->setRestricted($rank);
				$ts->sendWhitelist();
				$sender->sendMessage(TextFormat::GI . "Server whitelist has been updated!");
				break;
			case "off":
				if (!$ts->isRestricted()) {
					$sender->sendMessage(TextFormat::RI . "This server is not whitelisted!");
					return;
				}
				$ts->setRestricted();
				$ts->sendWhitelist();
				$sender->sendMessage(TextFormat::GI . "Server whitelist has been updated!");
				break;
			case "list":
				if (!$ts->isRestricted()) {
					$sender->sendMessage(TextFormat::RI . "This server is not whitelisted!");
					return;
				}
				$list = $ts->getWhitelist();
				$sender->sendMessage(TextFormat::GI . "Loading whitelist...");
				Core::getInstance()->getUserPool()->useUsers($list, function (array $users) use ($sender, $ts): void {
					/** @var User[] $users */
					if (!$sender instanceof Player || $sender->isConnected()) {
						$liststr = TextFormat::GOLD . str_repeat("=", 16) . PHP_EOL . TextFormat::AQUA . "Whitelist for " . TextFormat::GREEN . $ts->getIdentifier() . PHP_EOL;
						$currentLine = 0;
						foreach ($users as $user) {
							$currentLine++;
							$liststr .= TextFormat::YELLOW . $user->getGamertag() . TextFormat::GRAY . ", ";
							if ($currentLine >= 3) {
								$currentLine = 0;
								$liststr .= PHP_EOL;
							}
						}
						if (!$currentLine !== 0) $liststr .= PHP_EOL;
						$liststr .= TextFormat::GOLD . str_repeat("=", 16);
						$sender->sendMessage($liststr);
					}
				});
				break;
			default:
				if (count($args) == 0) {
					$sender->sendMessage(TextFormat::RI . "true or false boi");
					return;
				}
				$wl = array_shift($args);

				$identifier = strtolower(array_shift($args) ?? $manager->getThisServer()->getId());
				if (($server = $manager->getServerById($identifier)) === null) {
					$sender->sendMessage(TextFormat::RI . "Invalid server ID provided.");
					return;
				}

				Core::getInstance()->getUserPool()->useUser($name, function (User $user) use ($sender, $wl, $identifier, $server): void {
					if ($sender instanceof Player && !$sender->isConnected()) return;
					if (!$user->valid()) {
						$sender->sendMessage(TextFormat::RI . "Player never seen!");
						return;
					}

					switch ($wl) {
						case "add":
						case "true":
							if ($server->whitelist($user)) {
								$sender->sendMessage(TextFormat::GI . "Successfully added " . TextFormat::YELLOW . $user->getGamertag() . TextFormat::GRAY . " to the " . TextFormat::AQUA . $identifier . TextFormat::GRAY . " whitelist!");
								return;
							}
							$sender->sendMessage(TextFormat::RI . "Player is already on this whitelist!");
							break;
						case "remove":
						case "false":
							if ($server->unwhitelist($user)) {
								$sender->sendMessage(TextFormat::GI . "Successfully removed " . TextFormat::YELLOW . $user->getGamertag() . TextFormat::GRAY . " from the " . TextFormat::AQUA . $identifier . TextFormat::GRAY . " whitelist!");
								return;
							}
							$sender->sendMessage(TextFormat::RI . "Player is not on this whitelist!");
							break;
					}
				});
				break;
		}
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
