<?php

namespace core;

use pocketmine\Server;
use pocketmine\network\mcpe\protocol\ToastRequestPacket;
use pocketmine\permission\{
	DefaultPermissions,
	PermissionManager
};
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\AsyncPool;
use pocketmine\world\generator\{
	GeneratorManager,
	InvalidGeneratorOptionsException
};

use core\AtPlayer as Player;
use core\{
	announce\Announce,
	chat\Chat,
	cosmetics\Cosmetics,
	discord\Discord,
	entities\Entities,
	etc\Etc,
	gadgets\Gadgets,
	inbox\Inbox,
	lootboxes\LootBoxes,
	rank\Rank,
	rules\Rules,
	scoreboards\Scoreboards,
	staff\Staff,
	techie\Techie,
	tutorial\Tutorials,
	vote\Vote
};
use core\announce\commands\AnnounceCommand;
use core\command\FixIssueCommand;
use core\network\{
	Network,
	Structure as NS,
	protocol\PlayerSessionSavedPacket,
	server\ServerInstance
};
use core\session\{
	SessionManager,
	CoreSession
};
use core\settings\{
	GlobalSettings,
	command\SettingsCommand
};
use core\utils\{
	command\FlyCommand,
	command\ProfileCommand,
	command\SpawnIcon,
	command\TestNewForm,

	gpt\GptQueue,
	gpt\Model,

	AsyncIterator,
	BlockRegistry,
	CraftingRegistry,
	DiscordErrorConsoleAttachment,
    EntityRegistry,
    ItemRegistry,
	RegistryAsyncTask,
	LoadAction,
	TextFormat,
	TileRegistry,
	VoidGenerator,
	VpnCache
};
use core\user\{
	User,
	UserPool
};
use core\session\mysqli\data\{
	MySqlQuery,
	MySqlRequest
};
use core\network\protocol\ServerSubUpdatePacket;
use core\network\server\SubServer;
use core\staff\anticheat\AntiCheat;
use core\utils\command\TrimCommand;
use faction\Faction;
use faction\player\{
	FactionPlayer,
	FactionSession
};
use lobby\{
	Lobby,
	LobbySession
};
use pocketmine\inventory\CreativeInventory;
use pocketmine\item\Item;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use prison\{
	Prison,
	PrisonPlayer,
	PrisonSession
};
use skyblock\{
	SkyBlock,
	SkyBlockPlayer,
	SkyBlockSession
};
use pvp\{
	PvP,
	PvPPlayer,
	PvPSession
};

class Core extends PluginBase {

	const LINKS = [
		"server" => "play.avengetech.net",
		"store" => "store.avengetech.net",
		"discord" => "avengetech.net/discord",
		"vote" => "avengetech.net/vote",
	];

	public static ?Core $instance = null;
	public \mysqli $database;
	public static $iterator = null;

	public DiscordErrorConsoleAttachment $loggerAttachment;

	public string $dir = "/[REDACTED]";

	public Network $network;

	public Announce $announce;
	public Chat $chat;
	public Cosmetics $cosmetics;
	public Discord $discord;
	public Entities $entities;
	public Etc $etc;
	public Gadgets $gadgets;
	public Inbox $inbox;
	public LootBoxes $lootBoxes;
	public Rank $rank;
	public Rules $rules;
	public Scoreboards $scoreboards;
	public Staff $staff;
	public Techie $techie;
	public Tutorials $tutorials;
	public Vote $vote;

	public VpnCache $vpnCache;
	public GptQueue $gptQueue;

	public SessionManager $sessionManager;
	public UserPool $userPool;

	public $asyncPool;

	public User $sn3ak;

	public array $loadActions = [];

	public static array $dataTransfers = [];

	public function onLoad(): void {
		if (!defined(PHP_EOL)) define(PHP_EOL, '\n');
	}

