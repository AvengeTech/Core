<?php

namespace core\network;

use pocketmine\scheduler\ClosureTask;

use core\Core;
use core\discord\objects\{
	Post,
};
use core\network\commands\{
	AddUptime,
	Transfer,
	TransferAll,
	Lobby as LobbyCommand,
	Server,
	Reconnect,
	Ping,
	Alias,
	Downtime,
	Whitelist,
	Online,
	Reply,
	RestartGamemodes,
	Xuid,
	Queue,
	Toast,
	Summon,
	LoadTest,
	SubUpdateTest,
	FormTest,
	VarDumpEntity,
	WhoIs,
	ListSubServers,
	Network as CommandsNetwork
};
use core\network\commands\override\{
    GiveCommand,
    Stop,
	ListCommand,
	TellCommand
};
use core\network\server\{
	ServerManager,
	ServerInstance
};
use core\network\protocol\ServerSetStatusPacket;
use core\utils\TextFormat;

use lobby\Lobby;
use pocketmine\utils\SingletonTrait;
use skyblock\SkyBlock;
use prison\Prison;
use pvp\PvP;
use prison\PrisonPlayer;

class Network {
	use SingletonTrait;

	public EventListener $eventListener;

	public ServerManager $serverManager;

	public bool $sdScheduled = false;
	public bool $shutdown = false;
	public bool $shutdownDone = false;
	public int $timeSetup = 0;

	public int $ticks = 0;

	public bool $distanceChanged = false;
	public int $distance = 15;

	public function __construct(public Core $plugin) {
		self::$instance = $this;
		
		$this->serverManager = new ServerManager();
		$this->timeSetup = time();

		$plugin->getServer()->getPluginManager()->registerEvents(($this->eventListener = new EventListener($this->plugin, $this)), $this->plugin);

		$cmdMap = $plugin->getServer()->getCommandMap();
		foreach ($cmdMap->getCommands() as $cmd) {
			if (in_array($cmd->getName(), ["tell", "whitelist", "stop", "list"])) {
				$cmdMap->unregister($cmd);
				break;
			}
		}
		$cmdMap->registerAll("network", [
			new AddUptime($plugin, "adduptime", "Add server uptime"),
			new Downtime($plugin, "downtime", "Manage network downtime (tier 3)"),
			new Transfer($plugin, "transfer", "Transfer to a server in the network."),
			new TransferAll($plugin, "transferall", "Transfer all players to a server in the network."),
			new LobbyCommand($plugin, "lobby", "Return to the lobby."),
			new CommandsNetwork($plugin, "network", "Show the status of servers on the network"),
			new Server($plugin, "server", "See what server you are connected to."),
			new Reconnect($plugin, "reconnect", "Reconnect to your current server."),
			new Ping($plugin, "ping", "Check your ping connection to the server"),
			new Alias($plugin, "alias", "Check relating accounts (Staff)"),
			new Whitelist($plugin, "whitelist", "Add players to server whitelist (Staff)"),
			new Online($plugin, "online", "Check if a player is currently connected to AvengeTech"),
			new Reply($plugin, "reply", "Reply to the last player that messaged you"),
			new RestartGamemodes($plugin, "restartgamemodes", "Restart gamemode servers (tier 3)"),
			new Xuid($plugin, "xuid", "Bean, potato, cheese"),
			new Queue($plugin, "queue", "Add yourself to queue"),
			new Summon($plugin, "summon", "Summon player to this server (tier 3)"),
			new Toast($plugin, "toast", "TOAST!!!!"),
			new LoadTest($plugin, "loadtest", "Test load player data (new sessions)"),
			new SubUpdateTest($plugin, "su", "Test sub update data (new subservers)"),
			new FormTest($plugin, "ft", "form testing"),
			new VarDumpEntity($plugin, "vardumpentity", "ENTITY TEST SHIT"),
			new WhoIs($plugin, "whois", "See who a player is based on a nickname"),
			new ListSubServers($plugin, "listsubservers", "List subservers"),

			new Stop($plugin, "stop", "Stop the server"),
			new ListCommand($plugin, "list", "List players online!"),
			new TellCommand($plugin, "tell", "Message players"),
			new GiveCommand
		]);

		$plugin->getServer()->getNetwork()->setName(TextFormat::BOLD . TextFormat::AQUA . "Avenge" . TextFormat::GOLD . "Tech" . TextFormat::RESET . TextFormat::GRAY . " - " . $this->getServerManager()->getThisServer()->getId());
	}

	public function getServerManager(): ?ServerManager {
		return $this->serverManager;
	}

	public function tick(): void {
		$this->ticks++;
		if ($this->ticks % 10 === 0) {
			$this->calculateViewDistance();
			if ($this->distanceChanged) {
				$this->distanceChanged = false;
				foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
					$player->setViewDistance($this->distance);
				}
			}
		} //todo: reimplement per server method?

		($sm = $this->getServerManager())->tick();
		$ts = $sm->getThisServer();

