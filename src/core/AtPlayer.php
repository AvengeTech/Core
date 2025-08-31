<?php

namespace core;

use core\chat\Chat;
use core\chat\Structure as ChatStructure;
use pocketmine\Server;
use pocketmine\entity\{
	Attribute,
	effect\EffectInstance,
	Entity,
	Human,
	Living,
	Location
};
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\convert\{
	TypeConverter
};
use pocketmine\network\mcpe\protocol\{
	ModalFormRequestPacket,
	ModalFormResponsePacket,
	PlaySoundPacket,
	ServerSettingsResponsePacket,
	SetPlayerGameTypePacket,

	types\entity\EntityMetadataProperties,
	types\entity\StringMetadataProperty,
	types\entity\EntityLink,
	types\entity\PropertySyncData,

	AddPlayerPacket,
	MovePlayerPacket,
	PlayerAuthInputPacket,
	SetActorLinkPacket,
	UpdateAbilitiesPacket,
	types\AbilitiesData,
	types\DeviceOS,
	types\GameMode as GM,
	types\inventory\ItemStackWrapper
};
use pocketmine\player\{
	GameMode,
	Player,
	PlayerInfo,
	XboxLivePlayerInfo
};
use core\network\handler\SurvivalBlockBreakHandler;
use pocketmine\scheduler\ClosureTask;
use core\utils\TextFormat;

use core\Core;
use core\discord\objects\{
	Embed,
	Field,
	Footer,
	Post,
	Webhook
};
use core\gadgets\entity\SailingSub;
use core\inventory\ItemConversionListener;
use core\inventory\PlayerEnderInventoryListener;
use core\inventory\PlayerInventoryListener;
use core\items\type\TieredTool;
use core\rank\Structure as RankStructure;
use core\session\CoreSession;
use core\settings\GlobalSettings;
use core\staff\{
	entry\MuteEntry,
	inventory\SeeinvInventory
};
use core\staff\anticheat\session\Session;
use core\staff\anticheat\session\SessionManager;
use core\staff\inventory\EnderinvInventory;
use core\staff\utils\Disguise;
use core\techie\Structure as TStr;
use core\ui\{
	CustomUI,
	windows\CustomForm
};
use core\user\User;
use core\utils\{
	Facing,
	KickDelayTask,
	LoadAction,
	VpnDetectTask,
	gpt\Conversation,
	ItemRegistry,
	Utils,
	VpnCache,
};
use pocketmine\entity\animation\ArmSwingAnimation;
use pocketmine\entity\animation\CriticalHitAnimation;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\object\ItemEntity;
use pocketmine\entity\projectile\Arrow;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\item\Durable;
use pocketmine\item\enchantment\MeleeWeaponEnchantment;
use pocketmine\item\Item;
use pocketmine\lang\Translatable;
use pocketmine\nbt\tag\CompoundTag;
use core\network\NetworkSession;
use core\session\PlayerSession;
use core\staff\anticheat\utils\Devices;
use pocketmine\block\BlockTypeTags;
use pocketmine\block\VanillaBlocks;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\InputMode;
use pocketmine\network\mcpe\protocol\types\PlayerAuthInputFlags;
use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\PermissibleBase;
use pocketmine\permission\PermissibleDelegateTrait;
use pocketmine\ServerProperties;
use pocketmine\timings\Timings;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\Position;
use pocketmine\world\sound\EntityAttackNoDamageSound;
use pocketmine\world\sound\EntityAttackSound;
use pocketmine\world\sound\FireExtinguishSound;
use pocketmine\world\sound\ItemBreakSound;
use pocketmine\world\World;
use prison\Prison;
use prison\PrisonPlayer;
use skyblock\SkyBlockPlayer;

class AtPlayer extends Player {
	use PermissibleDelegateTrait;

	const MAX_REACH_DISTANCE = 6.25;

	const DEVICE_NAMES = [
		1 => "Android",
		2 => "iOS",
		3 => "OSX",
		4 => "Amazon",
		5 => "VR",
		6 => "HLNS",
		7 => "W10",
		8 => "W32",
		9 => "DEDI",
		10 => "TVOS",
		11 => "PS4",
		12 => "Switch",
		13 => "Xbox",
		14 => "why",
	];

	private EntityMetadataCollection $networkProperties;
	private bool $checkBlockIntersectionsNextTick = true;

	protected ?SurvivalBlockBreakHandler $newBlockBreakHandler = null;

	private ?Position $spawnPosition = null;
	private ?Position $deathPosition = null;

	public bool $fromProxy = false;
	public bool $playerInfoLoaded = true; //Portal
	public ?\Closure $whenInfoLoaded = null;

	public bool $sessionSaved = false;
	public ?\Closure $whenSessionSaved = null;
	public bool $gameSessionSaved = false;
	public ?\Closure $whenGameSessionSaved = null;

	public bool $loaded = false;
	public array $preLoadActions = [];
	public array $loadActions = [];

	public User $user;

	public bool $firstConnection = true;
	public string $ipAddress = "";

	public bool $vpnCheck = true;
	public string $ip = "";

	public int $lastTeleportTick = 0;

	public bool $afkStatus = false;
	private array $afkData = [];
	public int $lastNonAfkTick = 0;

	/** Login Packet Data */
	public string $connected_from = "";
	public string $clientId;
	public array $clientData;

	public string $transferring = "";
	public string $transferredFrom = ""; //onConnect from pending connection
	public int $lastTransferAttempt = 0;
	public int $graphicsMode;

	/** UI Data */
	public float $lastModalOpened = 0;
	/** @var array<int, CustomUi> */
	public array $activeModalWindows = [];
	public int $lastModalId = 1;

	public bool $vanished = false;
	public bool $frozen = false;

	public bool $voted = false;

	public string $lastMessaged = "";

	public ?SeeinvInventory $seeInv = null;
	public ?EnderinvInventory $enderInv = null;

	public bool $flightMode = false;

	public ?SailingSub $sub = null;

	const TECHIE_MODE_TIMEOUT = 30;
	public bool $techieMode = false;
	public int $techieModeExpire = -1;
	public ?Conversation $techieConversation = null;

	public bool $firstList = false;

	public string $lastMessage = "";

	public int $invUpdateTick = -1;
	public int $enderUpdateTick = -1;

	public ?Session $anticheatSession;
	public int $badInteractions = 0;

	public function __construct(Server $server, NetworkSession $session, PlayerInfo $playerInfo, bool $authenticated, Location $spawnLocation, ?CompoundTag $namedtag) {
		parent::__construct($server, $session, $playerInfo, $authenticated, $spawnLocation, $namedtag);

		$this->networkProperties = new EntityMetadataCollection();

		$rootPermissions = [DefaultPermissions::ROOT_USER => true];
		if ($this->server->isOp($this->username)) {
			$rootPermissions[DefaultPermissions::ROOT_OPERATOR] = true;
		}
		$this->perm = new PermissibleBase($rootPermissions);

		$this->graphicsMode = $playerInfo->getExtraData()["GraphicsMode"];
	}

	public function getGraphicsMode(): int {
		return $this->graphicsMode;
	}

	public function getNetworkSession(): NetworkSession {
		if ($this->networkSession === null) {
			throw new \LogicException("Player is not connected");
		}
		return $this->networkSession;
	}

	public function isFromProxy(): bool {
		return $this->fromProxy;
	}

	public function hasPlayerInfoLoaded(): bool {
		return $this->playerInfoLoaded;
	}

	public function setPlayerInfoLoaded(bool $loaded = true): void {
		$this->playerInfoLoaded = $loaded;
	}

	public function whenInfoLoaded(): ?\Closure {
		return $this->whenInfoLoaded;
	}

	public function setWhenInfoLoaded(?\Closure $closure = null): void {
		$this->whenInfoLoaded = $closure;
	}

	public function setSessionSaved(bool $saved = true): void {
		$this->sessionSaved = $saved;
	}

	public function hasSessionSaved(): bool {
		return $this->sessionSaved;
	}

	public function whenSessionSaved(): ?\Closure {
		return $this->whenSessionSaved;
	}

	public function setWhenSessionSaved(?\Closure $closure = null): void {
		$this->whenSessionSaved = $closure;
	}

	public function setGameSessionSaved(bool $saved = true): void {
		$this->gameSessionSaved = $saved;
	}

	public function hasGameSessionSaved(): bool {
		return $this->gameSessionSaved;
	}