	public function onEnable(): void {
		$this->loggerAttachment = new DiscordErrorConsoleAttachment();
		$this->getServer()->getLogger()->addAttachment($this->loggerAttachment);
		
		self::$instance = $this;

		$cm = $this->getServer()->getCommandMap();
		foreach (
			[
				"kill",
				"list",
				"me",
				"kick",
				"ban",
				"ban-ip",
				"pardon",
				"pardon-ip",
				"stop",
				"whitelist",
				"give",
			] as $cmd
		) $cm->unregister($cm->getCommand($cmd));

		($pm = PermissionManager::getInstance())
			->getPermission(DefaultPermissions::ROOT_CONSOLE)->addChild(
				$pm->getPermission("core.tier3")->getName(),
				true
			);

		date_default_timezone_set("America/New_York");

		$creds = array_merge(file($this->dir . "[REDACTED]"), ["core"]);
		foreach ($creds as $key => $cred) $creds[$key] = trim(str_replace("\n", "", $cred));
		try {
			$this->database = new \mysqli(...$creds);
			$this->database->query("SET SESSION wait_timeout=2147483");
			$this->database->query("CREATE TABLE IF NOT EXISTS chat_logs(xuid BIGINT(16) NOT NULL, timestamp INT NOT NULL, message VARCHAR(256) NOT NULL)");
		} catch (\Exception $e) {
			$this->getLogger()->error("Database connection failed! Error: " . $e->getMessage());
			$this->getServer()->shutdown();
		}
		$this->getLogger()->notice("Successfully connected to database.");

		$iterator = new AsyncIterator();
		$iterator::init($this);
		self::$iterator = $iterator;

		GeneratorManager::getInstance()->addGenerator(VoidGenerator::class, "void", \Closure::fromCallable(function (string $preset): ?InvalidGeneratorOptionsException {
			if ($preset === "") {
				return null;
			}
			try {
				VoidGenerator::convertSeed($preset);
				return null;
			} catch (InvalidGeneratorOptionsException $e) {
				return $e;
			}
		}), true);

		$this->sessionManager = new SessionManager($this, CoreSession::class, "core");
		$this->asyncPool = new AsyncPool(4, 512, $this->getServer()->getLoader(), $this->getServer()->getLogger(), $this->getServer()->getTickSleeper());

		$this->network = new Network($this);

		new AntiCheat;

		$pool = Server::getInstance()->getAsyncPool();
		$serverType = $this->getNetwork()->getServerManager()->getThisServer()->getType();

		TileRegistry::setup($serverType);
		BlockRegistry::setup($serverType);
		ItemRegistry::setup($serverType);
		CraftingRegistry::setup($serverType);
		EntityRegistry::setup($serverType);

		$pool->addWorkerStartHook(function(int $worker) use($pool, $serverType) : void{
			$pool->submitTaskToWorker(new RegistryAsyncTask($serverType), $worker);
		});

		foreach (CreativeInventory::getInstance()->getAll() as $item) {
			TypeConverter::getInstance()->coreItemStackToNet($item);
		}

		$this->announce = new Announce($this);
		$this->chat = new Chat($this);
		$this->cosmetics = new Cosmetics($this);
		$this->discord = new Discord($this);
		$this->entities = new Entities($this);
		$this->getEntities()->getCustomEntityRegistry()->registerEntities([
			"core:lootbox",
			"core:question",
			"core:trophy",

			"core:gadget.cake",
			"core:gadget.sailing_sub",
		]);
		$this->etc = new Etc($this);
		$this->gadgets = new Gadgets($this);
		$this->inbox = new Inbox($this);
		$this->lootBoxes = new LootBoxes($this);
		$this->rank = new Rank($this);
		$this->rules = new Rules($this);
		$this->staff = new Staff($this);
		$this->techie = new Techie($this);
		$this->tutorials = new Tutorials($this);
		$this->vote = new Vote($this);

		$this->scoreboards = new Scoreboards($this);

		$this->vpnCache = new VpnCache();

		$this->gptQueue = new GptQueue(new Model(
			Model::MODEL_35_TURBO,
			Model::ENDPOINT_CHAT
			//Model::MODEL_DAVINCI_002,
			//Model::ENDPOINT_OTHER
		));
		//$this->getGptQueue()->quickRequest("What's 9+10", function(GptRequest $request) : void{
		//	$response = $request->getResponse();
		//	$choices = $response->getChoices();
		//	foreach($choices as $choice){
		//		var_dump($choice->getMessage()->getContent());
		//	}
		//});

		$this->getServer()->getPluginManager()->registerEvents(new MainListener($this), $this);
		$this->getScheduler()->scheduleRepeatingTask(new MainTask($this), 1);

		$this->userPool = new UserPool();

		$this->sn3ak = new User(0, "sn3akrr");

		$cm->registerAll("utils", [
			new FlyCommand($this, "fly", "Toggle flight mode"),
			new ProfileCommand($this, "profile", "View a player profile"),
			new TestNewForm($this, "form", "Test new forms"),
			new SettingsCommand($this, "settings", "Manage your server settings"),
			new SpawnIcon($this, "icon", "Spawns AvengeTech icon"),
			new TrimCommand($this, "trim", "Trim an armor piece"),
		]);
		$cm->registerAll("core", [
			new FixIssueCommand,
			new AnnounceCommand($this, "announcements", "View announcement board!")
		]);


		$this->getNetwork()->getServerManager()->addSubUpdateHandler(function(string $server, string $type, array $data) : void{
			switch($type){
				case "msg":
					$msg = $data["msg"];
					$sound = $data["sound"] ?? "";
					Server::getInstance()->broadcastMessage($msg);
					if($sound !== ""){
						foreach (Server::getInstance()->getOnlinePlayers() as $player) {
							/** @var AtPlayer $player */
							$player->playSound($sound);
						}
					}
					break;

				case "vote":
					$tt = $data["type"] ?? "ok";
					switch($tt){
						case "count":
							Core::getInstance()->getVote()->addVoteCount(false);
							break;
						case "party":
							$status = $data["status"];
							$count = $data["count"];
							Core::getInstance()->getVote()->setPartyStatus($status, false);
							Core::getInstance()->getVote()->setVoteCount($count);
							break;

						case "sync":
							//echo "sending vote sync to " . $server, PHP_EOL;
							(new ServerSubUpdatePacket([
								"server" => $server,
								"type" => "vote",
								"data" => [
									"type" => "retrieve",
									"count" => Core::getInstance()->getVote()->getVoteCount()
								]
							]))->queue();
							break;
						case "retrieve":
							if($data["count"] !== 0){
								Core::getInstance()->getVote()->setVoteCount($data["count"]);
								//echo "vote count synced! ", $data["count"], PHP_EOL;
							}
							break;
					}
					echo "vote sub update packet received", PHP_EOL;
					break;

				case "giveall":
					$item = Item::nbtDeserialize(json_decode($data["item"]));

					foreach(Server::getInstance()->getOnlinePlayers() as $online){
						if(!$online instanceof AtPlayer) continue;
						if(!$online->isLoaded()) continue;
			
						if($online->getInventory()->canAddItem($item)){
							$online->sendMessage(TextFormat::GRAY . "Everyone online has received " . TextFormat::GREEN . TextFormat::BOLD . "+" . $item->getCount() . " " . TextFormat::RESET . TextFormat::AQUA . $item->getName() . TextFormat::GRAY . "!");
							$online->getInventory()->addItem($item);
						}
					}
					break;
			}
		});
	}

