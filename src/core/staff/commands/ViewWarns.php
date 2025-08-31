<?php

namespace core\staff\commands;

use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\Server;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\AtPlayer as Player;
use core\Core;
use core\staff\entry\WarnManager;
use core\staff\uis\actions\warn\WarnsListUi;
use core\user\User;
use core\utils\TextFormat;

class ViewWarns extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_STAFF);
		$this->setAliases(["vw"]);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args): void {
		if (count($args) == 0) {
			$sender->sendMessage(TextFormat::RI . "Must provide a username!");
			return;
		}
		$username = array_shift($args);
		$page = array_shift($args) ?? 1;

		$view = function (WarnManager $warnManager, User $user) use ($sender, $page): void {
			if ($sender instanceof Player && $sender->isConnected()) {
				$sender->showModal(new WarnsListUi($sender, $warnManager->getWarns(), $user));
			} else {
				$warns = $warnManager->getWarns();
				$chunks = array_chunk($warns, 6);
				$ws =
					"This player has " . count($warns) . " warns:" . PHP_EOL .
					"----- (Page " . $page . "/" . count($chunks) . ") -----" . PHP_EOL;

				foreach (($chunks[$page - 1] ?? []) as $warn) {
					$ws .=
						$warn->getType() . " | " . $warn->getReason() . " | " . $warn->getIssuer() . PHP_EOL;
				}
				$sender->sendMessage($ws);
			}
		};

		Core::getInstance()->getUserPool()->useUser($username, function (User $user) use ($sender, $view) {
			Core::getInstance()->getStaff()->loadWarnings($user, function (WarnManager $warnManager) use ($sender, $user, $view) {
				$view($warnManager, $user);
			});
		});
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