	public function whenGameSessionSaved(): ?\Closure {
		return $this->whenGameSessionSaved;
	}

	public function setWhenGameSessionSaved(?\Closure $closure = null): void {
		$this->whenGameSessionSaved = $closure;
	}

	public function isLoaded(): bool {
		return $this->loaded;
	}

	public function setLoaded(bool $loaded = true): void {
		$this->loaded = $loaded;
		if ($loaded) {
			$this->spawnToAll();
			$this->getInventory()->getListeners()->add($invlisten = new PlayerInventoryListener($this), $convertInvListen = new ItemConversionListener($this));
			$this->getArmorInventory()->getListeners()->add($invlisten, $convertInvListen);
			$this->getCursorInventory()->getListeners()->add($invlisten, $convertInvListen);
			$this->getEnderInventory()->getListeners()->add(new PlayerEnderInventoryListener($this), $convertInvListen);
			$convertInvListen->onContentChange($this->getInventory(), []);
			$convertInvListen->onContentChange($this->getArmorInventory(), []);
			$convertInvListen->onContentChange($this->getCursorInventory(), []);
			$convertInvListen->onContentChange($this->getEnderInventory(), []);
		}
	}

	public function hasPreLoadAction(): bool {
		return count($this->preLoadActions) > 0;
	}

	/** @return LoadAction[] */
	public function getPreLoadActions(): array {
		return $this->preLoadActions;
	}

	/** @return LoadAction[] */
	public function addPreLoadAction(LoadAction $loadAction): void {
		$this->preLoadActions[] = $loadAction;
	}

	public function removePreLoadAction(LoadAction $loadAction): void {
		$key = array_search($loadAction, $this->preLoadActions, true);
		if ($key !== false) {
			unset($this->preLoadActions[$key]);
		}
	}

	public function hasLoadAction(): bool {
		return count($this->loadActions) > 0;
	}

	/** @return LoadAction[] */
	public function getLoadActions(): array {
		return $this->loadActions;
	}

	public function addLoadAction(LoadAction $loadAction): void {
		$this->loadActions[] = $loadAction;
	}

	public function removeLoadAction(LoadAction $loadAction): void {
		$key = array_search($loadAction, $this->loadActions, true);
		if ($key !== false) {
			unset($this->loadActions[$key]);
		}
	}

	public function getUser(): ?User {
		if (!is_null($this->user)) {
			if ($this->user->xuid !== (int)$this->getXuid()) $this->user->xuid = (int)$this->getXuid();
			if ($this->user->gamertag !== (string)$this->getName()) $this->user->gamertag = (string)$this->getName();
		}
		return $this->user;
	}

	public function setUser(User $user): void {
		$this->user = $user;
	}

	public function isFirstConnection(): bool {
		return $this->firstConnection;
	}

	public function setFirstConnection(bool $first = true): void {
		$this->firstConnection = $first;
	}

	/**
	 * For use with proxy
	 */
	public function getIp(): string {
		return $this->ipAddress;
	}

	public function setIp(string $ip = "127.0.0.1"): void {
		$this->ipAddress = $ip;
	}

	//Login packet stuff
	public function getConnectedFrom(): string {
		return $this->connected_from;
	}

	public function getDeviceId(): string {
		return $this->clientId; //TODO: Change name after moving all direct calls to this function
	}

	public function getDeviceModel(): string {
		return $this->clientData["DeviceModel"] ?? "unknown";
	}

	public function getDeviceOS(): string {
		return $this->clientData["DeviceOS"] ?? "unknown";
	}

	public function getDeviceOSname(): string {
		return self::DEVICE_NAMES[$this->getDeviceOS()] ?? "?";
	}

	public function getDeviceOSnameA(): string {
		$os = $this->getDeviceOS();
		return "?" . $os;
	}

	public function getGuiScale(): int {
		return $this->clientData["GuiScale"] ?? 0;
	}

	public function getUiProfile(): int {
		return $this->clientData["UIProfile"] ?? 0;
	}

	public function isTransferring(): bool {
		return $this->transferring !== "" && $this->getLastTransferAttempt() + 15 > time();
	}

	public function setTransferring(string $id = ""): void {
		$this->transferring = $id;
		$this->lastTransferAttempt = time();
	}

	public function getLastTransferAttempt(): int {
		return $this->lastTransferAttempt;
	}

	public function getTransferring(): string {
		return $this->transferring;
	}

	public function getTransferredFrom(): string {
		return $this->transferredFrom;
	}

	public function setTransferredFrom(string $from): void {
		$this->transferredFrom = $from;
	}

	public function getSession(): ?CoreSession {
		return Core::getInstance()->getSessionManager()->getSession($this);
	}

	public function isMuted(): bool {
		return $this->getSession()?->getStaff()->getMuteManager()->isMuted() ?? false;
	}

	public function getRank(): string {
		return $this->getSession() !== null ? $this->getSession()->getRank()->getRank() : "default";
	}

	public function hasRank(): bool {
		return $this->getRank() !== "default";
	}

	public function setRank(string $rank): void {
		if ($this->getSession() !== null && $this->getSession()->isLoaded()) {
			$this->getSession()->getRank()->setRank($rank);
			$this->updateChatFormat();
			$this->updateNametag();
		} else {
			Core::getInstance()->getUserPool()->useUser($this->getName(), function (User $user) use ($rank): void {
				Core::getInstance()->getSessionManager()->useSession($user, function (CoreSession $session) use ($rank): void {
					if (!$this->isConnected()) return;
					$session->getRank()->setRank($rank);
					$this->updateChatFormat();
					$this->updateNametag();
				});
			});
		}
	}

	public function getRankHierarchy(?string $rank = null): int {
		if (is_null($rank) && $this->isTier3()) return PHP_INT_MAX;
		return RankStructure::RANK_HIERARCHY[strtolower($rank ?? $this->getRank())] ?? 0;
	}

	public function rankAtLeast(string $rank): bool {
		return $this->getRankHierarchy() >= $this->getRankHierarchy($rank);
	}

	public function getDisguise(): ?Disguise {
		return $this->getSession()?->getRank()->getDisguise();
	}

	public function isDisguiseEnabled(): bool {
		return ($this->getDisguise()?->isEnabled() ?? false) && $this->isStaff();
	}

	public function hasJoinMessage(): bool {
		return $this->getSession()->getSettings()->getSetting(GlobalSettings::JOIN_MESSAGE);
	}

	public function isSn3ak(): bool {
		return in_array($this->getName(), ["sn3akrr", "DooDooCrumbz"]);
	}

	public function isStaff(string $rank = ""): bool {
		return Core::getInstance()->getStaff()->isStaff($this, $rank);
	}

	public function isTier3(): bool {
		return Core::getInstance()->getStaff()->isTier3($this);
	}

	public function isTrainee(): bool {
		return $this->getRank() == "trainee";
	}

	public function isBuilder(): bool {
		return $this->getRank() == "builder";
	}

	#region Formatting
	public function updateChatFormat(): void {
		Core::getInstance()->getChat()->updateChatFormat($this);
	}

	public function updateNametagFormat(): void {
		Core::getInstance()->getChat()->updateNametagFormat($this);
	}

	public function getNewNametag(): string {
		return Core::getInstance()->getChat()->getNametagFormat($this);
	}

	public function updateNametag(): void {
		$this->setNametag($this->getNewNametag());
	}
	#endregion

	/** 
	 * @internal
	 * Handling the pre login info to cooperate with WaterDog
	 */
	public function handlePreLogin(PlayerInfo $info): void {
		$address = strtolower(explode(":", ($info->getExtraData()["ServerAddress"] ?? "play.avengetech.net:19132"))[0]);
		$this->connected_from = $address;

		$this->clientData = $info->getExtraData();
		$this->clientId = $cid = $info->getExtraData()["DeviceId"];
		$this->uuid = $uuid = $info->getUuid();
		$this->username = $username = $info->getUsername();

		if (!$info instanceof XboxLivePlayerInfo || isset($info->getExtraData()["Waterdog_IP"])) {
			$this->fromProxy = true;
			if (isset($info->getExtraData()["Waterdog_IP"])) {
				$this->setIp($info->getExtraData()["Waterdog_IP"]);
				$this->xuid = $info->getExtraData()["Waterdog_XUID"];
			} else {
				$this->setPlayerInfoLoaded(false);
			}
			return;
		}
		$this->xuid = $info->getXuid();
	}

