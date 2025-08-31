<?php

namespace core\network\commands\override;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\{
	Core,
	AtPlayer as Player,
	AtPlayer
};
use core\chat\Structure;
use core\command\type\CoreCommand;
use core\user\User;
use core\network\server\ServerInstance;
use core\rank\Rank;
use core\settings\GlobalSettings;
use core\utils\TextFormat;

class ListCommand extends CoreCommand {

	public function __construct(public Core $plugin, $name, $description) {
		parent::__construct($name, $description);
		$this->setAliases(["players"]);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args) {
		$isplayer = $sender instanceof AtPlayer;
		if ($isplayer) {
			/** @var Player $sender */
			$staff = $sender->getRankHierarchy() > 7;
			$legacyRanks = $sender->getSession()?->getSettings()->getSetting(GlobalSettings::LEGACY_RANK_ICONS) ?? false;
		} else {
			$staff = true;
			$legacyRanks = false;
		}
		$network = Core::getInstance()->getNetwork();
		$nameFormat = "{name}";
		$nicknameFormat = "*{nick} ({name})";
		$format = TextFormat::YELLOW . "{vanish}{name}";
		$rankedformat = TextFormat::BOLD . "{rank} " . TextFormat::RESET . TextFormat::YELLOW . "{vanish}{nameformat}";
		$onlinestring = "";

		if (count($args) > 0) {
			$sub = strtolower(array_shift($args));
			if (($server = Core::getInstance()->getNetwork()->getServerManager()->getServerById($sub)) !== null && $server->isOnline()) {
				$cluster = $server->getCluster();
				$sbs = [];

				foreach ($cluster->getPlayers() as $player) {
					$sbs[] = $player->getGamertag();
				}
				$count = count($cluster->getPlayers());

				$sender->sendMessage(TextFormat::GN . "Loading player list for " . TextFormat::GREEN . $server->getIdentifier());
				Core::getInstance()->getUserPool()->useUsers(
					$sbs,
					function (array $norank) use ($count, $sender, $onlinestring, $network, $format, $rankedformat, $staff, $server, $legacyRanks, $nameFormat, $nicknameFormat): void {
						/** @var User[] $norank */
						$norankstrings = [];
						foreach ($norank as $user) $norankstrings[] = $user->getName();
						Core::getInstance()->getUserPool()->useUsers(
							$norankstrings,
							function (array $withrank) use ($count, $sender, $onlinestring, $network, $format, $rankedformat, $staff, $server, $legacyRanks, $norank, $nameFormat, $nicknameFormat): void {
								/** @var User[] $withrank */
								/** @var User[] $users */
								$users = $withrank;
								$unuse = [];
								foreach ($users as $user) $unuse[] = $user->getName();
								foreach ($norank as $user) {
									if (!in_array($user->getName(), $unuse)) {
										$unuse[] = $user->getName();
										$users[] = $user;
									}
								}
								$byrank = [
									"owner" => [],
									"staff" => [],
									"yt" => [],
									"enderdragon" => [],
									"wither" => [],
									"enderman" => [],
									"ghast" => [],
									"blaze" => [],
									"endermite" => [],
									"default" => [],
								];
								foreach ($users as $user) {
									$pform = str_replace("{nameformat}", $user->hasNick() ? $nicknameFormat : $nameFormat, $user->getRank() == "default" ? $format : $rankedformat);
									$ch = Core::getInstance()->getChat();
									$pform = str_replace(["{rank}", "{name}", "{nick}", "{vanish}"], [$legacyRanks ? TextFormat::BOLD . Structure::LEGACY_RANK_FORMATS[strtolower($user->getRank())] . TextFormat::RESET : $ch->getFormattedRank($user->getRank()), $user->getName(), $user->getNick() ?? "", ""], $pform);

									if ($user->getRankHierarchy() <= 7 || $staff) {
										foreach ($byrank as $rank => $arr) {
											if ($user->getRank() == $rank) {
												$byrank[$rank][] = $pform;
												break;
											}
										}
										$yt = ["youtuber", "youtuber+", "youtuber++", "youtuber+++"];
										if (in_array($user->getRank(), $yt)) {
											$byrank["yt"][] = $pform;
										}
										if ($user->getRankHierarchy() > 7 && $user->getRank() != "owner") {
											$byrank["staff"][] = $pform;
										}
									}
								}
								foreach ($byrank as $rank => $array) {
									foreach (array_unique($array) as $format) {
										$onlinestring .= $format . TextFormat::GRAY . ", ";
									}
								}
								if (!$sender instanceof Player || $sender->isConnected()) $sender->sendMessage(
									TextFormat::GOLD . str_repeat("=", 16) . PHP_EOL .
										TextFormat::AQUA . "There are " . TextFormat::YELLOW . $count . TextFormat::AQUA . " players on " . TextFormat::GREEN . $server->getIdentifier() . TextFormat::AQUA . ":" . PHP_EOL .
										$onlinestring . PHP_EOL .
										TextFormat::YELLOW . $network->getServerManager()->getTotalPlayers() . TextFormat::AQUA . " total players are playing on AvengeTech!" . PHP_EOL .
										TextFormat::GOLD . str_repeat("=", 16)
								);
							},
							true
						);
					}
				);
				return;
			} else {
				if ($server !== null) {
					$sender->sendMessage(TextFormat::RN . TextFormat::RED . $server->getIdentifier() . TextFormat::GRAY . " is currently offline!");
				} else {
					$exists = false;
					$exceptions = [];
					/** @var array<string, ServerInstance[]> */
					$subByType = [];
					foreach (Core::getInstance()->getNetwork()->getServerManager()->getServers() as $server) {
						if (!$server->isOnline()) {
							if ($server->getType() === $sub) $exists = true;
							continue;
						}
						$exceptions[] = $server->getType();
						$subByType[$server->getType()] ??= [];
						$subByType[$server->getType()][] = $server;
					}
					$exceptions = array_unique($exceptions);
					if (in_array($sub, $exceptions)) {
						$sbs = [];
						$sbsFrom = [];
						foreach ($subByType[$sub] as $ss) {
							foreach ($ss->getCluster()->getPlayers() as $player) {
								$sbs[] = $player->getGamertag();
								$sbsFrom[$player->getGamertag()] = $ss->getIdentifier();
							}
						}
						$count = count($sbs);
						$sender->sendMessage(TextFormat::GN . "Loading player list for " . TextFormat::GREEN . $sub);
						Core::getInstance()->getUserPool()->useUsers(
							$sbs,
							function (array $norank) use ($count, $sender, $onlinestring, $network, $format, $rankedformat, $staff, $sub, $legacyRanks, $nameFormat, $nicknameFormat, $sbsFrom): void {
								/** @var User[] $norank */
								$norankstrings = [];
								foreach ($norank as $user) $norankstrings[] = $user->getName();
								Core::getInstance()->getUserPool()->useUsers(
									$norankstrings,
									function (array $withrank) use ($count, $sender, $onlinestring, $network, $format, $rankedformat, $staff, $sub, $legacyRanks, $norank, $nameFormat, $nicknameFormat, $sbsFrom): void {
										/** @var User[] $withrank */
										/** @var User[] $users */
										$users = $withrank;
										$unuse = [];
										foreach ($users as $user) $unuse[] = $user->getName();
										foreach ($norank as $user) {
											if (!in_array($user->getName(), $unuse)) {
												$unuse[] = $user->getName();
												$users[] = $user;
											}
										}
										$byrank = [
											"owner" => [],
											"staff" => [],
											"yt" => [],
											"enderdragon" => [],
											"wither" => [],
											"enderman" => [],
											"ghast" => [],
											"blaze" => [],
											"endermite" => [],
											"default" => [],
										];
										$servers = [];
										foreach ($users as $user) {
											if (isset($sbsFrom[$user->getName()])) $sub = $sbsFrom[$user->getName()];
											else $sub = "here";
											$servers[$sub] ??= $byrank;

											$pform = str_replace("{nameformat}", $user->hasNick() ? $nicknameFormat : $nameFormat, $user->getRank() == "default" ? $format : $rankedformat);
											$ch = Core::getInstance()->getChat();
											$pform = str_replace(["{rank}", "{name}", "{nick}", "{vanish}"], [$legacyRanks ? TextFormat::BOLD . Structure::LEGACY_RANK_FORMATS[strtolower($user->getRank())] . TextFormat::RESET : $ch->getFormattedRank($user->getRank()), $user->getName(), $user->getNick() ?? "", ""], $pform);

											if ($user->getRankHierarchy() < Rank::HIERARCHY_STAFF || $staff) {
												foreach ($servers[$sub] as $rank => $arr) {
													if ($user->getRank() == $rank) {
														$servers[$sub][$rank][] = $pform;
														break;
													}
												}
												$yt = ["youtuber", "youtuber+", "youtuber++", "youtuber+++"];
												if (in_array($user->getRank(), $yt)) {
													$servers[$sub]["yt"][] = $pform;
												}
												if ($user->getRankHierarchy() > Rank::HIERARCHY_STAFF && $user->getRank() != "owner") {
													$servers[$sub]["staff"][] = $pform;
												}
											}
										}
										foreach ($servers as $sub => $byrank) { // iteration hell
											$onlinestring .= TextFormat::GREEN . $sub . " (" . array_sum(array_map('count', $byrank)) . ")" . TextFormat::GRAY . ": ";
											foreach ($byrank as $rank => $array) {
												foreach (array_unique($array) as $format) {
													$onlinestring .= $format . TextFormat::GRAY . ", ";
												}
											}
											$onlinestring .= PHP_EOL;
										}
										if (!$sender instanceof Player || $sender->isConnected()) $sender->sendMessage(
											TextFormat::GOLD . str_repeat("=", 16) . PHP_EOL .
												TextFormat::AQUA . "There are " . TextFormat::YELLOW . $count . TextFormat::AQUA . " players on " . TextFormat::GREEN . $sub . TextFormat::AQUA . ":" . PHP_EOL .
												$onlinestring .
												TextFormat::YELLOW . $network->getServerManager()->getTotalPlayers() . TextFormat::AQUA . " total players are playing on AvengeTech!" . PHP_EOL .
												TextFormat::GOLD . str_repeat("=", 16)
										);
									},
									true
								);
							}
						);
					} elseif (!$exists) {
						$sender->sendMessage(TextFormat::RN . "No server called " . TextFormat::RED . $sub . TextFormat::GRAY . " exists on the network!");
					} else {
						$sender->sendMessage(TextFormat::RN . TextFormat::RED . $sub . TextFormat::GRAY . " is currently offline!");
					}
				}
				return;
			}
		}
		$byrank = [
			"owner" => [],
			"staff" => [],
			"yt" => [],
			"enderdragon" => [],
			"wither" => [],
			"enderman" => [],
			"ghast" => [],
			"blaze" => [],
			"endermite" => [],
			"default" => [],
		];

		$used = [];
		foreach ($players = $this->plugin->getServer()->getOnlinePlayers() as $player) {
			if (in_array($player->getName(), $used)) continue;
			$used[] = $player->getName();
			/** @var Player $player */
			if (!$player->isLoaded()) continue;
			$rs = $player->getSession()->getRank();
			$pform = str_replace("{nameformat}", (!$rs->isDisguiseEnabled() && ($rs->hasSub() && $rs->hasNick())) ? $nicknameFormat : $nameFormat, $player->getRank() == "default" ? $format : $rankedformat);
			$pform = str_replace(["{rank}", "{name}", "{nick}", "{vanish}"], [$legacyRanks ? TextFormat::BOLD . Structure::LEGACY_RANK_FORMATS[strtolower($rs->isDisguiseEnabled() ? $rs->getDisguise()->getRank() : $rs->getRank())] . TextFormat::RESET : $rs->getRankIcon(), $rs->isDisguiseEnabled() ? $rs->getDisguise()->getName() : $player->getName(), $rs->getNick(), $player->isVanished() ? TextFormat::GRAY : ""], $pform);

			if (($isplayer && (!$player->isVanished() || $staff)) || !$isplayer) {
				if (!$player->isVanished() || $staff) {
					$foundByRank = false;
					foreach ($byrank as $rank => $arr) {
						if (($rs->isDisguiseEnabled() ? $rs->getDisguise()->getRank() : $player->getRank()) == $rank) {
							$byrank[$rank][] = $pform;
							$foundByRank = true;
							break;
						}
					}
					$yt = ["youtuber", "youtuber+", "youtuber++", "youtuber+++"];
					if (in_array(($rs->isDisguiseEnabled() ? $rs->getDisguise()->getRank() : $player->getRank()), $yt) && !$foundByRank) {
						$byrank["yt"][] = $pform;
					}
					if ($player->getRankHierarchy() > 7 && ($rs->isDisguiseEnabled() ? $rs->getDisguise()->getRank() : $player->getRank()) != "owner" && !$foundByRank) {
						$byrank["staff"][] = $pform;
					}
				}
			}
		}
		foreach ($byrank as $rank => $array) {
			foreach (array_unique($array) as $format) {
				$onlinestring .= $format . TextFormat::GRAY . ", ";
			}
		}

		$count = count($players);

		$seconds = $network->getUptime();
		$hours = floor($seconds / 3600);
		$minutes = floor(((int) ($seconds / 60)) % 60);
		if (strlen((string) $hours) == 1) $hours = "0" . $hours;
		if (strlen((string) $minutes) == 1) $minutes = "0" . $minutes;
		$uptime = TextFormat::GOLD . $hours . TextFormat::RED . " hours, " . TextFormat::GOLD . $minutes . TextFormat::RED . " minutes";

		$elsestring = "";
		$sender->sendMessage(TextFormat::GN . "Loading player list...");
		$sbs = [];
		foreach (Core::thisServer()->getSubServers(false) as $ss) {
			foreach ($ss->getCluster()->getPlayers() as $ssu)
				if (!in_array($ssu->getUser()->getName(), $sbs) && !($this->plugin->getServer()->getPlayerExact($ssu->getUser()->getName()) instanceof Player)) $sbs[] = $ssu->getUser()->getName();
		}
		Core::getInstance()->getUserPool()->useUsers(
			$sbs,
			function (array $norank) use ($count, $sender, $uptime, $onlinestring, $elsestring, $network, $format, $rankedformat, $staff, $used, $legacyRanks, $nameFormat, $nicknameFormat): void {
				/** @var User[] $norank */
				$norankstrings = [];
				foreach ($norank as $user) $norankstrings[] = $user->getName();
				Core::getInstance()->getUserPool()->useUsers(
					$norankstrings,
					function (array $withrank) use ($count, $sender, $uptime, $onlinestring, $elsestring, $network, $format, $rankedformat, $staff, $used, $legacyRanks, $norank, $nameFormat, $nicknameFormat): void {
						/** @var User[] $withrank */
						/** @var User[] $users */
						$users = $withrank;
						$unuse = [];
						foreach ($users as $user) $unuse[] = $user->getName();
						foreach ($norank as $user) {
							if (!in_array($user->getName(), $unuse)) {
								$unuse[] = $user->getName();
								$users[] = $user;
							}
						}
						$byrank = [
							"owner" => [],
							"staff" => [],
							"yt" => [],
							"enderdragon" => [],
							"wither" => [],
							"enderman" => [],
							"ghast" => [],
							"blaze" => [],
							"endermite" => [],
							"default" => [],
						];
						/** @var User[] $users */
						foreach ($users as $user) {
							if (in_array($user->getName(), $used)) continue;
							$used[] = $user->getName();
							$count++;
							$pform = str_replace("{nameformat}", $user->hasNick() ? $nicknameFormat : $nameFormat, $user->getRank() == "default" ? $format : $rankedformat);
							$ch = Core::getInstance()->getChat();
							$pform = str_replace(["{rank}", "{name}", "{nick}", "{vanish}"], [$legacyRanks ? TextFormat::BOLD . Structure::LEGACY_RANK_FORMATS[strtolower($user->getRank())] . TextFormat::RESET : $ch->getFormattedRank($user->getRank()), $user->getName(), $user->getNick() ?? "", ""], $pform);

							if ($user->getRankHierarchy() <= 7 || $staff) {
								foreach ($byrank as $rank => $arr) {
									if ($user->getRank() == $rank) {
										$byrank[$rank][] = $pform;
										break;
									}
								}
								$yt = ["youtuber", "youtuber+", "youtuber++", "youtuber+++"];
								if (in_array($user->getRank(), $yt)) {
									$byrank["yt"][] = $pform;
								}
								if ($user->getRankHierarchy() > 7 && $user->getRank() != "owner") {
									$byrank["staff"][] = $pform;
								}
							}
						}
						foreach ($byrank as $rank => $array) {
							foreach (array_unique($array) as $format) {
								$elsestring .= $format . TextFormat::GRAY . ", ";
							}
						}
						if (!$sender instanceof Player || $sender->isConnected()) $sender->sendMessage(
							TextFormat::GOLD . str_repeat("=", 16) . PHP_EOL .
								TextFormat::YELLOW . TextFormat::BOLD . "UPTIME: " . TextFormat::RESET . $uptime . PHP_EOL .
								TextFormat::GOLD . str_repeat("=", 16) . PHP_EOL .
								TextFormat::AQUA . "There are " . TextFormat::YELLOW . $count . TextFormat::AQUA . " players on this server instance!" . PHP_EOL .
								$onlinestring . PHP_EOL .
								TextFormat::AQUA . "Players on other subservers:" . PHP_EOL .
								$elsestring . PHP_EOL .
								TextFormat::YELLOW . $network->getServerManager()->getTotalPlayers() . TextFormat::AQUA . " total players are playing on AvengeTech!" . PHP_EOL .
								TextFormat::GOLD . str_repeat("=", 16)
						);
					},
					true
				);
			}
		);
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