	public function onDisable(): void {
		$this->getNetwork()->shutdown();
		$this->database->close();
		$this->getServer()->getLogger()->removeAttachment($this->loggerAttachment);

		$this->getSessionManager()?->close();
		$this->getAsyncPool()?->shutdown();
	}

	/**
	 * Announces a message to subservers
	 * @param string $msg
	 * @param ?string $sound
	 */
	public static function announceToSS(string $msg, string $sound = "") : void{
		Server::getInstance()->broadcastMessage($msg);
		if($sound !== ""){
			foreach (Server::getInstance()->getOnlinePlayers() as $player) {
				/** @var AtPlayer $player */
				$player->playSound($sound);
			}
		}
		foreach(Core::thisServer()->getSubServers(false, true) as $server){
			(new ServerSubUpdatePacket([
				"server" => $server->getIdentifier(),
				"type" => "msg",
				"data" => [
					"msg" => $msg,
					"sound" => $sound
				]
			]))->queue();
		}
	}

	public static function getInstance(): ?self {
		return self::$instance;
	}

	public static function getIterator(): AsyncIterator {
		return self::$iterator;
	}

	public function getAsyncPool(): ?AsyncPool {
		return $this->asyncPool;
	}

	public function getDatabase(): ?\mysqli {
		return $this->database;
	}