	#region UI Controls
	public function showModal(CustomUI $modalWindow, bool $settings = false): void {
		if (microtime(true) - $this->lastModalOpened < 0.25) return;
		$pk = ($settings ? new ServerSettingsResponsePacket() : new ModalFormRequestPacket());
		$pk->formId = $id = ++$this->lastModalId;
		$pk->formData = $modalWindow->toJSON();
		$this->getNetworkSession()->sendDataPacket($pk);
		$this->activeModalWindows[$id] = $modalWindow;
		$this->lastModalOpened = microtime(true);
	}

	public function handleModalFormResponse(ModalFormResponsePacket $packet): bool {
		$id = $packet->formId;
		$data = $packet->formData;
		if (!empty($this->activeModalWindows) && isset($this->activeModalWindows[$id])) {
			$window = $this->activeModalWindows[$id];
			unset($this->activeModalWindows[$id]);
			if ($data === null) {
				$window->close($this);
			} else {
				$data = json_decode($data, true);

				if ($window instanceof CustomForm) {
					if (!is_array($data)) {
						$window->close($this);
						return true;
					}

					$data = $window::verifyData($window, $data, $this);
				}
				$window->handle($data, $this);
			}
			return true;
		}
		return false;
	}
	#endregion

	public function addEffect(EffectInstance $effect): bool {
		if ($this->getEffects()->has($effect->getType())) {
			$stackable = [3];
			$e = $this->getEffects()->get($effect->getType());
			if ($e->getEffectLevel() == $effect->getEffectLevel() && in_array(spl_object_id($effect->getType()), $stackable)) {
				$duration = $e->getDuration() + $effect->getDuration();
				$effect->setDuration($duration);
			}
		}
		return parent::getEffects()->add($effect);
	}

	public function playSound(string $name, Vector3 $pos = null, int $volume = 100, float $pitch = 1) {
		if ($pos == null) $pos = $this->getPosition()->add(0, 6, 0);
		$pk = new PlaySoundPacket();
		$pk->soundName = $name;
		$pk->x = (int) $pos->getX();
		$pk->y = (int) $pos->getY();
		$pk->z = (int) $pos->getZ();
		$pk->volume = $volume;
		$pk->pitch = $pitch;
		$this->getNetworkSession()->sendDataPacket($pk);
	}

	/**
	 * Toggles the player's vanished state
	 */
	public function toggleVanish(): bool {
		$vanished = $this->isVanished();
		$this->setVanished(!$vanished);
		return !$vanished;
	}

	public function setVanished(bool $bool = true): void {
		$this->vanished = $bool;
		if ($bool) {
			foreach ($this->getServer()->getOnlinePlayers() as $player) {
				/** @var AtPlayer $player */
				if (!$player->isStaff()) {
					$player->getNetworkSession()->onPlayerRemoved($this);
					$this->despawnFrom($player);
				}
			}
		} else {
			foreach ($this->getServer()->getOnlinePlayers() as $player) {
				/** @var AtPlayer $player */
				if (!$player->isStaff()) {
					$player->getNetworkSession()->onPlayerAdded($this);
					$this->spawnTo($player);
				}
			}
		}
		$this->updateNametagFormat();
		$this->updateNametag();
	}

	public function isVanished(): bool {
		return $this->vanished;
	}

	/**
	 * @internal
	 */
	public function spawnTo(Player $player): void {
		if (Core::thisServer()->getId() == "idle-1" || !$this->isLoaded()) {
			return;
		}
		/** @var self $player */
		if (
			!Core::getInstance()->getTutorials()->inTutorial($this) &&
			(!$this->isVanished() || $player->isStaff())
		) {
			parent::spawnTo($player);
		} elseif (!$player->isStaff()) {
			$player->getNetworkSession()->onPlayerRemoved($this);
		}
	}

	/**
	 * @internal
	 */
	public function knockBack(float $x, float $z, float $force = 0.4, ?float $verticalLimit = 0.4): void {
		$f = sqrt($x * $x + $z * $z);
		if ($f <= 0) {
			return;
		}
		if (mt_rand() / mt_getrandmax() > $this->getAttributeMap()->get(Attribute::KNOCKBACK_RESISTANCE)->getValue()) {
			$f = 1 / $f;
			$motion = clone $this->motion;
			$motion->x /= 2;
			$motion->y /= 2;
			$motion->z /= 2;
			$motion->x += $x * $f * $force;
			$motion->y += $force;
			$motion->z += $z * $f * $force;
			if ($motion->y > $force) {
				$motion->y = $force;
			}
			$this->setMotion($motion);
		}
	}

	/**
	 * @internal
	 * Called after EntityDamageEvent execution to apply post-hurt effects, such as reducing absorption or modifying
	 * armour durability.
	 * This will not be called by damage sources causing death.
	 */
	protected function applyPostDamageEffects(EntityDamageEvent $source): void {
		$this->setAbsorption(max(0, $this->getAbsorption() + $source->getModifier(EntityDamageEvent::MODIFIER_ABSORPTION)));
		$this->damageArmor($source->getBaseDamage());
	}

	public function teleport(Vector3 $pos, ?float $yaw = null, ?float $pitch = null): bool {
		$this->lastTeleportTick = $this->getServer()->getTick();
		if (parent::teleport($pos, $yaw, $pitch)) {

			$this->removeCurrentWindow();
			$this->stopSleep();

			$this->sendPosition($this->location, $this->location->yaw, $this->location->pitch, MovePlayerPacket::MODE_TELEPORT);
			$this->broadcastMovement(true);

			$this->spawnToAll();

			$this->resetFallDistance();
			$this->nextChunkOrderRun = 0;
			if ($this->spawnChunkLoadCount !== -1) {
				$this->spawnChunkLoadCount = 0;
			}
			$this->newBlockBreakHandler = null;

			//TODO: workaround for player last pos not getting updated
			//Entity::updateMovement() normally handles this, but it's overridden with an empty function in Player
			$this->resetLastMovements();


			return true;
		}
		return false;
	}

	/**
	 * @deprecated
	 * Teleports with a smooth animation sent to the network
	 */
	public function teleportSmooth(Vector3 $pos, ?float $yaw = null, ?float $pitch = null): void {
		$this->getNetworkSession()->syncMovement($pos, $yaw, $pitch); //todo: fix
	}

	/*
	Player::canInteract() {
		$eyePos = $this->getEyePos();
		if($eyePos->distanceSquared($pos) > $maxDistance ** 2){
			return false;
		}

		$dV = $this->getDirectionVector();
		$eyeDot = $dV->dot($eyePos);
		$targetDot = $dV->dot($pos);
		return ($targetDot - $eyeDot) >= -$maxDiff;
	}
	*/

	/**
	 * @internal
	 * Processes damage without sending certain packets that may cause odd client-sided damage animation issues
	 */
	public function processDamageSilent(float $damage): void {
		if ($this instanceof PrisonPlayer) {
			$combat = $this->getGameSession()->getCombat()->getTagger();
			if ($this->getHealth() <= $damage) {
				if ($combat instanceof PrisonPlayer && $combat->isAlive() && $combat->isConnected()) {
					Prison::getInstance()->getCombat()->processKill($combat, $this);
				} else {
					Prison::getInstance()->getCombat()->processSuicide($this);
				}
			} else {
				$this->setHealth($this->getHealth() - $damage);
			}
		} elseif ($this instanceof SkyBlockPlayer) {
			$combat = $this->getGameSession()->getCombat();
			/** @var SkyBlockPlayer $tagged */
			$tagged = $combat->getCombatMode()->getHit();
			if ($this->getHealth() <= $damage) {
				if ($combat->canCombat($tagged) && $tagged->isAlive() && $tagged->isConnected()) {
					$tagged->getGameSession()->getCombat()->kill($this);
				} else {
					$combat->suicide();
				}
			} else {
				if ($this->getGameSession()?->getEnchantments()->isAbsorbing()) {
					$this->getGameSession()->getEnchantments()->addAbsorbDamage($damage);
				}
				$this->setHealth($this->getHealth() - $damage);
			}
		}
		$this->doHitAnimation();
	}

