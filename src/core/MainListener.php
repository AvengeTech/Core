<?php

namespace core;

use pocketmine\Server;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\{
	PlayerCreationEvent,
	PlayerPreLoginEvent,
	PlayerLoginEvent,
	PlayerJoinEvent,
	PlayerChatEvent,
	PlayerMoveEvent,
	PlayerDeathEvent,
	PlayerInteractEvent,
	PlayerQuitEvent,
};
use pocketmine\event\entity\{
	EntityTeleportEvent,
	EntityDamageEvent,
	EntityDamageByEntityEvent,
	EntityDamageByChildEntityEvent,
	EntityItemPickupEvent,
	EntityBlockChangeEvent
};
use pocketmine\event\server\{
	CommandEvent,
	DataPacketReceiveEvent,
	DataPacketSendEvent,
	NetworkInterfaceRegisterEvent
};
use pocketmine\event\inventory\{
	CraftItemEvent,
	InventoryTransactionEvent
};

use pocketmine\inventory\{
	PlayerInventory,
	ArmorInventory,
	PlayerCraftingInventory,
	PlayerCursorInventory
};
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\raklib\RakLibInterface;
use pocketmine\network\mcpe\protocol\{
	AvailableActorIdentifiersPacket,
	GameRulesChangedPacket,
	InteractPacket,
	InventoryTransactionPacket,
	ModalFormResponsePacket,
	PlayerListPacket,
	EmotePacket,
	LevelSoundEventPacket,
	LoginPacket,
	ContainerOpenPacket,
	TextPacket,
	StartGamePacket,
	types\NetworkPermissions,
	types\inventory\ItemStack,
	types\inventory\UseItemOnEntityTransactionData,
	types\BoolGameRule
};
use pocketmine\player\{
	PlayerInfo,
};
use pocketmine\player\chat\LegacyRawChatFormatter;

use paroxity\portal\Portal;
use Ramsey\Uuid\Lazy\LazyUuidFromString as UUID;

use core\AtPlayer as Player;
use core\block\Chest;
use core\block\DyedShulkerBox;
use core\block\EnderChest;
use core\block\ShulkerBox;
use core\block\tile\Chest as TileChest;
use core\block\tile\EnderChest as TileEnderChest;
use core\block\tile\ShulkerBox as TileShulkerBox;
use core\Core;
use core\crafting\ColoredShulkerRecipe;
use core\discord\objects\Post;
use core\entities\bots\Bot;
use core\inbox\inventory\MessageInventory;
use core\network\{
	Links,
	Structure as NS,
};
use core\network\protocol\StaffCommandSeePacket;
use core\network\waterdog\{
	handler\WDPELoginPacketHandler
};
use core\settings\GlobalSettings;
use core\staff\{
	inventory\SeeinvInventory,
};
use core\network\protocol\PlayerChatPacket;
use core\network\raklib\NetworkInterface;
use core\rank\Rank;
use core\staff\inventory\EnderinvInventory;
use core\utils\{
	BlockRegistry,
	TextFormat,
	gpt\GptRequest,
	entity\TempFallingBlock,
	ItemRegistry,
};
use Exception;
use pocketmine\block\ShulkerBox as BlockShulkerBox;
use pocketmine\block\tile\ShulkerBox as BlockTileShulkerBox;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\world\ChunkLoadEvent;
use pocketmine\event\world\WorldParticleEvent;
use pocketmine\inventory\transaction\action\InventoryAction;
use skyblock\settings\SkyBlockSettings;

use pocketmine\lang\Translatable;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\PacketBroadcaster;
use pocketmine\network\mcpe\protocol\types\BlockPaletteEntry;
use pocketmine\network\mcpe\StandardEntityEventBroadcaster;
use pocketmine\network\mcpe\StandardPacketBroadcaster;
use pocketmine\network\query\DedicatedQueryNetworkInterface;
use pocketmine\scheduler\ClosureTask;
use skyblock\SkyBlockPlayer;

class MainListener implements Listener{

	public array $tapCooldown = [];
	public array $playerInfo = [];
	public array $sprintCheck = [];

	public bool $registeredSelfInterface = false;