	public function getNetwork(): ?Network {
		if (!isset($this->network)) return Network::getInstance();
		return $this->network;
	}

	protected static ServerInstance $thisServer;

	public static function thisServer(): ServerInstance|SubServer {
		return (self::$thisServer ??= Core::getInstance()->getNetwork()->getServerManager()->getThisServer());
	}

	public function getAnnounce(): Announce {
		return $this->announce;
	}

	public function getChat(): Chat {
		return $this->chat;
	}

	public function getCosmetics(): Cosmetics {
		return $this->cosmetics;
	}

	public function getDiscord(): Discord {
		return $this->discord;
	}

	public function getEntities(): Entities {
		return $this->entities;
	}

	public function getEtc(): Etc {
		return $this->etc;
	}

	public function getGadgets(): Gadgets {
		return $this->gadgets;
	}

	public function getInbox(): Inbox {
		return $this->inbox;
	}

	public function getLootBoxes(): LootBoxes {
		return $this->lootBoxes;
	}

	public function getRank(): Rank {
		return $this->rank;
	}

	public function getRules(): Rules {
		return $this->rules;
	}

	public function getScoreboards(): Scoreboards {
		return $this->scoreboards;
	}

	public function getStaff(): Staff {
		return $this->staff;
	}

	public function getTechie(): Techie {
		return $this->techie;
	}

	public function getTutorials(): Tutorials {
		return $this->tutorials;
	}

	public function getVote(): Vote {
		return $this->vote;
	}

	public function getVpnCache(): VpnCache {
		return $this->vpnCache;
	}

	public function getGptQueue(): GptQueue {
		return $this->gptQueue;
	}

	public function tick(): void {
	}

	public function getSessionManager(): ?SessionManager {
		return $this->sessionManager;
	}

	public function getUserPool(): UserPool {
		return $this->userPool;
	}

	public function getSn3ak(): User {
		return $this->sn3ak;
	}

	public function getLoadActions(): array {
		return $this->loadActions;
	}

	/**
	 * Queue an action for player loading
	 * @param LoadAction $action
	 */
	public function addLoadAction(LoadAction $action): void {
		if (!isset($this->loadActions[$action->getName()]))
			$this->loadActions[$action->getName()] = [];

		$this->loadActions[$action->getName()][] = $action;
	}

	/**
	 * Shift a load action for a player
	 * @param Player $player
	 */
	public function shiftLoadAction(Player $player): array {
		$actions = [];
		if (isset($this->loadActions[$player->getName()])) {
			$actions = $this->loadActions[$player->getName()];
			unset($this->loadActions[$player->getName()]);
		}
		return $actions;
	}