	public function clientBreakBlock(Vector3 $pos): bool {
		if (Core::thisServer()->isIdle()) return false;

		$block = $this->getWorld()->getBlock($pos);

		// Possibly validates block breaks were done properly (anti-instamine)
		if (!$block->getBreakInfo()->breaksInstantly() && is_null($this->newBlockBreakHandler) && !$this->isCreative()) {
			$tmpHandler = new SurvivalBlockBreakHandler($this, $pos, $block, Facing::DOWN, 16);
			if ($tmpHandler->getBreakProgress() < 1) return false;
		}

		return $this->breakBlock($pos);
	}

	/**
	 * Breaks the block at the given position using the currently-held item.
	 *
	 * @return bool if the block was successfully broken, false if a rollback needs to take place.
	 */
	public function breakBlock(Vector3 $pos): bool {
		if (Core::thisServer()->isIdle() || $this->isAFK()) return false;
		$this->removeCurrentWindow();

		if ($this->canInteract($pos->add(0.5, 0.5, 0.5), ($this->isTier3() ? 100 : (($this->getAntiCheatSession()?->isMobile() || $this->getAntiCheatSession()?->isConsole()) ? self::MAX_REACH_DISTANCE * 1.15 : self::MAX_REACH_DISTANCE)))) {
			$this->broadcastAnimation(new ArmSwingAnimation($this), $this->getViewers());
			$this->stopBreakBlock($pos);
			$item = $this->inventory->getItemInHand();
			$oldItem = clone $item;
			$returnedItems = [];
			if ($this->getWorld()->useBreakOn($pos, $item, $this, true, $returnedItems)) {
				$this->returnItemsFromAction($oldItem, $item, $returnedItems);
				$this->hungerManager->exhaust(0.005, PlayerExhaustEvent::CAUSE_MINING);
				return true;
			}
		} else {
			$this->logger->debug("Cancelled block break at $pos due to not currently being interactable");
		}

		return false;
	}

	/**
	 * Performs a left-click (attack) action on the block.
	 *
	 * @return bool if an action took place successfully
	 */
	public function attackBlock(Vector3 $pos, int $face): bool {
		if (Core::thisServer()->isIdle() || $this->isAFK()) return false;
		if ($pos->distanceSquared($this->location) > 10000) {
			return false; //TODO: maybe this should throw an exception instead?
		}

		$target = $this->getWorld()->getBlock($pos);

		$ev = new PlayerInteractEvent($this, $this->inventory->getItemInHand(), $target, null, $face, PlayerInteractEvent::LEFT_CLICK_BLOCK);
		if ($this->isSpectator()) {
			$ev->cancel();
		}
		$ev->call();
		if ($ev->isCancelled()) {
			return false;
		}
		$this->broadcastAnimation(new ArmSwingAnimation($this), $this->getViewers());
		if ($target->onAttack($this->inventory->getItemInHand(), $face, $this)) {
			return true;
		}

		$block = $target->getSide($face);
		if ($block->hasTypeTag(BlockTypeTags::FIRE)) {
			$this->getWorld()->setBlock($block->getPosition(), VanillaBlocks::AIR());
			$this->getWorld()->addSound($block->getPosition()->add(0.5, 0.5, 0.5), new FireExtinguishSound());
			return true;
		}

		if (!$this->isCreative() && !$block->getBreakInfo()->breaksInstantly()) {
			$this->newBlockBreakHandler = new SurvivalBlockBreakHandler($this, $pos, $target, $face, 16);
		}

		return true;
	}

	public function continueBreakBlock(Vector3 $pos, int $face): void {
		if ($this->newBlockBreakHandler !== null && $this->newBlockBreakHandler->getBlockPos()->distanceSquared($pos) < 0.0001) {
			$this->newBlockBreakHandler->setTargetedFace($face);
		}
	}

	public function stopBreakBlock(Vector3 $pos): void {
		if ($this->newBlockBreakHandler !== null && $this->newBlockBreakHandler->getBlockPos()->distanceSquared($pos) < 0.0001) {
			$this->newBlockBreakHandler = null;
		}
	}

	public function onUpdate(int $currentTick): bool {
		$tickDiff = $currentTick - $this->lastUpdate;

		if ($tickDiff <= 0) {
			return true;
		}

		$this->messageCounter = 2;

		$this->lastUpdate = $currentTick;

		if ($this->justCreated) {
			$this->onFirstUpdate($currentTick);
		}

		if (!$this->isAlive() && $this->spawned) {
			$this->onDeathUpdate($tickDiff);
			return true;
		}

		$this->timings->startTiming();

		if ($this->spawned) {
			Timings::$playerMove->startTiming();
			$this->processMostRecentMovements();
			$this->motion = Vector3::zero(); //TODO: HACK! (Fixes player knockback being messed up)
			if ($this->onGround) {
				$this->inAirTicks = 0;
			} else {
				$this->inAirTicks += $tickDiff;
			}
			Timings::$playerMove->stopTiming();

			Timings::$entityBaseTick->startTiming();
			$this->entityBaseTick($tickDiff);
			Timings::$entityBaseTick->stopTiming();

			if ($this->isCreative() && $this->fireTicks > 1) {
				$this->fireTicks = 1;
			}

			if (!$this->isSpectator() && $this->isAlive()) {
				Timings::$playerCheckNearEntities->startTiming();
				$this->checkNearEntities();
				Timings::$playerCheckNearEntities->stopTiming();
			}

			if ($this->newBlockBreakHandler !== null && !$this->newBlockBreakHandler->update()) {
				$this->breakBlock($this->newBlockBreakHandler->getBlockPos());
				$this->newBlockBreakHandler = null;
			}

			$rh = $this->getRankHierarchy();
			switch (true) {
				case $rh >= 9: // Staff
				case $this->getSession()?->getRank()->hasSub(): // Warden
					$afkTime = PHP_INT_MAX;
					break;
				case $rh >= 6: // Enderdragon
					$afkTime = 30;
					break;
				case $rh >= 5: // Wither
					$afkTime = 25;
					break;
				case $rh >= 4: // Enderman
					$afkTime = 20;
					break;
				case $rh >= 3: // Ghast
					$afkTime = 15;
					break;
				case $rh >= 2: // Blaze
					$afkTime = 10;
					break;
				case $rh >= 1: // Endermite
					$afkTime = 5;
					break;
				default:
					$afkTime = 2;
					break;
			}

			// $afkTime = 0.1; // debug afktime

			if (Server::getInstance()->getTick() - $this->lastNonAfkTick > 20 * 60 * $afkTime && $this->isLoaded()) {
				if (!$this->isAFK()) {
					$this->getEffects()->add(new EffectInstance(VanillaEffects::BLINDNESS(), 180, 255, false, false));
					$this->forceTitle(TextFormat::BOLD . TextFormat::RED . "Still There?", TextFormat::GRAY . "You have been marked AFK", 15, 130, 15);
				}
				$this->setAFK();
			} else {
				if ($this->isAFK()) {
					$this->getEffects()->remove(VanillaEffects::BLINDNESS());
					$this->forceTitle(" ", " ", 1, 1, 1);
				}
				$this->setAFK(false);
			}

			if ($this->isAFK() && (Server::getInstance()->getTick() - $this->lastNonAfkTick) % 160 === 0) {
				$this->getEffects()->add(new EffectInstance(VanillaEffects::BLINDNESS(), 180, 255, false, false));
				$this->forceTitle(TextFormat::BOLD . TextFormat::RED . "Still There?", TextFormat::GRAY . "You have been marked AFK", 15, 130, 15);
			}
		}

		$this->timings->stopTiming();

		return true;
	}

	public function forceTitle(string $title, string $subtitle = "", int $fadeIn = -1, int $stay = -1, int $fadeOut = -1): void {
		parent::sendTitle($title, $subtitle, $fadeIn, $stay, $fadeOut);
	}

	public function sendTitle(string $title, string $subtitle = "", int $fadeIn = -1, int $stay = -1, int $fadeOut = -1): void {
		if ($this->isAFK()) return;
		parent::sendTitle($title, $subtitle, $fadeIn, $stay, $fadeOut);
	}

