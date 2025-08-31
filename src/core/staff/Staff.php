<?php

namespace core\staff;

use pocketmine\Server;

use core\AtPlayer as Player;
use core\staff\commands\{
	Panel,
	Kick,
	Ban,
	BanIP,
	BanDev,
	Unban,
	UnbanIP,
	UnbanDev,
	Mute,
	MuteAs,
	Unmute,
	MuteAll,
	Warn,
	ViewWarns,
	WarnAs,
	MyWarns,

	Vanish,
	Freeze,
	Teleport,
	Seeinv,
	SeeCommand,
	SocialSpy,
	StaffChat,
	AntiCheat,
	Sudo,
	Sound,
	Cape,
	Disguise,
	GiveAll,
	SeeEnderchest
};
use core\staff\entry\{
	BanEntry,
	BanManager,
	IPBanEntry,
	DeviceBanEntry,
	MuteEntry,
	MuteManager,
	WarnEntry,
	WarnManager
};

use core\Core;
use core\chat\Chat;
use core\discord\objects\{
	Post,
	Embed,
	Field,
	Footer,
	Webhook
};
use core\inbox\object\{
	InboxInstance,
	MessageInstance
};
use core\network\protocol\{
	StaffChatPacket,
	StaffBanPacket,
	StaffBanIpPacket,
	StaffBanDevicePacket,
	StaffMutePacket,
	StaffWarnPacket
};
use core\session\CoreSession;
use core\session\mysqli\data\{
	MySqlQuery,
	MySqlRequest
};
use core\staff\anticheat\AntiCheat as AnticheatAntiCheat;
use core\user\User;
use core\utils\TextFormat;

class Staff {

	public static int $isId = 0;

	public bool $allMuted = false;
	public array $checks = [];

	public array $seeAll = [];
	public array $tellSeeAll = [];

	public array $playerByEid = [];

	public function __construct(public Core $plugin) {
		$map = $plugin->getServer()->getCommandMap();
		$map->registerAll("staff", [
			new Panel($plugin, "panel", "Opens the staff panel"),
			new Kick($plugin, "kick", "Kick players (staff)"),
			new Ban($plugin, "ban", "Ban players (CONSOLE ONLY)"),
			new BanIP($plugin, "banip", "Ban IP addresses (staff)"),
			new BanDev($plugin, "bandev", "More severe bans (staff)"),
			new Unban($plugin, "unban", "Unban players (staff)"),
			new UnbanIP($plugin, "unbanip", "Unban addresses (staff)"),
			new UnbanDev($plugin, "unbandev", "Unban players (staff)"),
			new Mute($plugin, "mute", "Mute players"),
			new MuteAs($plugin, "muteas", "Mute as a staff member (staff)"),
			new Unmute($plugin, "unmute", "Unmute players (staff)"),
			new MuteAll($plugin, "muteall", "Mute all players (tier 3)"),
			new Warn($plugin, "warn", "Warn players (staff)"),
			new ViewWarns($plugin, "viewwarns", "View another player's warns (staff)"),
			new WarnAs($plugin, "warnas", "Warn as a staff member (staff)"),
			new MyWarns($plugin, "mywarns", "View warnings given to you"),

			new Vanish($plugin, "vanish", "Turn invisible (staff)"),
			new Freeze($plugin, "freeze", "Freeze players (staff)"),
			new Teleport($plugin, "stp", "Teleport to players (staff)"),
			new Seeinv($plugin, "seeinv", "See player inventories (staff)"),
			new SeeEnderchest($plugin, "sec", "See player ender chests (staff)"),
			new SeeCommand($plugin, "seecommand", "See player commands (staff)"),
			new SocialSpy($plugin, "ss", "ss (staff)"),
			new StaffChat($plugin, "staffchat", "Toggle staff chat mode (staff)"),
			new AntiCheat($plugin, "anticheat", "AntiCheat (staff)"),
			new Sudo($plugin, "sudo", "Sudo (tier 3)"),
			new Sound($plugin, "sound", "Play sound (staff)"),
			new Cape($plugin, "cape", "Cape stuff (staff)"),
			new GiveAll($plugin, "giveall", "Gives an item to the entire server (tier 3)"),
			new Disguise($plugin, "disguise", "Gives staff a disguise! (staff)")
		]);
	}

	public function getPlayerByEid(int $eid): ?Player {
		return $this->playerByEid[$eid] ?? null;
	}

	public function areAllMuted(): bool {
		return $this->allMuted;
	}