	public function onJoin(Player $player): void {
		$player->setInvisible();

		$this->getStaff()->playerByEid[$player->getId()] = $player;

		//$player->setViewDistance($this->getNetwork()->distance);

		if (count(($loadAction = $this->shiftLoadAction($player))) > 0) {
			foreach ($loadAction as $la) {
				$player->addPreLoadAction($la);
				$player->addLoadAction($la);
			}
		}

		if ($this->getNetwork()->shutdownScheduled() || $this->getNetwork()->isShuttingDown()) return;

		$player->setWhenInfoLoaded(function (Player $player): void {
			$player->setUser($this->getUserPool()->addByPlayer($player));

			$ip = $player->isFromProxy() && NS::PROXY == NS::PROXY_PORTAL ? $player->getIp() : $player->getNetworkSession()->getIp();
			if (($vpnc = $this->getVpnCache())->entryExists($ip)) {
				if ($vpnc->getEntryValue($ip)) {
					$player->kick(TextFormat::RED . "You cannot connect to AvengeTech using a VPN service! Please disable your VPN and try again", false);
					return;
				}
			} else {
				$player->sendVpnCheck($ip);
			}

			if (
				!($ts = ($net = $this->getNetwork())->getServerManager()->getThisServer())->hasPendingConnection($player) &&
				!$ts->isBeingSummoned($player) &&
				$this->getServer()->getPort() != Core::getInstance()->getNetwork()->getServerManager()->getServerById("lobby-1")->getPort() &&
				!$player->isStaff() &&
				!$player->isTier3() &&
				!$player->isFromProxy()
			) {
				$player->kick(TextFormat::RED . "You must join from the main server port!");
				return;
			}

			$this->getNetwork()->getServerManager()->getThisServer()->onConnect($player);

			$player->setWhenSessionSaved(function (Player $player) use ($net): void {
				if (!$player->isConnected()) return;
				$player->sendTitle(TextFormat::AQUA . "Loading", TextFormat::GOLD . "Core session data...", 10, 30, 10);
				$this->getSessionManager()->loadSession($player, function (CoreSession $csession) use ($net): void {
					$player = $csession->getPlayer();
					if ($player instanceof Player) {
						if (($t = $csession->getInbox()->getTotalNewMessages()) > 0) {
							$player->sendMessage(TextFormat::YI . "You have " . TextFormat::AQUA . $t . TextFormat::GRAY . " new message! Type " . TextFormat::YELLOW . "/inbox " . TextFormat::GRAY . "to view them!");
						}

						$entry = $this->getVote()->getWinnerEntry($player);
						if ($entry !== null && !$entry->hasClaimed()) {
							$player->sendMessage(TextFormat::BOLD . TextFormat::YELLOW . "[" . TextFormat::OBFUSCATED . str_repeat(TextFormat::GOLD . "|" . TextFormat::RED . "|", 3) . TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . "] " . TextFormat::RESET . TextFormat::AQUA . "You won the vote prize drawing last month! Type " . TextFormat::YELLOW . "/winner " . TextFormat::AQUA . "to see what you won!");
						}

						if ($player->isStaff()) {
							$player->setVanished($csession->getSettings()->getSetting(GlobalSettings::VANISHED));
							if (!$csession->getSettings()->getSetting(GlobalSettings::ANTICHEAT_MESSAGES)) $csession->getStaff()->toggleAnticheat();
							if ($csession->getSettings()->getSetting(GlobalSettings::COMMAND_SEE)) $this->getStaff()->toggleSeeAll($player->getName());
							if ($csession->getSettings()->getSetting(GlobalSettings::TELL_SEE)) $this->getStaff()->toggleTellSeeAll($player->getName());
							if ($csession->getSettings()->getSetting(GlobalSettings::STAFFCHAT_JOIN)) $csession->getStaff()->toggleStaffChat();
						} else {
							foreach ($this->getServer()->getOnlinePlayers() as $pl) {
								/** @var AtPlayer $pl */
								if ($pl->isLoaded() && $pl->isVanished()) {
									$player->getNetworkSession()->onPlayerRemoved($pl);
								}
							}
						}

						$csession->getLoginCommands()->executeCommands();

						$net->eventListener->onJoin($player);

						$afterLoaded = function (Player $player) use ($csession): void {
							$player->setLoaded();

							$player->updateChatFormat();
							$player->updateNametagFormat();
							$player->updateNametag();
							$this->getEntities()->onJoin($player);

							$player->setInvisible(false);

							$from = explode("-", $player->getTransferredFrom());
							$from = ($from[0] ?? "") . "-" . ($from[1] ?? "");
							$tsi = explode("-", Core::thisServer()->getIdentifier());
							$tsi = $tsi[0] . "-" . $tsi[1];
							if (
								!$player->isStaff() && $player->getRank() !== "default" && $player->hasJoinMessage() &&
								$player->isFirstConnection()
							) {
								$this->getChat()->updateNametagFormat($player);
								foreach ($this->getServer()->getOnlinePlayers() as $p) {
									/** @var AtPlayer $p */
									if ($p !== $player) $p->sendJoinMessage(
										$this->getChat()->ntf[$player->getName()],
										($player->getSession()->getRank()->hasSub() && !$player->isStaff()) ? "warden" : $player->getRank(),
										TextFormat::AQUA . ">>> " . $this->getChat()->getNametagFormat($player) . TextFormat::RESET . TextFormat::GREEN . " joined the server!"
									);
								}
							}

							$this->getScoreboards()->onJoin($player);

							$player->sendTitle(TextFormat::ICON_AVENGETECH, TextFormat::AQUA . "Data loaded!", 5, 10, 5);
							if (
								$player->isFirstConnection() &&
								$csession->getSettings()->getSetting(GlobalSettings::ANNOUNCEMENT_BOARD) &&
								$player->getRankHierarchy() >= 6
							) {
								$this->getAnnounce()->onJoin($player);
							} else {
								if (($closure = $this->getAnnounce()->getAfterAnnouncementClosure()) !== null) {
									$closure($player);
								}
							}

							$ts = $this->getNetwork()->getServerManager()->getThisServer();
							if ($ts->isBeingSummoned($player)) {
								$ts->onSummon($player);
							}

							if($ts->getId() == "idle-1"){
								$player->setNoClientPredictions();
							}
						};

						$player->sendTitle(TextFormat::AQUA . "Loading", TextFormat::GOLD . "Game session data...", 10, 30, 10);
						$player->setWhenGameSessionSaved(function (Player $player) use ($afterLoaded, $csession): void {
							switch ($this->getNetwork()->getServerManager()->getThisServer()->getType()) {
								case "lobby":
									($lobby = Lobby::getInstance())->getSessionManager()->loadSession($player, function (LobbySession $session) use ($csession, $lobby, $afterLoaded): void {
										/** @var LobbyPlayer $player */
										$player = $session->getPlayer();
										if ($player instanceof Player) {
											/** @var LobbyPlayer $player */
											$player->setHotbar("spawn");
											$afterLoaded($player);
										}
									});
									break;
								case "prison":
									($prison = Prison::getInstance())->getSessionManager()->loadSession($player, function (PrisonSession $session) use ($csession, $prison, $afterLoaded): void {
										/** @var PrisonPlayer $player */
										$player = $session->getPlayer();
										if ($player instanceof Player) {
											/** @var PrisonPlayer $player */
											$prison->onJoin($player);
											$afterLoaded($player);
										}
									});
									break;
								case "skyblock":
									($skyblock = SkyBlock::getInstance())->getSessionManager()->loadSession($player, function (SkyBlockSession $session) use ($csession, $skyblock, $afterLoaded): void {
										/** @var SkyBlockPlayer $player */
										$player = $session->getPlayer();
										if ($player instanceof Player) {
											/** @var SkyBlockPlayer $player */
											$afterLoaded($player);
											$skyblock->onJoin($player);
										}
									});
									break;
								case "pvp":
									($pvp = PvP::getInstance())->getSessionManager()->loadSession($player, function (PvPSession $session) use ($csession, $pvp, $afterLoaded): void {
										/** @var PvPPlayer $player */
										$player = $session->getPlayer();
										if ($player instanceof Player) {
											/** @var PvPPlayer $player */
											$pvp->onJoin($player);
											$afterLoaded($player);
										}
									});
									break;
								case "faction":
									($faction = Faction::getInstance())->getSessionManager()->loadSession($player, function (FactionSession $session) use ($csession, $faction, $afterLoaded): void {
										/** @var FactionPlayer $player */
										$player = $session->getPlayer();
										if ($player instanceof Player) {
											/** @var FactionPlayer $player */
											$faction->onJoin($player);
											$afterLoaded($player);
										}
									});
									break;
								default:
									$player = $csession->getPlayer();
									if ($player instanceof Player) {
										$afterLoaded($player);
									}
									break;
							}
						});
					}
				});
			});
		});
	}