	/**
	 * @internal
	 * This method executes post-disconnect actions and cleanups.
	 *
	 * @param Translatable|string      $reason      Shown in the server log - this should be a short one-line message
	 * @param Translatable|string|null $quitMessage Message to broadcast to online players (null will use default)
	 */
	public function onPostDisconnect(Translatable|string $reason, Translatable|string|null $quitMessage): void {
		if ($this->isConnected()) {
			throw new \LogicException("Player is still connected");
		}

		//prevent the player receiving their own disconnect message
		$this->server->unsubscribeFromAllBroadcastChannels($this);

		$this->removeCurrentWindow();

		$ev = new PlayerQuitEvent($this, $quitMessage ?? $this->getLeaveMessage(), $reason);
		$ev->call();
		if (($quitMessage = $ev->getQuitMessage()) !== "") {
			$this->server->broadcastMessage($quitMessage);
		}
		$this->save();

		$this->spawned = false;

		$this->stopSleep();
		$this->newBlockBreakHandler = null;
		$this->despawnFromAll();

		$this->server->removeOnlinePlayer($this);

		foreach ($this->server->getOnlinePlayers() as $player) {
			if (!$player->canSee($this)) {
				$player->showPlayer($this);
			}
		}
		$this->hiddenPlayers = [];

		if ($this->location->isValid()) {
			foreach ($this->usedChunks as $index => $status) {
				World::getXZ($index, $chunkX, $chunkZ);
				$this->unloadChunk($chunkX, $chunkZ);
			}
		}
		if (count($this->usedChunks) !== 0) {
			throw new AssumptionFailedError("Previous loop should have cleared this array");
		}
		$this->loadQueue = [];

		$this->removeCurrentWindow();
		$this->removePermanentInventories();

		$this->perm->getPermissionRecalculationCallbacks()->clear();

		$this->flagForDespawn();
	}

	protected function destroyCycles(): void {
		$this->networkSession = null;
		unset($this->cursorInventory);
		unset($this->craftingGrid);
		$this->spawnPosition = null;
		$this->deathPosition = null;
		$this->newBlockBreakHandler = null;
		parent::destroyCycles();
	}

	public function attackEntity(Entity $entity): bool {
		if (Core::thisServer()->isIdle()) return false;
		if (!$entity->isAlive()) {
			return false;
		}
		if ($entity instanceof ItemEntity || $entity instanceof Arrow) {
			$this->logger->debug("Attempted to attack non-attackable entity " . get_class($entity));
			return false;
		}

		$heldItem = $this->inventory->getItemInHand();
		$oldItem = clone $heldItem;

		$ev = new EntityDamageByEntityEvent($this, $entity, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $heldItem->getAttackPoints());
		if (!$this->canInteract($entity->getLocation(), ($this->isTier3() ? 100 : (($this->getAntiCheatSession()?->isMobile() || $this->getAntiCheatSession()?->isConsole()) ? self::MAX_REACH_DISTANCE * 1.15 : self::MAX_REACH_DISTANCE)))) {
			$this->logger->debug("Cancelled attack of entity " . $entity->getId() . " due to not currently being interactable");
			$ev->cancel();
		} elseif ($this->isSpectator() || ($entity instanceof Player && !$this->server->getConfigGroup()->getConfigBool(ServerProperties::PVP))) {
			$ev->cancel();
		}

		$meleeEnchantmentDamage = 0;
		/** @var EnchantmentInstance[] $meleeEnchantments */
		$meleeEnchantments = [];
		foreach ($heldItem->getEnchantments() as $enchantment) {
			$type = $enchantment->getType();
			if ($type instanceof MeleeWeaponEnchantment && $type->isApplicableTo($entity)) {
				$meleeEnchantmentDamage += $type->getDamageBonus($enchantment->getLevel());
				$meleeEnchantments[] = $enchantment;
			}
		}
		$ev->setModifier($meleeEnchantmentDamage, EntityDamageEvent::MODIFIER_WEAPON_ENCHANTMENTS);

		if (!$this->isSprinting() && !$this->isFlying() && $this->fallDistance > 0 && !$this->effectManager->has(VanillaEffects::BLINDNESS()) && !$this->isUnderwater()) {
			$ev->setModifier($ev->getFinalDamage() / 2, EntityDamageEvent::MODIFIER_CRITICAL);
		}

		$entity->attack($ev);
		$this->broadcastAnimation(new ArmSwingAnimation($this), $this->getViewers());

		$soundPos = $entity->getPosition()->add(0, $entity->size->getHeight() / 2, 0);
		if ($ev->isCancelled()) {
			$this->getWorld()->addSound($soundPos, new EntityAttackNoDamageSound());
			return false;
		}
		$this->getWorld()->addSound($soundPos, new EntityAttackSound());

		if ($ev->getModifier(EntityDamageEvent::MODIFIER_CRITICAL) > 0 && $entity instanceof Living) {
			$entity->broadcastAnimation(new CriticalHitAnimation($entity));
		}

		foreach ($meleeEnchantments as $enchantment) {
			$type = $enchantment->getType();
			assert($type instanceof MeleeWeaponEnchantment);
			$type->onPostAttack($this, $entity, $enchantment->getLevel());
		}

		if ($this->isAlive()) {
			//reactive damage like thorns might cause us to be killed by attacking another mob, which
			//would mean we'd already have dropped the inventory by the time we reached here
			$returnedItems = [];
			$heldItem->onAttackEntity($entity, $returnedItems);
			$this->returnItemsFromAction($oldItem, $heldItem, $returnedItems);

			$this->hungerManager->exhaust(0.1, PlayerExhaustEvent::CAUSE_ATTACK);
		}

		return true;
	}

	public function interactBlock(Vector3 $pos, int $face, Vector3 $clickOffset): bool {
		if (Core::thisServer()->isIdle() || $this->isAFK()) return false;
		$this->setUsingItem(false);

		if ($this->canInteract($pos->add(0.5, 0.5, 0.5), ($this->isTier3() ? 100 : (($this->getAntiCheatSession()?->isMobile() || $this->getAntiCheatSession()?->isConsole()) ? self::MAX_REACH_DISTANCE * 1.15 : self::MAX_REACH_DISTANCE)))) {
			$this->broadcastAnimation(new ArmSwingAnimation($this), $this->getViewers());
			$item = $this->inventory->getItemInHand(); //this is a copy of the real item
			$oldItem = clone $item;

			$returnedItems = [];
			if ($this->getWorld()->useItemOn($pos, $item, $face, $clickOffset, $this, true, $returnedItems)) {
				if (count($returnedItems) > 0) {
					$blockAt = $this->getWorld()->getBlock($pos);
					//Utils::dumpVals($this->getName(), "Failed to place " . count($returnedItems) . " block(s) against " . $blockAt->getName() . ". Sneaking?: " . ($this->getAntiCheatSession()->isSneaking() ? "Y" : "N"));
				}
				$this->returnItemsFromAction($oldItem, $item, $returnedItems);
				$this->badInteractions = max(0, $this->badInteractions - 2);
				return true;
			}
		} else {
			$dist = $pos->add(0.5, 0.5, 0.5)->distance($eyePos = $this->getEyePos());
			$this->logger->debug("Cancelled interaction of block at $pos due to not currently being interactable");
			if (($dist > ($this->getAntiCheatSession()?->isMobile() || $this->getAntiCheatSession()?->isConsole()) ? self::MAX_REACH_DISTANCE * 1.15 : self::MAX_REACH_DISTANCE) && !$this->isCreative()) {
				$this->badInteractions++;
			}
		}

		return false;
	}

	/**
	 * @param Item[] $extraReturnedItems
	 */
	private function returnItemsFromAction(Item $oldHeldItem, Item $newHeldItem, array $extraReturnedItems): void {
		$heldItemChanged = false;

		if (!$newHeldItem->equalsExact($oldHeldItem) && $oldHeldItem->equalsExact($this->inventory->getItemInHand())) {
			//determine if the item was changed in some meaningful way, or just damaged/changed count
			//if it was really changed we always need to set it, whether we have finite resources or not
			$newReplica = clone $oldHeldItem;
			$newReplica->setCount($newHeldItem->getCount());
			if ($newReplica instanceof Durable && $newHeldItem instanceof Durable) {
				$newReplica->setDamage($newHeldItem->getDamage());
			}
			$damagedOrDeducted = $newReplica->equalsExact($newHeldItem);

			if (!$damagedOrDeducted || $this->hasFiniteResources()) {
				if ($newHeldItem instanceof Durable && $newHeldItem->isBroken()) {
					$this->broadcastSound(new ItemBreakSound());
				}
				$this->inventory->setItemInHand($newHeldItem);
				$heldItemChanged = true;
			}
		}

		if (!$heldItemChanged) {
			$newHeldItem = $oldHeldItem;
		}

		if ($heldItemChanged && count($extraReturnedItems) > 0 && $newHeldItem->isNull()) {
			$this->inventory->setItemInHand(array_shift($extraReturnedItems));
		}
		foreach ($this->inventory->addItem(...$extraReturnedItems) as $drop) {
			//TODO: we can't generate a transaction for this since the items aren't coming from an inventory :(
			$ev = new PlayerDropItemEvent($this, $drop);
			if ($this->isSpectator()) {
				$ev->cancel();
			}
			$ev->call();
			if (!$ev->isCancelled()) {
				$this->dropItem($drop);
			}
		}
	}