	public function toggleAllMuted(): bool {
		return $this->allMuted = !$this->allMuted;
	}

	public function getSeeAll(): array {
		return $this->seeAll;
	}

	public function canSeeAll(string $name): bool {
		return in_array($name, $this->getSeeAll());
	}

	public function toggleSeeAll(string $name): bool {
		if ($this->canSeeAll($name)) {
			unset($this->seeAll[array_search($name, $this->seeAll)]);
			return false;
		} else {
			$this->seeAll[] = $name;
			return true;
		}
	}

	public function getTellSeeAll(): array {
		return $this->tellSeeAll;
	}

	public function canTellSeeAll(string $name): bool {
		return in_array($name, $this->getTellSeeAll());
	}

	public function toggleTellSeeAll(string $name): bool {
		if ($this->canTellSeeAll($name)) {
			unset($this->tellSeeAll[array_search($name, $this->tellSeeAll)]);
			return false;
		} else {
			$this->tellSeeAll[] = $name;
			return true;
		}
	}

	public function sendStaffMessage($player, string $message, string $identifier = ""): void {
		$name = ($player instanceof Player ? $player->getName() : $player);
		if (!$player instanceof Player || $player->hasRank()) $message = Chat::convertWithEmojis($message);
		$msg = TextFormat::BOLD . TextFormat::GREEN . "[S" . ($identifier !== "" ? "(" . $identifier . ")" : "") . ": " . $name . "] " . TextFormat::RESET . TextFormat::WHITE . ($pmsg = $message);
		foreach ($this->plugin->getServer()->getOnlinePlayers() as $p) {
			/** @var AtPlayer $p */
			if ($p->isStaff()) {
				$p->sendMessage($msg);
			}
		}

		$tid = Core::getInstance()->getNetwork()->getIdentifier();
		if ($identifier == $tid) {
			$post = new Post(
				$name . ": " . $message,
				"Staff Chat Log - " . $tid . " - " . $name,
				"[REDACTED]",
			);
			$post->setWebhook(Webhook::getWebhookByName("staff-chat-log"));
			$post->send();

			$packet = new StaffChatPacket([
				"sender" => $name,
				"message" => $message
			]);
			$packet->queue();
		}

		$this->plugin->getLogger()->info($msg);
	}

	public function sendCommandSee(string $sender, string $command, string $identifier): void {
		$viewers = $this->getSeeAll();
		$ts = Core::thisServer();
		$servs = $ts->getSubServers(true, true);
		$here = false;
		foreach ($servs as $serv) {
			if ($serv->getId() === $identifier) {
				$here = true;
				break;
			}
		}
		if ($here) {
			foreach ($viewers as $viewer) {
				$player = Server::getInstance()->getPlayerExact($viewer);
				if ($player instanceof Player) {
					$player->sendMessage(TextFormat::BOLD . TextFormat::YELLOW . "[" . TextFormat::OBFUSCATED . "|||" . TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . "] " . TextFormat::RESET . TextFormat::AQUA . $sender . ": " . TextFormat::ITALIC . TextFormat::GRAY . "/" . $command);
				}
			}
		}
		//echo $sender . " (" . $identifier . "): " . $command, PHP_EOL;
	}

	public function anticheatAlert(string $message): void {
		$staff = Core::getInstance()->getStaff()->getOnlineStaff(false);
		Server::getInstance()->broadcastMessage($message, $staff);
	}

	public static function newIsId(): int {
		return self::$isId++;
	}


	public function isStaff(Player $player, string $rank = ""): bool {
		return $this->isTier3($player) || isset(Structure::STAFF_TO_LEVEL[$rank == "" ? $player->getRank() : $rank]);
	}

	public function isTrainee(Player $player): bool {
		return $player->getRank() == "trainee";
	}

	public function isTier3(Player $player): bool {
		return in_array((int)$player->getXuid(), Structure::STAFF_TIER3);
	}

	public function getStaffLevel(Player $player): int {
		return Structure::STAFF_TO_LEVEL[$player->getRank()];
	}

