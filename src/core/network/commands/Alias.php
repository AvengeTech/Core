<?php

namespace core\network\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use core\{
	Core,
	AtPlayer as Player
};
use core\command\type\CoreCommand;
use core\rank\Rank;
use core\session\mysqli\data\{
	MySqlRequest,
	MySqlQuery
};
use core\utils\TextFormat;

class Alias extends CoreCommand {

	public Core $plugin;

	public function __construct(Core $plugin, $name, $description) {
		$this->plugin = $plugin;
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_STAFF);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args): void {
		if (empty($args)) {
			$sender->sendMessage(TextFormat::RI . "Usage: /alias <name> [ip:dev]");
			return;
		}
		$name = array_shift($args);
		if (empty($args)) {
			$type = "ip";
		} else {
			$type = strtolower(array_shift($args));
		}

		switch ($type) {
			default:
			case "ip":
			case "address":
				Core::getInstance()->getUserPool()->useUser($name, function (\core\user\User $user) use ($sender, $name): void {
					if ($sender instanceof Player && !$sender->isConnected()) return;
					$xuid = $user->getXuid();
					Core::getInstance()->getSessionManager()->sendStrayRequest(new MySqlRequest(
						"alias_ip_" . $sender->getName() . "_" . $xuid,
						new MySqlQuery(
							"main",
							"SELECT lastaddress FROM network_playerdata WHERE xuid=?",
							[$xuid]
						)
					), function (MySqlRequest $request) use ($sender, $xuid, $name): void {
						if ($sender instanceof Player && !$sender->isConnected()) return;
						$rows = $request->getQuery()->getResult()->getRows();
						if (count($rows) == 0) {
							$sender->sendMessage(TextFormat::RI . "No address associated with this account.");
							return;
						}
						$result = $rows[0];
						$address = $result["lastaddress"];
						if ($address === null) {
							$sender->sendMessage(TextFormat::RI . "This user has no address attached to their account! (for some odd reason)");
							return;
						}
						Core::getInstance()->getSessionManager()->sendStrayRequest(new MySqlRequest(
							"alias_ip_" . $sender->getName() . "_" . $xuid . "_pt2",
							new MySqlQuery(
								"main",
								"SELECT xuid FROM network_playerdata WHERE lastaddress=?",
								[$address]
							)
						), function (MySqlRequest $request) use ($sender, $xuid, $name, $address): void {
							if ($sender instanceof Player && !$sender->isConnected()) return;
							$xuids = [];
							$rows = $request->getQuery()->getResult()->getRows();
							foreach ($rows as $row) $xuids[] = $row["xuid"];

							$request = new MySqlRequest("alias_ip_" . $sender->getName() . "_" . $xuid . "_pt3", []);
							foreach ($xuids as $x) {
								$request->addQuery(new MySqlQuery($x, "SELECT gamertag FROM network_playerdata WHERE xuid=?", [$x]));
							}
							Core::getInstance()->getSessionManager()->sendStrayRequest($request, function (MySqlRequest $request) use ($sender, $name, $xuid, $address): void {
								if ($sender instanceof Player && !$sender->isConnected()) return;
								$names = [];
								$nr = new MySqlRequest("alias_ip_" . $sender->getName() . "_" . $xuid . "_pt4", []);
								foreach ($request->getQueries() as $query) {
									$nr->addQuery(new MySqlQuery(($query->getResult()->getRows()[0] ?? [])["gamertag"] ?? "Server", "SELECT xuid FROM staff_bans WHERE xuid=?", [$query->getId()]));
								}
								Core::getInstance()->getSessionManager()->sendStrayRequest($nr, function (MySqlRequest $request) use ($sender, $name, $address): void {
									$names = [];
									foreach ($request->getQueries() as $query) {
										$names[$query->getId()] = count($query->getResult()->getRows()) > 0;
									}
									if ($sender instanceof Player) {
										if (!$sender->isTier3()) {
											$aa = explode(".", $address);
											$address = "";
											foreach ($aa as $part) {
												$address .= str_repeat("*", strlen($part)) . ".";
											}
											$address = rtrim($address, ".");
										}
									}
									$string = "";
									foreach ($names as $n => $banned) {
										$string .= ($banned ? TextFormat::BOLD . TextFormat::RED . "BANNED " . TextFormat::RESET : "") . TextFormat::YELLOW . $n . TextFormat::GRAY . ", ";
									}
									$sender->sendMessage(TextFormat::YELLOW . "Listing accounts with similar IP address to " . TextFormat::AQUA . $name . TextFormat::GRAY . " (" . TextFormat::WHITE . "IP: " . $address . TextFormat::GRAY . ")" . PHP_EOL . $string);
								});
							});
						});
					});
				});
				$sender->sendMessage(TextFormat::YI . "Checking for associated accounts...");
				break;

			case "dev":
			case "device":
				Core::getInstance()->getUserPool()->useUser($name, function (\core\user\User $user) use ($sender, $name): void {
					if ($sender instanceof Player && !$sender->isConnected()) return;
					$xuid = $user->getXuid();
					Core::getInstance()->getSessionManager()->sendStrayRequest(new MySqlRequest(
						"alias_did_" . $sender->getName() . "_" . $xuid,
						new MySqlQuery(
							"main",
							"SELECT deviceid FROM network_playerdata WHERE xuid=?",
							[$xuid]
						)
					), function (MySqlRequest $request) use ($sender, $xuid, $name): void {
						if ($sender instanceof Player && !$sender->isConnected()) return;
						$rows = $request->getQuery()->getResult()->getRows();
						if (count($rows) == 0) {
							$sender->sendMessage(TextFormat::RI . "No device ID associated with this account.");
							return;
						}
						$result = $rows[0];
						$deviceid = $result["deviceid"];
						if ($deviceid === null) {
							$sender->sendMessage(TextFormat::RI . "This user has no device ID attached to their account! (for some odd reason)");
							return;
						}
						Core::getInstance()->getSessionManager()->sendStrayRequest(new MySqlRequest(
							"alias_did_" . $sender->getName() . "_" . $xuid . "_pt2",
							new MySqlQuery(
								"main",
								"SELECT xuid FROM network_playerdata WHERE deviceid=?",
								[$deviceid]
							)
						), function (MySqlRequest $request) use ($sender, $xuid, $name, $deviceid): void {
							if ($sender instanceof Player && !$sender->isConnected()) return;
							$xuids = [];
							$rows = $request->getQuery()->getResult()->getRows();
							foreach ($rows as $row) $xuids[] = $row["xuid"];

							$request = new MySqlRequest("alias_did_" . $sender->getName() . "_" . $xuid . "_pt3", []);
							foreach ($xuids as $x) {
								$request->addQuery(new MySqlQuery($x, "SELECT gamertag FROM network_playerdata WHERE xuid=?", [$x]));
							}
							Core::getInstance()->getSessionManager()->sendStrayRequest($request, function (MySqlRequest $request) use ($sender, $name, $xuid, $deviceid): void {
								if ($sender instanceof Player && !$sender->isConnected()) return;
								$names = [];
								$nr = new MySqlRequest("alias_did_" . $sender->getName() . "_" . $xuid . "_pt4", []);
								foreach ($request->getQueries() as $query) {
									$nr->addQuery(new MySqlQuery(($query->getResult()->getRows()[0] ?? [])["gamertag"] ?? "Server", "SELECT xuid FROM staff_bans WHERE xuid=?", [$query->getId()]));
								}
								Core::getInstance()->getSessionManager()->sendStrayRequest($nr, function (MySqlRequest $request) use ($sender, $name, $deviceid): void {
									$names = [];
									foreach ($request->getQueries() as $query) {
										$names[$query->getId()] = count($query->getResult()->getRows()) > 0;
									}
									$string = "";
									foreach ($names as $n => $banned) {
										$string .= ($banned ? TextFormat::BOLD . TextFormat::RED . "BANNED " . TextFormat::RESET : "") . TextFormat::YELLOW . $n . TextFormat::GRAY . ", ";
									}
									$sender->sendMessage(TextFormat::YELLOW . "Listing accounts with similar device ID to " . TextFormat::AQUA . $name . TextFormat::GRAY . " (" . TextFormat::WHITE . "DID: " . $deviceid . TextFormat::GRAY . ")" . PHP_EOL . $string);
								});
							});
						});
					});
				});
				$sender->sendMessage(TextFormat::YI . "Checking for associated accounts...");
				break;
		}
	}

	public function getPlugin(): Core {
		return $this->plugin;
	}
}