	/**
	 * @deprecated
	 * Send this after teleporting to a player
	 */
	public function teleportProcess(Player $player): void {
	}

	public function transferTo(string $identifier): void {
	}

	public function getSeeInv(): ?SeeinvInventory {
		return $this->getSession()?->seeInv;
	}

	public function getEnderInv(): ?EnderinvInventory {
		return $this->getSession()?->enderInv;
	}

	public function sendVpnCheck(string $ip = ""): void {
		Server::getInstance()->getAsyncPool()->submitTask(new VpnDetectTask($this, ($ip == "" ? ($this->isFromProxy() ? $this->getIp() : $this->getNetworkSession()->getIp()) : $ip)));
	}

	public function returnVpnCheck(array $data): bool {
		$info = $data[($used = $data["used_address"] ?? $this->getNetworkSession()->getIp())] ?? ["proxy" => "no"];
		if ($info["proxy"] == "yes" && !in_array(trim($used), VpnCache::IP_WHITELIST)) {
			$post = new Post("", "VPN Detection - " . ($id = Core::getInstance()->getNetwork()->getIdentifier()), "[REDACTED]", false, "", [
				new Embed("", "rich", "**" . $this->getName() . "** was detected using a **" . ($info["type"] ?? "VPN") . "**!", "", "ffb106", new Footer("Haxx0r has been neutralized"), "", "[REDACTED]", null, [
					new Field("Address", $used, true),
					new Field("Provider", ($info["provider"] ?? "unknown"), true),
				])
			]);
			$post->setWebhook(Webhook::getWebhookByName("vpn"));
			$post->send();

			$this->kickDelay(TextFormat::RED . "You cannot connect to AvengeTech using a VPN service! Please disable your VPN and try again", 40);
			Core::getInstance()->getVpnCache()->addEntry($used);
			return $this->vpnCheck = false;
		}

		Core::getInstance()->getVpnCache()->addEntry($used, false);
		return $this->vpnCheck = true;
	}

	public function hasVoted(): bool {
		return $this->voted;
	}

	public function setVoted(bool $voted = true): void {
		$this->voted = $voted;
	}

	public function getLastMessaged(): ?Player {
		return Server::getInstance()->getPlayerExact($this->getLastMessagedName());
	}

	public function getLastMessagedName(): string {
		return $this->lastMessaged;
	}

	public function setLastMessaged($player): void {
		$this->lastMessaged = $player instanceof Player ? $player->getName() : $player;
	}

	public function kickDelay(string $message, int $delay = 20): void {
		Core::getInstance()->getScheduler()->scheduleDelayedTask(new KickDelayTask($this, $message), $delay);
	}

	public function inFlightMode(): bool {
		return $this->flightMode;
	}

	/**
	 * Override in gamemode classes
	 *
	 * Should return true or error message
	 */
	public function canFly(): string|bool {
		if ($this->isSn3ak() || $this->isStaff() || $this->isTier3()) {
			return true;
		} else {
			return "You must be staff to fly here!";
		}
	}

	public function getAntiCheatSession(): ?Session {
		$this->anticheatSession ??= SessionManager::fetch()->getSessionFor($this);
		return $this->anticheatSession;
	}

	/**
	 * Some packet overrides to enforce kick text in our formats
	 */
	public function kickPlayer(AtPlayer|string $moderator, string $reason, bool $fromBan = false): bool {
		if ($moderator instanceof AtPlayer) $moderator = Core::getInstance()->getChat()->getFormattedRank($moderator->getRank()) . TextFormat::RESET . " " . TextFormat::YELLOW . $moderator->getName();
		else $moderator = TextFormat::YELLOW . $moderator;
		$this->getNetworkSession()->disconnect(TextFormat::RED . "Moderator: " . $moderator . TextFormat::RESET . PHP_EOL . TextFormat::RED . (!$fromBan ? ("Reason: " . TextFormat::AQUA . $reason) : $reason));
		return !$this->isConnected();
	}

	protected function initEntity(CompoundTag $nbt): void {
		parent::initEntity($nbt);

		$this->effectManager->clear();
	}

	protected function entityBaseTick(int $tickDiff = 1): bool {
		if ($this->badInteractions > 7 && false) {
			$this->kickPlayer(TextFormat::BOLD . TextFormat::AQUA . "Hy" . TextFormat::GOLD . "Tech" . TextFormat::AQUA . " AntiCheat", "Invalid block interactions");
			return false;
		}
		if (!is_null($this->getAntiCheatSession())) $this->setSneaking($this->getAntiCheatSession()->isSneaking());
		$this->updateOwnNametags(); // update nametags player-side
		$enchantmentRetick = $tickDiff - 1;
		if ($enchantmentRetick > 0) {
			$effects = $this->effectManager->all();
			for (; $enchantmentRetick > 0; $enchantmentRetick--) {
				foreach ($effects as $instance) {
					$type = $instance->getType();
					if ($type->canTick($instance)) {
						$type->applyEffect($this, $instance);
					}
				}
			}
		}
		return $this->parentBaseTicks($tickDiff);
	}

	/**
	 * Necessary override in order to harness the changed network properties & forcefully override the nametag and score tag
	 */
	protected function parentBaseTicks(int $tickDiff = 1): bool {
		// base entity
		if ($this->justCreated) {
			$this->justCreated = false;
			if (!$this->isAlive()) {
				$this->kill();
			}
		}

		$changedProperties = $this->getDirtyNetworkData();
		if (count($changedProperties) > 0) {
			$this->sendData([$this], $changedProperties);
			$this->networkProperties->clearDirtyProperties();
		}
		if (isset($changedProperties[EntityMetadataProperties::NAMETAG])) unset($changedProperties[EntityMetadataProperties::NAMETAG]);
		if (isset($changedProperties[EntityMetadataProperties::SCORE_TAG])) unset($changedProperties[EntityMetadataProperties::SCORE_TAG]);
		if (count($changedProperties) > 0) {
			$this->sendData(null, $changedProperties);
			$this->networkProperties->clearDirtyProperties();
		}

		$hasUpdate = false;

		if ($this->checkBlockIntersectionsNextTick) {
			$this->checkBlockIntersections();
		}
		$this->checkBlockIntersectionsNextTick = true;

		if ($this->location->y <= World::Y_MIN - 16 && $this->isAlive()) {
			$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_VOID, 10);
			$this->attack($ev);
			$hasUpdate = true;
		}

		if ($this->isOnFire() && $this->doOnFireTick($tickDiff)) {
			$hasUpdate = true;
		}

		if ($this->noDamageTicks > 0) {
			$this->noDamageTicks -= $tickDiff;
			if ($this->noDamageTicks < 0) {
				$this->noDamageTicks = 0;
			}
		}

		$this->ticksLived += $tickDiff;

		// living
		if ($this->isAlive()) {
			if ($this->effectManager->tick($tickDiff)) {
				$hasUpdate = true;
			}

			if ($this->isInsideOfSolid()) {
				$hasUpdate = true;
				$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_SUFFOCATION, 1);
				$this->attack($ev);
			}

			if ($this->doAirSupplyTick($tickDiff)) {
				$hasUpdate = true;
			}