	/** @return AtPlayer[] */
	public function getOnlineStaff(bool $all = true): array {
		$staff = [];
		foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
			/** @var AtPlayer $player */
			if ($player->isLoaded() && $player->isStaff()) {
				if ($all || $player->getSession()->getStaff()->inAnticheat())
					$staff[] = $player;
			}
		}
		return $staff;
	}

	public function ban($banned, $by, string $reason, int $until = -1): void {
		if ($banned instanceof Player || $banned instanceof User) {
			$banned_lb = (int) $banned->getXuid();
		} else {
			$banned_lb = $banned;
		}

		if ($by instanceof Player || $by instanceof User) {
			$by_lb = (int) $by->getXuid();
		} else {
			$by_lb = $by;
		}

		$when = time();
		$identifier = Core::getInstance()->getNetwork()->getIdentifier();
		$until = ($until == -1 ? -1 : time() + $until);
		$length = ($until == -1 ? "ETERNITY" : floor(($until - time()) / 86400) . " days");

		Core::getInstance()->getUserPool()->useUsers([$banned_lb, $by_lb], function (array $users) use ($banned_lb, $by_lb, $when, $reason, $identifier, $until, $length): void {
			/** @var User $banned */
			$banned = $users[$banned_lb];
			/** @var User $by */
			$by = $users[$by_lb];

			if (!$banned->valid()) {
				if (preg_match('/^(?:\d{1,3}\.){3}\d{1,3}$/', $address = $banned_lb)) {
					($ban = new BanEntry(
						$banned_lb,
						$by->getXuid(),
						$reason,
						$identifier,
						$when,
						$until,
						false,
						BanEntry::TYPE_IP
					))->save();
					Core::getInstance()->getSessionManager()->sendStrayRequest(new MySqlRequest(
						"users_relating_to_address_" . $address,
						new MySqlQuery("main", "SELECT gamertag FROM network_playerdata WHERE lastaddress=?", [$address])
					), function (MySqlRequest $request) use ($address, $by, $reason, $length, $until, $identifier): void {
						$list = (array) $request->getQuery()->getResult()->getRows();
						$accounts = "";
						foreach ($list as $l) {
							$accounts .= $l["gamertag"] . ", ";
						}
						$post = new Post("", "Ban Log - " . Core::getInstance()->getNetwork()->getIdentifier(), "[REDACTED]", false, "", [
							new Embed("", "rich", "IP address has been banned!", "", "ffb106", new Footer("Please reply to this message with evidence!"), "", "[REDACTED]", null, [
								new Field("Associated accounts", $accounts, true),
								new Field("Banned by", $by->getGamertag(), true),
								new Field("Timestamp", date("F j, Y, g:ia", time()), true),
								new Field("Reason", $reason, true),
								new Field("Length", $length, true),
							])
						]);
						$post->setWebhook(Webhook::getWebhookByName("ban-log"));
						$post->send();

						$matching = 0;
						foreach (Server::getInstance()->getOnlinePlayers() as $player) {
							if ($player->isConnected() && $player->getNetworkSession()->getIp() == $address) {
								$matching++;
								$player->kick(TextFormat::RED . "Your IP address was banned by " . $by->getGamertag() . " for: '" . $reason . "'. Appeal for an unban on Discord! avengetech.net/discord", false);
							}
						}
						if ($matching == 0) {
							$pk = new StaffBanIpPacket([
								"ip" => $address,
								"by" => $by->getGamertag(),
								"length" => $until,
								"reason" => $reason
							]);
							$pk->queue();
						}
					});
				} else {
					($ban = new BanEntry(
						$banned_lb,
						$by->getXuid(),
						$reason,
						$identifier,
						$when,
						$until,
						false,
						BanEntry::TYPE_DEVICE
					))->save();
					Core::getInstance()->getSessionManager()->sendStrayRequest(new MySqlRequest(
						"users_relating_to_dev_" . ($deviceId = $banned_lb),
						new MySqlQuery("main", "SELECT gamertag FROM network_playerdata WHERE deviceid=?", [$deviceId])
					), function (MySqlRequest $request) use ($deviceId, $by, $reason, $length, $until, $identifier): void {
						$list = (array) $request->getQuery()->getResult()->getRows();
						$accounts = "";
						foreach ($list as $l) {
							$accounts .= $l["gamertag"] . ", ";
						}
						$post = new Post("", "Ban Log - " . Core::getInstance()->getNetwork()->getIdentifier(), "[REDACTED]", false, "", [
							new Embed("", "rich", "Device ID has been banned!", "", "ffb106", new Footer("Please reply to this message with evidence!"), "", "[REDACTED]", null, [
								new Field("Associated accounts", $accounts, true),
								new Field("Banned by", $by->getGamertag(), true),
								new Field("Timestamp", date("F j, Y, g:ia", time()), true),
								new Field("Reason", $reason, true),
								new Field("Length", $length, true),
							])
						]);
						$post->setWebhook(Webhook::getWebhookByName("ban-log"));
						$post->send();

						$matching = 0;
						foreach (Server::getInstance()->getOnlinePlayers() as $player) {
							/** @var AtPlayer $player */
							if ($player->clientId == $deviceId) {
								$matching++;
								$player->kick(TextFormat::RED . "You were banned by " . $by->getGamertag() . " for: '" . $reason . "'. Appeal for an unban on Discord! avengetech.net/discord", false);
							}
						}
						if ($matching == 0) {
							$pk = new StaffBanDevicePacket([
								"did" => $deviceId,
								"by" => $by->getGamertag(),
								"length" => $until,
								"reason" => $reason
							]);
							$pk->queue();
						}
					});
				}
				$this->loadBans($banned_lb, function (BanManager $bm) use ($ban): void {
					$bm->addBan($ban);
				});
				return;
			}

			($ban = new BanEntry(
				$banned->getXuid(),
				$by->getXuid(),
				$reason,
				$identifier,
				$when,
				$until,
				false,
				BanEntry::TYPE_REGULAR
			))->save();

			Core::getInstance()->getSessionManager()->useSession($banned, function (CoreSession $session) use ($ban): void {
				$session->getStaff()->getBanManager()->addBan($ban);
			});

			$post = new Post("", "Ban Log - " . Core::getInstance()->getNetwork()->getIdentifier(), "[REDACTED]", false, "", [
				new Embed("", "rich", "**" . TextFormat::clean($banned->getGamertag()) . "** has been banned!", "", "ffb106", new Footer("Please reply to this message with evidence! (new method)"), "", "[REDACTED]", null, [
					new Field("Banned by", TextFormat::clean($by->getGamertag()), true),
					new Field("Timestamp", date("F j, Y, g:ia", time()), true),
					new Field("** **", "** **", true),
					new Field("Reason", $reason, true),
					new Field("Length", $length, true),
				])
			]);
			$post->setWebhook(Webhook::getWebhookByName("ban-log"));
			$post->send();

			if ($banned->validPlayer()) {
				$banned->getPlayer()->kickPlayer(
					$by->getGamertag(),
					TextFormat::RED . "You were banned!" . PHP_EOL .
						TextFormat::RED . "Reason: " . TextFormat::YELLOW . "'" . $reason . "'" . PHP_EOL .
						TextFormat::RED . "Length: " . TextFormat::YELLOW . $length . PHP_EOL .
						TextFormat::RED . "Appeal for an unban at " . TextFormat::YELLOW . "avengetech.net/discord",
					true
				);
			} else {
				$pk = new StaffBanPacket([
					"player" => $banned->getGamertag(),
					"by" => $by->getGamertag(),
					"length" => $until,
					"reason" => $reason
				]);
				$pk->queue();
			}

			foreach ($this->getOnlineStaff() as $staff) {
				$staff->sendMessage(TextFormat::ITALIC . TextFormat::GRAY . "[BAN LOG] " . $by->getGamertag() . " just banned " . $banned->getGamertag() . "! Reason: " . $reason);
			}
		});
	}

	public function unban($banned, ?User $moderator = null): void {
		if ($banned instanceof Player || $banned instanceof User) $banned = $banned->getXuid();
		Core::getInstance()->getUserPool()->useUser($banned, function (User $user) use ($moderator): void {
			if (!$user->valid()) {
				// TODO: Device & IP Bans
				return;
			}

			Core::getInstance()->getSessionManager()->useSession($user, function (CoreSession $session) use ($moderator): void {
				$ban = $session->getStaff()->getBanManager()->getRecentBan();
				if (!is_null($ban) && !$ban->isRevoked()) $ban->revoke($moderator);
			});
		});
	}

	public function loadBans(mixed $id, \Closure $closure): void {
		if ($id instanceof Player || $id instanceof User) {
			$id = $id->getXuid();
		}
		Core::getInstance()->getUserPool()->useUser($id, function (User $user) use ($id, $closure) {
			if ($user->valid()) {
				Core::getInstance()->getSessionManager()->useSession($user, function (CoreSession $session) use ($closure) {
					$closure($session->getStaff()->getBanManager());
				});
			} else {
				Core::getInstance()->getSessionManager()->sendStrayRequest(
					new MySqlRequest("load_ban_" . $id, new MySqlQuery("main", "SELECT * FROM bans WHERE id=?", [$id])),
					function (MySqlRequest $request) use ($id, $closure): void {
						$bm = new BanManager(new User(intval($id), $id));
						$bans = $request->getQuery()->getResult();
						$rows = (array) $bans->getRows();
						foreach ($rows as $data) {
							$bm->addBan(new BanEntry($id, $data["by"], $data["when"], $data["reason"], $data["identifier"], $data["until"], $data["revoked"], $data["type"]));
						}
						$closure($bm);
					}
				);
			}
		});
	}

	public function mute($muted, $by, $reason, int $until = -1): void {
		if ($muted instanceof Player || $muted instanceof User) {
			$muted_lb = (int) $muted->getXuid();
		} else {
			$muted_lb = $muted;
		}

		if ($by instanceof Player || $by instanceof User) {
			$by_lb = (int) $by->getXuid();
		} else {
			$by_lb = $by;
		}

		$when = time();
		$identifier = Core::getInstance()->getNetwork()->getIdentifier();
		$until = ($until == -1 ? -1 : time() + $until);
		$length = ($until == -1 ? "ETERNITY" : floor(($until - time()) / 86400) . " days");

		Core::getInstance()->getUserPool()->useUsers([$muted_lb, $by_lb], function (array $users) use ($muted_lb, $by_lb, $when, $reason, $identifier, $until, $length): void {
			/** @var User $muted */
			$muted = $users[$muted_lb];
			/** @var User $by */
			$by = $users[$by_lb];

			if (!$muted->valid()) {
				return;
			}

			($mute = new MuteEntry(
				$muted,
				$by->getXuid(),
				$reason,
				$identifier,
				$when,
				$until
			))->save();

			$post = new Post("", "Mute Log - " . Core::getInstance()->getNetwork()->getIdentifier(), "[REDACTED]", false, "", [
				new Embed("", "rich", "**" . $muted->getGamertag() . "** has been muted!", "", "ffb106", new Footer("Please reply to this message with evidence! (new method)"), "", "[REDACTED]", null, [
					new Field("Muted by", $by->getGamertag(), true),
					new Field("Timestamp", date("F j, Y, g:ia", time()), true),
					new Field("Reason", $reason, true),
					new Field("Length", $length, true),
				])
			]);
			$post->setWebhook(Webhook::getWebhookByName("mute-log"));
			$post->send();

			Core::getInstance()->getSessionManager()->useSession($muted, function (CoreSession $session) use ($mute): void {
				$session->getStaff()->getMuteManager()->addMute($mute);
			});

			if ($muted->validPlayer()) {
				($pl = $muted->getPlayer())->sendMessage(TextFormat::RI . "You have been muted by " . TextFormat::YELLOW . $by->getGamertag() . TextFormat::GRAY . "! Reason: " . TextFormat::AQUA . "'" . $reason . "'" . TextFormat::GRAY . " - Length: " . $length);
			} else {
				$inbox = new InboxInstance($muted);
				$msg = new MessageInstance($inbox, MessageInstance::newId(), time(), 0, "You were muted!", "You have been muted by " . TextFormat::YELLOW . $by->getGamertag() . TextFormat::WHITE . "!" . PHP_EOL . "Reason: " . TextFormat::WHITE . $reason . PHP_EOL . "Length: " . $length, false);
				$inbox->addMessage($msg, true);

				$pk = new StaffMutePacket([
					"player" => $muted->getGamertag(),
					"by" => $by->getXuid(),
					"length" => $until,
					"reason" => $reason,
					"when" => $when,
					"identifier" => $identifier
				]);
				$pk->queue();
			}

			foreach ($this->getOnlineStaff() as $staff) {
				$staff->sendMessage(TextFormat::ITALIC . TextFormat::GRAY . "[MUTE LOG] " . $by->getGamertag() . " just muted " . $muted->getGamertag() . "! Reason: " . $reason);
			}
		});
	}

	public function unmute($muted, User $moderator): void {
		Core::getInstance()->getUserPool()->useUser($muted, function (User $user) use ($moderator): void {
			if ($user->valid()) {
				Core::getInstance()->getSessionManager()->useSession($user, function (CoreSession $session) use ($moderator): void {
					$mute = $session->getStaff()->getMuteManager()->getRecentMute();
					if (!is_null($mute) && !$mute->isRevoked()) {
						$mute->revoke($moderator);
					}
				});
			}
		});
	}

	public function loadMutes($player, \Closure $closure): void {
		if ($player instanceof Player || $player instanceof User) {
			$player = $player->getXuid();
		}
		Core::getInstance()->getUserPool()->useUser($player, function (User $user) use ($closure): void {
			if ($user->valid()) {
				Core::getInstance()->getSessionManager()->useSession($user, function (CoreSession $session) use ($closure): void {
					$closure($session->getStaff()->getMuteManager());
				});
			} else {
				$closure(new MuteManager($user));
			}
		});
	}

	public function warn($warned, $by, string $reason = "", ?string $identifier = null, int $type = WarnEntry::TYPE_CHAT, bool $severe = false, bool $silent = false): void {
		if ($warned instanceof Player || $warned instanceof User) {
			$warned_lb = (int) $warned->getXuid();
		} else {
			$warned_lb = $warned;
		}

		if ($by instanceof Player || $by instanceof User) {
			$by_lb = (int) $by->getXuid();
		} else {
			$by_lb = $by;
		}

		$when = time();
		$identifier = $identifier ?? Core::getInstance()->getNetwork()->getIdentifier();

		Core::getInstance()->getUserPool()->useUsers([$warned_lb, $by_lb], function (array $users) use ($warned_lb, $by_lb, $when, $reason, $identifier, $type, $severe, $silent): void {
			/** @var User $warned */
			$warned = $users[$warned_lb];
			/** @var User $by */
			$by = $users[$by_lb];

			($warn = new WarnEntry(
				$warned,
				$by->getXuid(),
				$reason,
				$identifier,
				$when,
				$type,
				$severe
			))->save();

			Core::getInstance()->getSessionManager()->useSession($warned, function (CoreSession $session) use ($warned, $by, $warn, $identifier, $reason, $type, $severe, $silent): void {
				$warnManager = $session->getStaff()->getWarnManager();
				$post = new Post("", "Warn Log - " . $identifier, "[REDACTED]", false, "", [
					new Embed("", "rich", "**" . $warned->getGamertag() . "** has been warned!", "", "ffb106", new Footer("Please reply to this message with evidence!"), "", "[REDACTED]", null, [
						new Field("Warned by", $by->getGamertag(), true),
						new Field("Timestamp", date("F j, Y, g:ia", $warn->getWhen()), true),
						new Field("Type", $type === WarnEntry::TYPE_CHAT ? "chat" : "misc", true),
						new Field("Reason", $reason, true),
						new Field("Severe", $severe === 1 ? "YES" : "NO", true),
						new Field("Total warns", count($warnManager->getWarns()) + 1, true),
					])
				]);
				$post->setWebhook(Webhook::getWebhookByName("warn-log"));
				$post->send();
				$warnManager->addWarn($warn);
				$warnManager->checkNeedsMute($by, $severe, $silent);
			});

			if ($warned->validPlayer() && ($pl = $warned->getPlayer())->isLoaded()) {
				$pl->sendMessage(TextFormat::RI . "You have been warned by " . TextFormat::YELLOW . $by->getGamertag() . TextFormat::GRAY . "! Reason: " . TextFormat::AQUA . $reason);
			} else {
				$inbox = new InboxInstance($warned);
				$msg = new MessageInstance($inbox, MessageInstance::newId(), time(), 0, "Staff warning!", "You have been warned by " . TextFormat::YELLOW . $by->getGamertag() . TextFormat::WHITE . " on " . TextFormat::AQUA . $identifier . TextFormat::WHITE . "! Reason: " . TextFormat::AQUA . $reason, false);
				$inbox->addMessage($msg, true);

				$pk = new StaffWarnPacket([
					"player" => $warned->getGamertag(),
					"by" => $by->getXuid(),
					"reason" => $reason,
					"identifier" => $identifier,
					"when" => $when
				]);
				$pk->queue();
			}

			foreach ($this->getOnlineStaff() as $staff) {
				$staff->sendMessage(TextFormat::ITALIC . TextFormat::GRAY . "[WARNING LOG] " . $by->getGamertag() . " just warned " . $warned->getGamertag() . "! Reason: " . $reason);
			}
		});
	}

	public function loadWarnings(mixed $player, \Closure $closure): void {
		if ($player instanceof Player || $player instanceof User) {
			$player = $player->getXuid();
		}
		Core::getInstance()->getUserPool()->useUser($player, function (User $user) use ($closure): void {
			if ($user->valid()) {
				Core::getInstance()->getSessionManager()->useSession(
					$user,
					function (CoreSession $session) use ($closure): void {
						$closure($session->getStaff()->getWarnManager());
					}
				);
			} else {
				$closure(new WarnManager($user));
			}
		});
	}
}