	const PACK_DATA = [
		"[REDACTED]" => [
			"contentId" => "[REDACTED]",
			"encryptionKey" => "[REDACTED]"
		],
	];

	public function __construct(public Core $plugin) {}

	public function regCustomPlayer(PlayerCreationEvent $e) {
		$e->setPlayerClass(AtPlayer::class);
	}

	public function onInterfaceReg(NetworkInterfaceRegisterEvent $e) {
		$interface = $e->getInterface();
		if ($interface instanceof RakLibInterface) {
			if (!$interface instanceof NetworkInterface) {
				$e->cancel();
				($server = $this->plugin->getServer())->getNetwork()->registerInterface(new NetworkInterface($server, $server->getIp(), $server->getPort(), false, $pbroad = new StandardPacketBroadcaster($server), new StandardEntityEventBroadcaster($pbroad, TypeConverter::getInstance()), TypeConverter::getInstance()));
				$this->registeredSelfInterface = true;
			}
		} elseif ($this->registeredSelfInterface && $interface instanceof DedicatedQueryNetworkInterface) $e->cancel();
		if ($this->plugin->getNetwork()->getProxy() == NS::PROXY_WATERDOG) {
			if ($interface instanceof RakLibInterface) $interface->setPacketLimit(PHP_INT_MAX);
		}
	}