			foreach ($this->armorInventory->getContents() as $index => $item) {
				$oldItem = clone $item;
				if ($item->onTickWorn($this)) {
					$hasUpdate = true;
					if (!$item->equalsExact($oldItem)) {
						$this->armorInventory->setItem($index, $item);
					}
				}
			}
		}

		if ($this->attackTime > 0) {
			$this->attackTime -= $tickDiff;
		}

		// human
		$this->hungerManager->tick($tickDiff);
		$this->xpManager->tick($tickDiff);

		return $hasUpdate;
	}

	/**
	 * This enforces the nametag and scoretag client side (Third-person nametag fixes for client-sided nametags)
	 */
	protected function syncNetworkData(EntityMetadataCollection $properties): void {
		parent::syncNetworkData($properties);

		if (!$this->isLoaded()) return;

		$legacyIcons = $this->getSession()?->getSettings()->getSetting(GlobalSettings::LEGACY_RANK_ICONS) ?? false;
		$inCombat = false;
		if ($this instanceof SkyBlockPlayer) {
			$inCombat = $this->getGameSession()?->getCombat()->getCombatMode()?->inCombat() ?? false;
		}
		if ($this instanceof PrisonPlayer) {
			$inCombat = $this->getGameSession()?->getCombat()->isTagged() ?? false;
		}

		$afkPrefix = TextFormat::GRAY . TextFormat::BOLD . "[AFK] " . TextFormat::RESET;
		if ($legacyIcons) {
			if (!isset(Core::getInstance()->getChat()->ntf[$this->getName()])) Core::getInstance()->getChat()->updateNametagFormat($this);
			$rank = $this->isDisguiseEnabled() ? $this->getDisguise()->getRank() : (($this->getSession()?->getRank()->hasSub() && !$this->isStaff()) ? "warden" : $this->getRank());
			$nametag = str_replace("{RANK}", TextFormat::BOLD . ChatStructure::LEGACY_RANK_FORMATS[strtolower($rank)] . TextFormat::RESET, $inCombat ? Core::getInstance()->getChat()->cntf[$this->getName()] : Core::getInstance()->getChat()->ntf[$this->getName()]);
		} else {
			$nametag = Core::getInstance()->getChat()->getNametagFormat($this, $inCombat);
		}
		if ($this->isAFK()) $nametag = $afkPrefix . $nametag;
		$properties->setString(EntityMetadataProperties::NAMETAG, $nametag);
		if ($this instanceof PrisonPlayer || $this instanceof SkyBlockPlayer) {
			$scoreTag = $this->getHealthBar($this, $inCombat);
			$properties->setString(EntityMetadataProperties::SCORE_TAG, $scoreTag);
		}
	}

	public function updateOwnNametags(bool $forceCombat = false): void {
		if (!$this->isLoaded()) return;
		$legacyIcons = $this->getSession()?->getSettings()->getSetting(GlobalSettings::LEGACY_RANK_ICONS) ?? false;
		$inCombat = false;
		if ($this instanceof SkyBlockPlayer) {
			$inCombat = $this->getGameSession()?->getCombat()->getCombatMode()?->inCombat() ?? false;
		}
		if ($this instanceof PrisonPlayer) {
			$inCombat = $this->getGameSession()?->getCombat()->isTagged() ?? false;
		}

		if ($forceCombat) $inCombat = true;

		/* SELF NAMETAG (For Clients with Third-Person Nametag) */
		$afkPrefix = TextFormat::GRAY . TextFormat::BOLD . "[AFK] " . TextFormat::RESET;
		if ($this->ticksLived % 55 === 0) Core::getInstance()->getChat()->updateNametagFormat($this); // update formats every 2.75 seconds | TODO: Update nametags anytime a change is made
		if ($legacyIcons) {
			$rank = $this->isDisguiseEnabled() ? $this->getDisguise()->getRank() : (($this->getSession()?->getRank()->hasSub() && !$this->isStaff()) ? "warden" : $this->getRank());
			$nametag = str_replace("{RANK}", TextFormat::BOLD . ChatStructure::LEGACY_RANK_FORMATS[strtolower($rank)] . TextFormat::RESET, $inCombat ? Core::getInstance()->getChat()->cntf[$this->getName()] : Core::getInstance()->getChat()->ntf[$this->getName()]);
		} else {
			$nametag = Core::getInstance()->getChat()->getNametagFormat($this, $inCombat);
		}
		if ($this->isAFK()) $nametag = $afkPrefix . $nametag;
		$metaData = [EntityMetadataProperties::NAMETAG => new StringMetadataProperty($nametag)];
		if ($this instanceof PrisonPlayer || $this instanceof SkyBlockPlayer) {
			$scoreTag = $this->getHealthBar($this, $inCombat);
			$metaData[EntityMetadataProperties::SCORE_TAG] = new StringMetadataProperty($scoreTag);
		}
		$this->sendData([$this], $metaData);
		/* */

		/* OTHER PLAYERS NAMETAGS */
		foreach ($this->getWorld()->getEntities() as $p) {
			if (!$p instanceof AtPlayer) continue;
			if (!$p->isLoaded() || $p->getXuid() === $this->getXuid()) continue;
			if ($this->ticksLived % 55 === 0) Core::getInstance()->getChat()->updateNametagFormat($p);
			$afkPrefix = TextFormat::GRAY . TextFormat::BOLD . "[AFK] " . TextFormat::RESET;
			if ($legacyIcons) {
				$rank = $p->isDisguiseEnabled() ? $p->getDisguise()->getRank() : (($p->getSession()?->getRank()->hasSub() && !$p->isStaff()) ? "warden" : $p->getRank());
				$nametag = str_replace("{RANK}", TextFormat::BOLD . ChatStructure::LEGACY_RANK_FORMATS[strtolower($rank)] . TextFormat::RESET, $inCombat ? Core::getInstance()->getChat()->cntf[$p->getName()] : Core::getInstance()->getChat()->ntf[$p->getName()]);
			} else {
				$nametag = Core::getInstance()->getChat()->getNametagFormat($p, $inCombat);
			}
			if ($p->isAFK()) $nametag = $afkPrefix . $nametag;
			$metaData = [EntityMetadataProperties::NAMETAG => new StringMetadataProperty($nametag)];
			if ($p instanceof PrisonPlayer || $p instanceof SkyBlockPlayer) {
				$scoreTag = $p->getHealthBar($this, $inCombat);
				$metaData[EntityMetadataProperties::SCORE_TAG] = new StringMetadataProperty($scoreTag);
			}
			$p->sendData([$this], $metaData);
		}
		/* */
	}

	public function getInputMode(): string {
		return match ($this->getAntiCheatSession()?->inputMode ?? -1) {
			InputMode::MOUSE_KEYBOARD => TextFormat::EMOJI_KEYBOARD,
			InputMode::TOUCHSCREEN => TextFormat::EMOJI_TOUCH,
			InputMode::GAME_PAD => TextFormat::EMOJI_CONTROLLER,
			InputMode::MOTION_CONTROLLER => TextFormat::EMOJI_WAVE,
			default => ""
		};
	}

	public function setFlightMode(bool $mode = true, ?GameMode $gamemode = null, bool $doubleJumpEnabled = false): void {
		$this->flightMode = $mode;
		if (!$mode) {
			$this->getNetworkSession()->sendDataPacket(SetPlayerGameTypePacket::create(TypeConverter::getInstance()->coreGameModeToProtocol(GameMode::CREATIVE())));
			$this->setGamemode(GameMode::CREATIVE());
			$this->setGamemode($gamemode ?? GameMode::ADVENTURE());
			if ($doubleJumpEnabled) $this->setAllowFlight(true);
		} else {
			$this->setAllowFlight(true);
		}
	}

	public function getLinks(): array {
		$links = [];
		if ($this->onSailingSub()) {
			$links[] = new EntityLink($this->getSailingSub()->getId(), $this->getId(), EntityLink::TYPE_RIDER, true, true, 0);
		}
		return $links;
	}

	public function getSailingSub(): ?SailingSub {
		return $this->sub;
	}

	public function setSailingSub(?SailingSub $sub = null): void {
		$this->sub = $sub;
	}

	public function onSailingSub(): bool {
		return $this->getSailingSub() !== null;
	}

	public function inTechieMode(): bool {
		return $this->techieMode && $this->techieModeExpire > time();
	}

	public function getTechieConversation(): ?Conversation {
		return $this->techieConversation;
	}

	public function setTechieMode(bool $techie = true): void {
		if (!$this->inTechieMode() && $techie) {
			$this->techieConversation = new Conversation(
				TStr::CONVERSATION_PROMPT
			);
		}
		if ($techie) {
			$this->techieModeExpire = time() + self::TECHIE_MODE_TIMEOUT;
		}
		$this->techieMode = $techie;
	}

	public function inLobby(): bool {
		return true;
	}

	public function setFrozen(bool $frozen = true): void {
		$this->frozen = $frozen;
		$this->setNoClientPredictions($frozen);
	}

	public function isFrozen(): bool {
		return $this->frozen;
	}

	public function toggleFrozen(): bool {
		$this->setFrozen(!$this->isFrozen());
		return $this->isFrozen();
	}

	public function isAFK(): bool {
		return $this->afkStatus;
	}

	public function setAFK(bool $afk = true): void {
		$this->afkStatus = $afk;
	}

	public function checkAFK(PlayerAuthInputPacket $packet): void {
		if (isset($this->afkData[Server::getInstance()->getTick()])) return;

		$inputFlags = $packet->getInputFlags();
		$movementKeys = [
			"up" => $inputFlags->get(PlayerAuthInputFlags::UP),
			"down" => $inputFlags->get(PlayerAuthInputFlags::DOWN),
			"left" => $inputFlags->get(PlayerAuthInputFlags::LEFT),
			"right" => $inputFlags->get(PlayerAuthInputFlags::RIGHT),
			"up_left" => $inputFlags->get(PlayerAuthInputFlags::UP_LEFT),
			"up_right" => $inputFlags->get(PlayerAuthInputFlags::UP_RIGHT),
			"down_left" => $inputFlags->get(PlayerAuthInputFlags::DOWN_LEFT),
			"down_right" => $inputFlags->get(PlayerAuthInputFlags::DOWN_RIGHT),
		];
		$sneaking = $inputFlags->get(PlayerAuthInputFlags::SNEAK_DOWN) || $inputFlags->get(PlayerAuthInputFlags::SNEAK_PRESSED_RAW) || $inputFlags->get(PlayerAuthInputFlags::SNEAK_TOGGLE_DOWN);
		$sprinting = $inputFlags->get(PlayerAuthInputFlags::SPRINT_DOWN) || $inputFlags->get(PlayerAuthInputFlags::SPRINTING);

		$rotation = [
			"pitch" => $packet->getPitch(),
			"yaw" => $packet->getYaw(),
			"head_yaw" => $packet->getHeadYaw(),
		];

		$compiled = [$movementKeys, $sneaking, $sprinting, $rotation];

		$this->afkData[Server::getInstance()->getTick()] = $compiled;

		if ($this->compareAFKIndex(Server::getInstance()->getTick() - 2) > 0) $this->lastNonAfkTick = Server::getInstance()->getTick();
	}

	protected function compareAFKIndex(int $from): float {
		if ($from === Server::getInstance()->getTick()) {
			return 0;
		}

		$diff = 0;

		$base = $this->afkData[Server::getInstance()->getTick() - 1] ?? null;

		if (is_null($base)) {
			return 0;
		}

		for ($i = Server::getInstance()->getTick() - 1; $i >= $from; $i--) {
			if (!isset($this->afkData[$i]) || $i === Server::getInstance()->getTick() - 1) continue;
			$data = $this->afkData[$i];
			$movementKeys = $data[0];
			$sneaking = $data[1];
			$sprinting = $data[2];
			$rotation = $data[3];

			foreach ($movementKeys as $key => $value) {
				if ($value !== $base[0][$key]) {
					$diff += 1;
				}
			}
			if ($sneaking !== $base[1]) {
				$diff += 1;
			}
			if ($sprinting !== $base[2]) {
				$diff += 1;
			}
			foreach ($rotation as $key => $value) {
				if (Server::getInstance()->getTick() - $this->lastTeleportTick <= 10) {
					continue;
				}
				if ($value !== $base[3][$key]) {
					$diff += 1;
				}
			}
			$base = $data;
		}

		return $diff / (Server::getInstance()->getTick() - $from - 1);
	}

	public function getLastMessage(): string {
		return $this->lastMessage;
	}

	public function setLastMessage(string $message): void {
		$this->lastMessage = $message;
	}

	protected function sendSpawnPacket(Player $player): void {
		/** @var AtPlayer $player */
		$links = $this->getLinks();

		($networkSession = $player->getNetworkSession())->sendDataPacket(AddPlayerPacket::create(
			$this->isDisguiseEnabled() ? $this->getDisguise()->uuid : $this->getUniqueId(),
			$this->isDisguiseEnabled() ? $this->getDisguise()->getName() : $this->getName(),
			$this->isDisguiseEnabled() ? $this->getDisguise()->id : $this->getId(),
			"",
			$this->location->asVector3(),
			$this->getMotion(),
			$this->location->pitch,
			$this->location->yaw,
			$this->location->yaw,
			ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet($this->getInventory()->getItemInHand())),
			GM::SURVIVAL,
			$this->getAllNetworkData(),
			new PropertySyncData([], []),
			UpdateAbilitiesPacket::create(new AbilitiesData(0, 0, $this->getId(), [])),
			$links,
			"",
			DeviceOS::UNKNOWN
		));

		$this->sendSkin([$player]);

		$legacyIcons = $player->getSession()?->getSettings()->getSetting(GlobalSettings::LEGACY_RANK_ICONS) ?? false;

		Core::getInstance()->getChat()->updateNametagFormat($this);
		if ($legacyIcons) {
			$rank = ($this->getSession()?->getRank()->hasSub() && !$this->isStaff()) ? "warden" : $this->getRank();
			$nametag = str_replace("{RANK}", TextFormat::BOLD . ChatStructure::LEGACY_RANK_FORMATS[strtolower($rank)] . TextFormat::RESET, Core::getInstance()->getChat()->ntf[$this->getName()]);
		} else {
			$nametag = $this->getNameTag();
		}

		$this->sendData([$player], [EntityMetadataProperties::NAMETAG => new StringMetadataProperty($nametag)]);

		$entityEventBroadcaster = $networkSession->getEntityEventBroadcaster();
		$entityEventBroadcaster->onMobArmorChange([$networkSession], $this);
		$entityEventBroadcaster->onMobOffHandItemChange([$networkSession], $this);

		if (count($links) > 0) {
			Core::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player, $links): void {
				if ($player->isConnected()) {
					foreach ($links as $link) {
						$pk = new SetActorLinkPacket();
						$pk->link = $link;
						$player->getNetworkSession()->sendDataPacket($pk);
					}
				}
			}), 10);
		}

		Core::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player): void {
			if (!$this->isDisguiseEnabled()) $this->getSession()?->getCosmetics()->sendAnimations($player, $this->getId());
		}), 20);
	}

	public function getEffectViewers(): array {
		$viewers = $this->getPosition()->getWorld()->getViewersForPosition($this->getPosition());
		/** @var self $viewer */
		foreach ($viewers as $key => $viewer) {
			if ($viewer->getName() !== $this->getName() && ($viewer->isLoaded() &&
				!$viewer->getSession()->getSettings()->getSetting(GlobalSettings::DISPLAY_COSMETIC_EFFECTS)
			)) {
				unset($viewers[$key]);
			}
		}
		return $viewers;
	}

	/**
	 * Enforces formatting based on the current player's chat format settings
	 */
	public function sendChatMessage(string $format, string $rank, Translatable|string $message, Translatable|string $preformatted): void {
		$legacyIcons = $this->getSession()?->getSettings()->getSetting(GlobalSettings::LEGACY_RANK_ICONS) ?? false;
		if ($message instanceof Translatable) $message = $message->getText();
		if ($preformatted instanceof Translatable) $preformatted = $preformatted->getText();

		if ($legacyIcons) {
			$m = str_replace(["{RANK}", "{MESSAGE}"], [TextFormat::BOLD . ChatStructure::LEGACY_RANK_FORMATS[strtolower($rank)] . TextFormat::RESET, ($rank !== "default" ? Chat::convertWithEmojis($message) : $message)], $format);
			$this->sendMessage($m);
		} else {
			$this->sendMessage($preformatted);
		}
	}

	/**
	 * Enforces formatting based on the current player's chat format settings
	 */
	public function sendJoinMessage(string $format, string $rank, Translatable|string $preformatted): void {
		$legacyIcons = $this->getSession()?->getSettings()->getSetting(GlobalSettings::LEGACY_RANK_ICONS) ?? false;
		if ($preformatted instanceof Translatable) $preformatted = $preformatted->getText();

		if ($legacyIcons) {
			$nametag = str_replace("{RANK}", TextFormat::BOLD . ChatStructure::LEGACY_RANK_FORMATS[strtolower($rank)] . TextFormat::RESET, $format);
			$this->sendMessage(TextFormat::AQUA . ">>> " . $nametag . TextFormat::RESET . TextFormat::GREEN . " joined the server!");
		} else {
			$this->sendMessage($preformatted);
		}
	}
}