	public function onQuit(Player $player): void {
		if($player->isFrozen()){
			foreach ($this->getServer()->getOnlinePlayers() as $pl) {
				/** @var AtPlayer $pl */
				if($pl->isLoaded() && $pl->isStaff()){
					$pl->sendMessage(TextFormat::RI . $player->getName() . " left while frozen!");
				}
				if ($player->isDisguiseEnabled()) $pl->getNetworkSession()->sendDataPacket(PlayerListPacket::remove([$player->getDisguise()->getPlayerListRemoveEntry()]));
			}
		}

		$this->getEntities()->onQuit($player);

		unset($this->getStaff()->playerByEid[$player->getId()]);


		$transferring = $player->getTransferring();
		if ($this->getSessionManager()->saveOnLeave) {
			$this->getSessionManager()->saveSession($player, true, function (CoreSession $session) use ($transferring): void {
				$session->getSessionManager()->removeSession($session->getXuid());
				$pk = new PlayerSessionSavedPacket([
					"player" => $session->getGamertag(),
					"server" => $transferring,
					"type" => PlayerSessionSavedPacket::TYPE_CORE
				]);
				$pk->queue();
			});
		}
		switch (($ts = $this->getNetwork()->getServerManager()->getThisServer())->getType()) {
			case "lobby":
				Lobby::getInstance()->onQuit($player);
				if (Lobby::getInstance()->getSessionManager()->saveOnLeave) {
					Lobby::getInstance()->getSessionManager()->saveSession($player, true, function (LobbySession $session) use ($ts, $transferring): void {
						$session->getSessionManager()->removeSession($session->getXuid());
						if ($ts->isRelated($transferring)) {
							$pk = new PlayerSessionSavedPacket([
								"player" => $session->getGamertag(),
								"server" => $transferring,
								"type" => PlayerSessionSavedPacket::TYPE_GAME
							]);
							$pk->queue();
						}
					});
				}
				break;
			case "prison":
				Prison::getInstance()->onQuit($player);
				if (Prison::getInstance()->getSessionManager()->saveOnLeave) {
					Prison::getInstance()->getSessionManager()->saveSession($player, true, function (PrisonSession $session) use ($ts, $transferring): void {
						$session->getSessionManager()->removeSession($session->getXuid());
						if ($ts->isRelated($transferring)) {
							$pk = new PlayerSessionSavedPacket([
								"player" => $session->getGamertag(),
								"server" => $transferring,
								"type" => PlayerSessionSavedPacket::TYPE_GAME
							]);
							$pk->queue();
						}
					});
				}
				break;
			case "skyblock":
				SkyBlock::getInstance()->onQuit($player);
				if (SkyBlock::getInstance()->getSessionManager()->saveOnLeave) {
					if (!$ts->isRelated($transferring)) {
						SkyBlock::getInstance()->getSessionManager()->saveSession($player, true, function (SkyBlockSession $session) use ($ts, $transferring): void {
							$session->getSessionManager()->removeSession($session->getXuid());
							/**if($ts->isRelated($transferring)){
								$pk = new PlayerSessionSavedPacket([
									"player" => $session->getGamertag(),
									"server" => $transferring,
									"type" => PlayerSessionSavedPacket::TYPE_GAME
								]);
								$pk->queue();
							}*/
						});
					}
				}
				break;
			case "pvp":
				PvP::getInstance()->onQuit($player);
				if (PvP::getInstance()->getSessionManager()->saveOnLeave) {
					PvP::getInstance()->getSessionManager()->saveSession($player, true, function (PvPSession $session) use ($ts, $transferring): void {
						$session->getSessionManager()->removeSession($session->getXuid());
						if ($ts->isRelated($transferring)) {
							$pk = new PlayerSessionSavedPacket([
								"player" => $session->getGamertag(),
								"server" => $transferring,
								"type" => PlayerSessionSavedPacket::TYPE_GAME
							]);
							$pk->queue();
						}
					});
				}
				break;
		}
	}

	/**
	 * Logs player chat
	 * @param Player $player
	 * @param string $message
	 */
	public static function logChat(Player $player, string $message) : void{
		$time = microtime(true);
		Core::getInstance()->getSessionManager()->sendStrayRequest(new MySqlRequest(
			"chat_log_" . $player->getXuid() . "_" . $time,
			new MySqlQuery("main", "INSERT INTO chat_logs(xuid, timestamp, message) VALUES(?, ?, ?)", [$player->getXuid(), $time, $message])
		), function (MySqlRequest $request) {});
	}

	/**
	 * Broadcasts a toast to players
	 * @param ?string $title
	 * @param ?string $body
	 * @param ?array $players
	 */
	public static function broadcastToast(string $title = "", string $body = "", array $players = []): void {
		/** @var Player[] */
		$players = count($players) === 0 ? Server::getInstance()->getOnlinePlayers() : $players;
		$pk = ToastRequestPacket::create($title, $body);
		foreach ($players as $player) {
			$player->getNetworkSession()->sendDataPacket($pk);
		}
	}
}