	public function onCraft(CraftItemEvent $e) {
		if ($e->isCancelled()) return;

		$recipe = $e->getRecipe();
		if ($recipe instanceof ColoredShulkerRecipe) {
			$falseGrid = new PlayerCraftingInventory($e->getPlayer());
			$falseGrid->setContents($e->getInputs());
			$realOutput = $recipe->getResultsFor($falseGrid);
			if (!empty($realOutput)) {
				$realOutput = $realOutput[array_key_first($realOutput)];
				$output = $e->getOutputs()[array_key_first($e->getOutputs())];
				if (!$realOutput->equals($output)) {
					$reflection = new \ReflectionClass($e);
					$prop = $reflection->getProperty('outputs');
					$prop->setAccessible(true);
					$prop->setValue($e, [$realOutput]);
					Core::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($e): void {
						$player = $e->getPlayer();
						if (!is_null($player) && $player->isConnected()) $player->getNetworkSession()->getInvManager()?->syncAll();
					}), 1);
				}
			}
		}
	}

	/**
	 * @priority HIGH
	 */
	public function onPre(PlayerPreLoginEvent $e) {
		$player = ($info = $e->getPlayerInfo())->getUsername();
		if (isset(Core::$dataTransfers[strtolower($player)])) {
			$e->setKickFlag(PlayerPreLoginEvent::KICK_FLAG_PLUGIN, TextFormat::RED . "You were kicked by: " . TextFormat::BOLD . TextFormat::AQUA . "Avenge" . TextFormat::GOLD . "Tech " . TextFormat::RESET . TextFormat::AQUA . "Support" . PHP_EOL . TextFormat::RED . "Reason: Data transfer in progress...");
			return;
		}
		$this->playerInfo[$player] = $e->getPlayerInfo();
	}

	/**
	 * @priority HIGH
	 */
	public function onLogin(PlayerLoginEvent $e) {
		/** @var Player $player */
		$player = $e->getPlayer();
		$result = $player->handlePreLogin($this->playerInfo[$player->getName()]);
		unset($this->playerInfo[$player->getName()]);
	}

	/**
	 * @priority HIGH
	 */
	public function onJoin(PlayerJoinEvent $e) {
		$e->setJoinMessage("");
		/** @var Player $player */
		$player = $e->getPlayer();
		if (Core::thisServer()->getId() == "idle-1") {
			$player->setNoClientPredictions();
		}
		if (Core::thisServer()->isTestServer()) {
			$pk = new GameRulesChangedPacket();
			$pk->gameRules["showcoordinates"] = new BoolGameRule(true, true);
			$player->getNetworkSession()->sendDataPacket($pk);
		} else {
			$pk = new GameRulesChangedPacket();
			$pk->gameRules["showcoordinates"] = new BoolGameRule(false, false);
			$player->getNetworkSession()->sendDataPacket($pk);
		}
		if ($player->isFromProxy() && $this->plugin->getNetwork()->getProxy() == NS::PROXY_PORTAL) {
			Portal::getInstance()->requestPlayerInfo($player, function (UUID $uuid, ?Player $player, int $status, string $xuid, string $address) {
				if ($player === null) return;
				$reflection = new \ReflectionClass($player);
				$property = $reflection->getProperty("xuid");
				$property->setAccessible(true);
				$property->setValue($player, $xuid);
				$player->setIp(explode(":", $address)[0]);
				$player->setPlayerInfoLoaded();
			});
		}
		$this->plugin->onJoin($player);

		//$player->setScale(0.5);
		//$this->plugin->getScheduler()->scheduleDelayedTask(new BanCheckDelayedTask($e->getPlayer()), 10);
	}

	public function onQuit(PlayerQuitEvent $e) {
		$e->setQuitMessage("");
		$this->plugin->onQuit($e->getPlayer());
	}

	/**
	 * @priority LOW
	 */
	public function onChat(PlayerChatEvent $e) {
		/** @var Player $player */
		$player = $e->getPlayer();
		$msg = $e->getMessage();
		$chat = $this->plugin->getChat();
		$staff = $this->plugin->getStaff();
		if (!$player->isLoaded()) {
			$e->cancel();
			return;
		}
		if ($staff->areAllMuted() && !$player->isTier3()) {
			$player->sendMessage(TextFormat::RI . "Everyone has been temporarily muted.");
			$e->cancel();
			return;
		}
		if ($player->isMuted()) {
			$player->sendMessage(TextFormat::RI . "You are muted! Appeal for an unmute in our Discord server, " . TextFormat::YELLOW . Links::DISCORD);
			$e->cancel();
			return;
		}

		if ($player->getSession()->getStaff()->inStaffChat()) {
			$e->cancel();
			$staff->sendStaffMessage($player, $msg, $this->plugin->getNetwork()->getIdentifier());
			return;
		}

		$player->lastNonAfkTick = Server::getInstance()->getTick();

		if ($chat->getAntiSpam()->talked($player)) {
			$e->cancel();
			$player->sendMessage(TextFormat::RI . "You can talk again in " . $chat->getAntiSpam()->canTalkIn($player) . " seconds. Reduce your chat delay with a paid rank! " . Links::SHOP);
			return;
		}
		$chat->getAntiSpam()->setTalked($player);

		$offense = $chat->getFilter()->getStringOffense($msg);
		if ($offense > 0) {
			$e->cancel();
			$player->sendMessage(TextFormat::RI . "Please do not advertise!");
			return;
		}

		$msg = str_replace("[item]", TextFormat::BOLD . TextFormat::YELLOW . "[" . TextFormat::RESET . $player->getInventory()->getItemInHand()->getName() . TextFormat::RESET . TextFormat::BOLD . TextFormat::YELLOW . "]" . TextFormat::RESET . TextFormat::GRAY, TextFormat::clean($msg));

		$e->setFormatter(new LegacyRawChatFormatter("{%1}"));
		$e->setMessage($chat->getChatFormat($player, $msg));
		$player->setLastMessage($msg);

		if (Core::thisServer()->getType() === "skyblock") {
			/** @var SkyBlockPlayer $player */
			if (($gs = $player->getGameSession())->getSettings()->getSetting(SkyBlockSettings::ISLAND_CHAT)) {
				if ($gs->getIslands()->atValidIsland()) {
					$e->cancel();
					return;
				}
			}
		}

		$this->plugin->getDiscord()->getChatQueue()->addMessage($player, $msg);

		$servers = [];
		foreach (Core::thisServer()->getSubServers(false, true) as $server) {
			$servers[] = $server->getIdentifier();
		}
		if (!isset($chat->cfc[$player->getName()])) $chat->updateChatFormat($player);
		(new PlayerChatPacket([
			"player" => $player->getName(),
			"message" => $msg,
			"server" => Core::thisServer()->getIdentifier(),
			"formatted" => $chat->getChatFormat($player, $msg),
			"format" => $chat->cfc[$player->getName()],
			"rank" => ($player->getSession()->getRank()->hasSub() && !$player->isStaff()) ? "warden" : $player->getRank(),
			"sendto" => $servers,
		]))->queue();

		Core::logChat($player, $msg);

		$e->cancel();
		foreach (Server::getInstance()->getOnlinePlayers() as $p) {
			/** @var AtPlayer $p */
			$p->sendChatMessage($chat->cfc[$player->getName()], $player->isDisguiseEnabled() ? $player->getDisguise()->getRank() : (($player->getSession()->getRank()->hasSub() && !$player->isStaff()) ? "warden" : $player->getRank()), $msg, $chat->getChatFormat($player, $msg));
		}

		if ($player->isSn3ak()) {
			if (str_starts_with(strtolower($msg), "hey techie")) {
				$player->setTechieMode();
				$this->plugin->getGptQueue()->quickRequest($msg, function (GptRequest $request) use ($player): void {
					$response = $request->getResponse();
					$choices = $response->getChoices();
					foreach ($choices as $choice) {
						Server::getInstance()->broadcastMessage(TextFormat::EMOJI_TECHIE . TextFormat::BOLD . TextFormat::AQUA . " Techie: " . TextFormat::RESET . TextFormat::AQUA . ($msg = $choice->getMessage())->getContent());
						$player->getTechieConversation()->addMessage($msg);
					}
				}, $player->getTechieConversation());
			} elseif (str_contains(strtolower($msg), "bye techie")) {
				$player->setTechieMode(false);
				$this->plugin->getGptQueue()->quickRequest($msg, function (GptRequest $request) use ($player): void {
					$response = $request->getResponse();
					$choices = $response->getChoices();
					foreach ($choices as $choice) {
						Server::getInstance()->broadcastMessage(TextFormat::EMOJI_TECHIE . TextFormat::BOLD . TextFormat::AQUA . " Techie: " . TextFormat::RESET . TextFormat::AQUA . ($msg = $choice->getMessage())->getContent());
						$player->getTechieConversation()->addMessage($msg);
					}
				}, $player->getTechieConversation());
			} elseif ($player->inTechieMode()) {
				$player->setTechieMode();
				$this->plugin->getGptQueue()->quickRequest($msg, function (GptRequest $request) use ($player): void {
					$response = $request->getResponse();
					$choices = $response->getChoices();
					foreach ($choices as $choice) {
						Server::getInstance()->broadcastMessage(TextFormat::EMOJI_TECHIE . TextFormat::BOLD . TextFormat::AQUA . " Techie: " . TextFormat::RESET . TextFormat::AQUA . ($msg = $choice->getMessage())->getContent());
						$player->getTechieConversation()->addMessage($msg);
					}
				}, $player->getTechieConversation());
			}
		}
	}

	/**
	 * @priority LOWEST
	 */
	public function onCommandPre(CommandEvent $e) {
		$player = $e->getSender();
		if (!$player instanceof Player) return;

		if (!$player->isLoaded()) {
			$e->cancel();
			return;
		}
		$cmd = $e->getCommand();
		$raw = $cmd;
		$cmd = explode(" ", $cmd)[0];

		if ($player->isFrozen()) {
			$e->cancel();
			$player->sendMessage(TextFormat::RI . "You cannot send commands while frozen!");
			return;
		}

		$found = false;
		foreach ($this->plugin->getServer()->getCommandMap()->getCommands() as $command) {
			if ($command->getName() == $cmd) {
				$found = true;
				break;
			}
			foreach ($command->getAliases() as $alias) {
				if ($alias == $cmd || $alias == "/" . $cmd) {
					$found = true;
					break 2;
				}
			}
		}
		if (!$found) {
			$e->cancel();
			$player->sendMessage(TextFormat::RED . "Unknown command.");
			return;
		}


		$player->getSession()->getStaff()->getWatchlist()->addCommand($raw);
		$ts = $this->plugin->getNetwork()->getServerManager()->getThisServer();
		$post = new Post(
			$player->getName() . ": _/" . $raw . "_",
			$player->getName() . ($ts->isSubServer() ? " | " . $ts->getId() : "")
		);
		$post->setWebhook(Core::getInstance()->getDiscord()->getConsoleWebhook());
		$post->send();

		(new StaffCommandSeePacket([
			"sender" => $player->getName(),
			"identifier" => Core::thisServer()->getIdentifier(),
			"command" => $raw
		]))->queue();

		$prevent = [
			"tell", "tt", "m", "w",
			"whisper", "msg",
			"reply", "re", "r",
		];
		if (in_array($cmd, $prevent)) {
			if ($player->isMuted()) {
				$player->sendMessage(TextFormat::RI . "You are muted! Appeal for an unmute in our Discord server, " . TextFormat::YELLOW . Links::DISCORD);
				$e->cancel();
				return;
			}
		}
	}

	public function onMove(PlayerMoveEvent $e) {
		/** @var Player $player */
		$player = $e->getPlayer();
		if (!$player->isLoaded()) {
			$e->cancel();
			return;
		}
		/**if(
			$this->plugin->getTutorials()->inTutorial($player) &&
			$e->getFrom()->distance($e->getTo()) > 0
		){
			$e->cancel();
			return;
		}*/
		/**if(!$player->isSprinting() && $player->getSession()->getSettings()->getSetting(GlobalSettings::AUTO_SPRINT)){
			if(($this->sprintCheck[$player->getName()] ?? 0) != time()){
				$player->toggleSprint(true);
			}
			$this->sprintCheck[$player->getName()] = time();
		}*/
		foreach ($this->plugin->getEntities()->getBots()->getBots() as $bot) {
			$bot->move($player);
		}
		foreach ($this->plugin->getEntities()->getFloatingText()->getTexts() as $text) {
			$text->move($player);
		}
	}

	public function onDeath(PlayerDeathEvent $e) {
		$e->setDeathMessage('');
	}

	public function onPickupItem(EntityItemPickupEvent $e) {
		$entity = $e->getEntity();
		if ($entity instanceof AtPlayer && !$entity->isLoaded()) {
			$e->cancel();
			return;
		}
	}

	public function onChange(EntityTeleportEvent $e) {
		$player = $e->getEntity();
		if ($player instanceof Player && $e->getTo()->getWorld() !== $e->getFrom()->getWorld()) {
			$this->plugin->getEntities()->changeLevel($player, $e->getTo()->getWorld()->getDisplayName());
			$this->plugin->getCosmetics()->changeLevel($player, $e->getTo()->getWorld()->getDisplayName());
		}
	}

	public function onDmg(EntityDamageEvent $e) {
		if ($e instanceof EntityDamageByEntityEvent && $e->getCause() != EntityDamageEvent::CAUSE_MAGIC) {
			$killer = $e->getDamager();
			if ($killer instanceof Player) {
				if ($e instanceof EntityDamageByChildEntityEvent) {
					$killer->playSound("random.orb", $killer->getPosition()->subtract(0, 5, 0), 50, 0.5);
				} else {
					$entity = $e->getEntity();
					if ($entity instanceof SkyBlockPlayer && $entity->getSession()->getSettings()->getSetting(GlobalSettings::CPS_PING_COUNTER)) {
						$tping = $killer?->getSession()?->getStaff()->getPing() ?? "0";
						$entity->sendActionBarMessage(TextFormat::YELLOW . "CPS: " . TextFormat::GOLD . $entity->getSession()->getStaff()->getDisplayCPS() . TextFormat::GRAY . " | " . TextFormat::YELLOW . "Ping: " . TextFormat::GREEN . $entity->getSession()->getStaff()->getPing() . "ms" . TextFormat::AQUA . "/" . TextFormat::RED . $tping . "ms");
					}
				}
			}
		}
	}

	public function onInvPickup(EntityItemPickupEvent $e) {
		$player = $e->getOrigin();
		if ($player instanceof Player) {
			if (!$player->isLoaded()) {
				$e->cancel();
				return;
			}
		}
	}

	public function onBlockChange(EntityBlockChangeEvent $e) {
		if ($e->getEntity() instanceof TempFallingBlock) {
			$e->cancel();
		}
	}

	public function onReceive(DataPacketReceiveEvent $e) {
		/** @var Player $player */
		$player = ($origin = $e->getOrigin())->getPlayer();
		$packet = $e->getPacket();

		if (
			$packet instanceof LoginPacket &&
			/**Core::thisServer()->getIdentifier() !== "skyblock-test" &&*/
			/**$origin->getIp() == "127.0.0.1" &&*/
			NS::PROXY == NS::PROXY_WATERDOG
		) {
			$e->getOrigin()->setHandler(
				new WDPELoginPacketHandler(
					$this->plugin->getServer(),
					$e->getOrigin(),
					function (PlayerInfo $info) use ($e): void {
						(function () use ($info): void {
							$this->info = $info;
							$this->getLogger()->info("Player: " . TextFormat::AQUA . $info->getUsername() . TextFormat::RESET);
							$this->getLogger()->setPrefix($this->getLogPrefix());
						})->call($e->getOrigin());
					},
					function (bool $isAuthenticated, bool $authRequired, null|Translatable|string $error, ?string $clientPubKey) use ($e): void {
						if ($error instanceof Translatable) $error = $error->getText();
						(function () use ($isAuthenticated, $authRequired, $error, $clientPubKey): void {
							$this->setAuthenticationStatus($isAuthenticated, $authRequired, $error, $clientPubKey);
						})->call($e->getOrigin());
					}
				)
			);
		}

		if ($packet instanceof InventoryTransactionPacket) {
			$data = $packet->trData;
			$action = $data->getTypeId();

			if ($data instanceof UseItemOnEntityTransactionData && (!isset($this->tapCooldown[$player->getXuid()]) || $this->tapCooldown[$player->getXuid()] !== time())) {
				$this->tapCooldown[$player->getXuid()] = time();
				$eid = $data->getActorRuntimeId();
				foreach ($this->plugin->getEntities()->getBots()->getBots() as $bot) {
					/** @var Bot $bot */
					if ($bot->getId() == $eid || $bot->getSittingId() == $eid) {
						$bot->interact($player);
						return;
					}
				}
			}
		}

		if ($packet instanceof InteractPacket && $packet->action === InteractPacket::ACTION_LEAVE_VEHICLE) {
			if ($player->onSailingSub()) {
				$player->getSailingSub()->getUp($player);
			}
		}

		if ($packet instanceof ModalFormResponsePacket) {
			$player->handleModalFormResponse($packet);
			$player->lastNonAfkTick = Server::getInstance()->getTick();
		}

		if ($packet instanceof EmotePacket) {
			var_dump($packet->getEmoteId());
			if ($packet->getFlags() === 0) {
				$e->cancel();
				$pk = EmotePacket::create($player->getId(), $packet->getEmoteId(), $packet->getEmoteLengthTicks(), "", "", EmotePacket::FLAG_MUTE_ANNOUNCEMENT);
				foreach ($player->getViewers() as $viewer) $viewer->getNetworkSession()->sendDataPacket($pk);
			}


			$emote = $packet->getEmoteId();
			$techie = $this->plugin->getTechie()->getTechie();
			if ($techie !== null)
				$techie->copy($player, $packet->getEmoteId());
		}

		if ($packet instanceof LevelSoundEventPacket) {
			if ($player->isVanished())
				$e->cancel();
		}

		if ($packet instanceof StartGamePacket) {
			$packet->levelSettings->muteEmoteAnnouncements = true;
			$packet->networkPermissions = new NetworkPermissions(true);
		}

		if ($packet instanceof PlayerListPacket) {
			if (!$player->firstList) {
				$player->firstList = true;
				//$e->cancel(); //todo
			}
		}
	}

	public function onDataPacketSend(DataPacketSendEvent $e) {
		foreach($e->getPackets() as $pk){
			if($pk instanceof ContainerOpenPacket) {
				if (!is_int($pk->windowId) || !is_int($pk->windowType)) {
					$e->cancel();
				}
			}
			if($pk instanceof AvailableActorIdentifiersPacket) {
				$pk->identifiers = $this->plugin->getEntities()->getCustomEntityRegistry()->nbt($pk->identifiers);
			}
			// if ($pk instanceof ResourcePacksInfoPacket) {
			// 	foreach ($pk->resourcePackEntries as $key => $entry) {
			// 		try {
			// 			$property = new \ReflectionProperty($entry::class, "packId");
			// 			$property->setAccessible(true);
			// 			$class = new \ReflectionClass($entry);
			// 			if (isset(self::PACK_DATA[$packId = $entry->getPackId()->getUrn()])) {
			// 				$data = self::PACK_DATA[$packId];
			// 				$property = new \ReflectionProperty($entry::class, "contentId");
			// 				$property->setAccessible(true);
			// 				$property->setValue($entry, $data["contentId"]);
			// 				$property = new \ReflectionProperty($entry::class, "encryptionKey");
			// 				$property->setAccessible(true);
			// 				$property->setValue($entry, $data["encryptionKey"]);
			// 				$pk->resourcePackEntries[$key] = $entry;
			// 			}
			// 		} catch (\ReflectionException $e) {
			// 			$this->plugin->getLogger()->logException($e);
			// 		}
			// 	}
			// }
			if ($pk instanceof TextPacket && in_array($pk->type, [TextPacket::TYPE_CHAT, TextPacket::TYPE_RAW])) {
				$pk->message = $pk->message . " ";
			}
		}
	}

	public function onInvMove(InventoryTransactionEvent $e) {
		$t = $e->getTransaction();
		/** @var Player $player */
		$player = $t->getSource();
		foreach ($t->getInventories() as $inv) {
			/** @var SlotChangeAction[] */
			$slot = array_filter($t->getActions(), function (InventoryAction $action) use ($inv): bool {
				return $action instanceof SlotChangeAction && $action->getInventory() == $inv;
			});
			try {
				$slot = $slot[array_key_first($slot)]->getSlot();
			} catch (Exception $e) {
				$slot = -1;
			}
			if (
				$inv instanceof PlayerInventory ||
				$inv instanceof ArmorInventory ||
				$inv instanceof PlayerCursorInventory
			) {
				continue;
			} else {
				if ($this->plugin->getNetwork()->aboutToRestart()) {
					$e->cancel();
					return;
				}
			}
			if ($inv instanceof SeeinvInventory || $inv instanceof EnderinvInventory) {
				if ($player->getRankHierarchy() >= Rank::HIERARCHY_SR_MOD && $inv->isValidSlot($slot)) {
					continue;
				}
				$e->cancel();
				$player->getNetworkSession()->getInvManager()->syncSlot($player->getCursorInventory(), 0, ItemStack::null());
				break;
			}
			if ($inv instanceof MessageInventory) {
				foreach ($t->getActions() as $action) {
					if (
						$action instanceof SlotChangeAction &&
						$action->getInventory() instanceof MessageInventory
					) {
						if ($action->getSourceItem()->equals(VanillaItems::AIR(), true, false)) {
							$e->cancel();
							return;
						}
						if (($cmd = $action->getSourceItem()->getNamedTag()->getString("command", "")) !== "") {
							$e->cancel();
							$action->getInventory()->removeItem($action->getSourceItem());
							($server = Server::getInstance())->dispatchCommand(new ConsoleCommandSender($server, $server->getLanguage()), str_replace("{player}", '"' . $player->getName() . '"', $cmd));
						}
					}
				}
			}
		}
	}

	public function onParticle(WorldParticleEvent $e) {
		/** @var AtPlayer[] */
		$recipients = [];
		foreach ($e->getRecipients() as $player) {
			/** @var AtPlayer $player */
			if ($player->getSession()?->getSettings()->getSetting(GlobalSettings::PARTICLES)) $recipients[] = $player;
		}
		$e->setRecipients($recipients);
	}

	/** CONTAINER FIX */
	public function onChunkLoad(ChunkLoadEvent $event) {
		if(Core::thisServer()->getType() !== "skyblock") return;
		$tiles = $event->getChunk()->getTiles();

		foreach ($tiles as $tile) {
			$pos = ($block = $tile->getBlock())->getPosition();
			if (!$pos->isValid()) continue;
			$world = $pos->getWorld();
			if ($tile instanceof TileShulkerBox) {
				/** @var ShulkerBox $block */
				if ($block instanceof DyedShulkerBox) {
					$replaceBlock = VanillaBlocks::DYED_SHULKER_BOX()->setColor($block->getColor());
				} else {
					$replaceBlock = VanillaBlocks::SHULKER_BOX();
				}
				$replaceBlock->setFacing($block->getFacing());
				$newTile = new BlockTileShulkerBox($world, $pos);

				$inventoryContents = $tile->getInventory()->getContents();
				$newTile->getInventory()->setContents($inventoryContents);
				$newTile->addNameSpawnData($tile->getSpawnCompound());
				$world->setBlock($pos, $replaceBlock);

				$world->removeTile($tile);
				$world->addTile($newTile);
				continue;
			}
			if ($tile instanceof \pocketmine\block\tile\Chest) {
				/** @var \pocketmine\block\Chest $block */
				$oldChestData = [$tile->getSpawnCompound(), $tile->getInventory()->getContents(), $tile->getName(), $block->getFacing()];
				$newChest = BlockRegistry::CHEST();
				$newChest->setFacing($oldChestData[3]);
				$newTile = new TileChest($world, $pos);

				$newTile->addNameSpawnData($oldChestData[0]);
				$newTile->getInventory()->setContents($oldChestData[1]);
				$newTile->setName($oldChestData[2]);
				$world->setBlock($pos, $newChest);

				$world->removeTile($tile);
				$world->addTile($newTile);
				$world->getBlock($pos)->onPostPlace();
				continue;
			}
			if ($tile instanceof \pocketmine\block\tile\EnderChest) {
				/** @var \pocketmine\block\EnderChest $block */
				$oldChestData = [$block->getFacing()];
				$newChest = BlockRegistry::ENDER_CHEST();
				$newChest->setFacing($oldChestData[0]);
				$newTile = new TileEnderChest($world, $pos);

				$world->setBlock($pos, $newChest);

				$world->removeTile($tile);
				$world->addTile($newTile);
				continue;
			}
		}
	}

	public function onInteract(PlayerInteractEvent $e) {
		$pos = ($block = $e->getBlock())->getPosition();
		if (!$pos->isValid()) return;
		$tile = ($world = $pos->getWorld())->getTile($pos);

		if ($tile instanceof TileShulkerBox) {
			/** @var ShulkerBox $block */
			$e->cancel();
			if ($block instanceof DyedShulkerBox) {
				$replaceBlock = VanillaBlocks::DYED_SHULKER_BOX()->setColor($block->getColor());
			} else {
				$replaceBlock = VanillaBlocks::SHULKER_BOX();
			}
			$replaceBlock->setFacing($block->getFacing());
			$newTile = new BlockTileShulkerBox($world, $pos);

			$inventoryContents = $tile->getInventory()->getContents();
			$newTile->getInventory()->setContents($inventoryContents);
			$newTile->addNameSpawnData($tile->getSpawnCompound());
			$world->setBlock($pos, $replaceBlock);

			$world->removeTile($tile);
			$world->addTile($newTile);
			return;
		}
		if ($tile instanceof \pocketmine\block\tile\Chest) {
			/** @var \pocketmine\block\Chest $block */
			$oldChestData = [$tile->getSpawnCompound(), $tile->getInventory()->getContents(), $tile->getName(), $block->getFacing()];
			$newChest = BlockRegistry::CHEST();
			$newChest->setFacing($oldChestData[3]);
			$newTile = new TileChest($world, $pos);

			$newTile->addNameSpawnData($oldChestData[0]);
			$newTile->getInventory()->setContents($oldChestData[1]);
			$newTile->setName($oldChestData[2]);
			$world->setBlock($pos, $newChest);

			$world->removeTile($tile);
			$world->addTile($newTile);
			$world->getBlock($pos)->onPostPlace();
			return;
		}
		if ($tile instanceof \pocketmine\block\tile\EnderChest) {
			/** @var \pocketmine\block\EnderChest $block */
			$oldChestData = [$block->getFacing()];
			$newChest = BlockRegistry::ENDER_CHEST();
			$newChest->setFacing($oldChestData[0]);
			$newTile = new TileEnderChest($world, $pos);

			$world->setBlock($pos, $newChest);

			$world->removeTile($tile);
			$world->addTile($newTile);
			return;
		}
	}
}