		if ($this->ticks % 20 == 0) {
			$left = $this->getRestartTime() - time();
			if ($left <= 10) {
				foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
					$player->removeCurrentWindow();
					$player->sendMessage(TextFormat::RI . TextFormat::YELLOW . "Server restarting in " . ($left - 5) . " seconds..");
				}
			}
			if ($left <= 5) {
				if (!$this->sdScheduled) {
					$this->scheduleShutdown();

					switch ($ts->getType()) {
						case "lobby":
							($ssm = Lobby::getInstance()->getSessionManager())->saveAll();
							$ssm->saveOnLeave = false;
							break;

						case "skyblock":
							SkyBlock::getInstance()->getCombat()->removeLogs();
							SkyBlock::getInstance()->getGames()->close();
							SkyBlock::getInstance()->getTrade()->close();
							SkyBlock::getInstance()->getIslands()->getIslandManager()->saveAll();
							SkyBlock::getInstance()->getAuctionHouse()->getAuctionManager()->save();

							($ssm = SkyBlock::getInstance()->getSessionManager())->saveAll();
							$ssm->saveOnLeave = false;
							break;
						case "prison":
							Prison::getInstance()->getCombat()->close();
							Prison::getInstance()->getTrade()->close();
							Prison::getInstance()->getAuctionHouse()->getAuctionManager()->save();
							foreach (Prison::getInstance()->getBlockTournament()->getGameManager()->getActiveGames() as $game) {
								$game->end(true);
							}
							Prison::getInstance()->getGangs()->getGangManager()->getBattleManager()->cancelAllBattles("Server restarting!");

							($ssm = Prison::getInstance()->getSessionManager())->saveAll();
							$ssm->saveOnLeave = false;
							break;
						case "pvp":
							($ssm = PvP::getInstance()->getSessionManager())->saveAll();
							$ssm->saveOnLeave = false;
							break;
					}
					($ssm = $this->plugin->getSessionManager())->saveAll();
					$ssm->saveOnLeave = false;
					$this->plugin->getNetwork()->getServerManager()->getThisServer()->reconnectAll();
				}
			}
		}
	}

	public function close(): void {
		$this->getServerManager()->close();
	}

	public function getProxy(): int {
		return Structure::PROXY;
	}

	public function getMainAddress(): string {
		return Links::DEFAULT_IP;
	}

	public function getPlainAddress(): string {
		return Links::PLAIN_IP;
	}

	public function getServerTypes(): array {
		return Structure::SERVER_TYPES;
	}

	public static function validIdentifier(string $identifier): bool {
		return isset(Structure::SOCKET_PORTS[$identifier]);
	}

	protected static ServerInstance $thisServer;

	//IDENTIFYING SERVERS
	public function getThisServer(): ServerInstance {
		return (self::$thisServer ??= $this->getServerManager()->getThisServer());
	}

	public function getIdentifier(): string {
		return $this->getThisServer()->getIdentifier();
	}

	public function getServerType(): string {
		return $this->getThisServer()->getType();
	}

	public function getServerId(): int {
		return $this->getThisServer()->getTypeId();
	}

	public function getPortByIdentifier(string $identifier): int {
		foreach (Structure::PORT_TO_IDENTIFIER as $port => $id) {
			if ($identifier == $id) return $port;
		}
		return 0;
	}

	public function getRestartTime(): int {
		return $this->timeSetup + ((Structure::RESTART_TIMES[$this->getIdentifier()] ?? Structure::RESTART_TIMES[$this->getServerType()]) * 60);
	}

	public function getUptime(): int {
		return time() - $this->timeSetup;
	}

	public function aboutToRestart(): bool {
		return $this->getRestartTime() - time() <= 10;
	}

	public function getServerTypeByIdentifier(string $identifier): string {
		return explode("-", $identifier)[0];
	}

	public function getCaseName(string $type): string {
		return Structure::TYPE_TO_CASE[strtolower($type)];
	}

	public function calculateViewDistance(): void {
		$count = count($this->plugin->getServer()->getOnlinePlayers());
		$distance = 16;
		if ($count <= 80) {
			$distance = 16;
		} elseif ($count <= 90) {
			$distance = 8;
		} elseif ($count <= 110) {
			$distance = 4;
		} else {
			$distance = 3;
		}
		if ($distance !== $this->distance) {
			$this->distanceChanged = true;
			$this->distance = $distance;
		}
	}

	public function shutdownScheduled(): bool {
		return $this->sdScheduled;
	}

	public function isShuttingDown(): bool {
		return $this->shutdown;
	}

	public function scheduleShutdown(int $seconds = 3): void {
		if ($this->shutdownScheduled()) return;
		$this->sdScheduled = true;

		$pk = new ServerSetStatusPacket([
			"online" => false,
		]);
		$pk->queue();

		$this->plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function (): void {
			$this->shutdown();
		}), $seconds * 20);
	}

	public function shutdown(): void {
		if ($this->shutdown) return;
		$this->shutdown = true;

		$post = new Post("Server is shutting down!", "Network - " . $this->getIdentifier());
		$post->setWebhook($this->plugin->getDiscord()->getConsoleWebhook());
		$post->send();

		$plugins = ["Lobby", "Prison", "SkyBlock", "PvP", "Creative", "Build"]; //Add more, they disable BEFORE this.
		$manager = $this->plugin->getServer()->getPluginManager();
		foreach ($plugins as $name) {
			$plugin = $manager->getPlugin($name);
			if ($plugin != null) {
				if ($name === "Prison") {
					foreach (PrisonPlayer::PLOT_WORLDS as $worldname) {
						$world = $this->plugin->getServer()->getWorldManager()->getWorldByName($worldname);
						if ($world !== null) {
							$world->save();
						}
					}
				}
			}
		}

		$this->close();

		$this->plugin->getServer()->shutdown();
	}
}
