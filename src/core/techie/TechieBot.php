<?php

namespace core\techie;

use pocketmine\entity\{
	animation\ArmSwingAnimation,
	Human,
	Entity,
	Location,
	Skin
};
use pocketmine\world\{
	World,
	ChunkLoader,
	format\Chunk
};
use pocketmine\world\sound\PopSound;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\event\entity\{
	EntityDamageEvent,
	EntityDamageByEntityEvent
};
use pocketmine\{
	block\VanillaBlocks,
	network\mcpe\convert\TypeConverter,
	player\Player as PMPlayer,
	Server
};


use pocketmine\math\Vector3;

use pocketmine\network\mcpe\protocol\{
	types\entity\EntityMetadataCollection,
	types\entity\EntityMetadataFlags,
	types\entity\EntityMetadataProperties,
	types\entity\PropertySyncData,

	types\inventory\ItemStackWrapper,

	AddPlayerPacket,
	UpdateAbilitiesPacket,
	types\AbilitiesData,
	PlayerListPacket,
	types\PlayerListEntry,
	RemoveActorPacket,
	EmotePacket
};
use Ramsey\Uuid\Uuid;

use core\AtPlayer as Player;
use core\Core;
use core\chat\Chat;
use core\network\Links;
use core\utils\{
	CapeData,
	TextFormat
};
use pocketmine\network\mcpe\convert\LegacySkinAdapter;

class TechieBot extends Human implements ChunkLoader {

	const FIND_DISTANCE = 10;
	const LOSE_DISTANCE = 15;

	const BUBBLE_SHOW = 1;
	const BUBBLE_HIDE = 2;

	const ACTION_NONE = 0;
	const ACTION_WAVE = 1;
	const ACTION_JUMP = 2;
	const ACTION_WAVEJUMP = 3;
	const ACTION_DANCE = 4;

	public $aliveTicks = 0;

	public $bubbles = [];
	public $bkey = 0;

	public $bubbleeid = -1;
	public $bubblestatus = self::BUBBLE_HIDE;

	public $bubbleticks = 0;
	public $bubbletext = "";
	public $bubblepos = null;

	public $dialogue = [];
	public $dkey = 0;

	public $morph = null;

	public $lookingAt = "";
	public $lookingTicks = 0;

	public $action = self::ACTION_NONE;
	public $actionTicks = 0;

	public $waveCooldown = 0;
	public $jumpCooldown = 0;
	public $danceCooldown = 0;
	public $copyCooldown = 0;

	public int $loaderId = 0;
	public int $lastChunkHash;
	public array $loadedChunks = [];

	public function __construct(Location $position, Skin $skin, public bool $sitting = false) {
		parent::__construct($position, $skin);
		$this->loaderId = $this->getId();

		$server = Core::getInstance()->getNetwork()->getServerType();
		$this->bubbles = array_merge(
			Structure::BUBBLES["default"],
			Structure::BUBBLES[$server]
		);
		foreach ($this->bubbles as $key => $msg) {
			$this->bubbles[$key] = Chat::convertWithEmojis($msg);
		}

		$this->bubbleeid = Entity::nextRuntimeId();
		$this->bubblepos = $this->getPosition()->add(0, 2.4, 0);
		$this->dialogue = array_merge(Structure::TECHIE_DATA[$server]["dialogue"], Structure::GLOBAL_DIALOGUE);
		foreach ($this->dialogue as $key => $msg) {
			$this->dialogue[$key] = Chat::convertWithEmojis($msg);
		}

		shuffle($this->bubbles);
		shuffle($this->dialogue);

		$this->setNametag(TextFormat::AQUA . TextFormat::BOLD . "Techie");
		$this->setMaxHealth(10000);
		$this->setHealth(10000);

		$this->setSkin((new CapeData())->getSkinWithCape($this, "atmc"));
		$this->sendSkin();

		$this->setNameTagAlwaysVisible(true);
	}

	public function getNextBubble(): string {
		$this->bkey++;
		if ($this->bkey >= count($this->bubbles)) {
			$this->bkey = 0;
			shuffle($this->bubbles);
		}

		return $this->bubbles[$this->bkey];
	}

	public function getRandomBubble(): string {
		return $this->bubbles[mt_rand(0, count($this->bubbles) - 1)];
	}

	public function setBubbleText(string $text): void {
		$this->bubbletext = $text;
	}

	public function getNextDialogue(): string {
		$this->dkey++;
		if ($this->dkey >= count($this->dialogue)) {
			$this->dkey = 0;
			shuffle($this->dialogue);
		}

		return $this->dialogue[$this->dkey];
	}

	public function getRandomDialogue(): string {
		return $this->dialogue[mt_rand(0, count($this->dialogue) - 1)];
	}

	public function sendMessage(Player $player, string $message): void {
		if (!$player->isConnected()) return;
		$message = strtr($message, [
			"{store}" => Links::SHOP,
			"{shop}" => Links::SHOP,
			"{player}" => $player->getName(),
		]);
		$player->sendMessage(TextFormat::EMOJI_TECHIE . " " . TextFormat::AQUA . TextFormat::BOLD . "Techie: " . TextFormat::RESET . TextFormat::ITALIC . TextFormat::AQUA . $message);
	}

	public function broadcastMessage(string $message): void {
		foreach (Server::getInstance()->getOnlinePlayers() as $player) {
			$this->sendMessage($player, $message);
		}
	}

	public function isSitting(): bool {
		return $this->sitting;
	}

	public function wave(): void {
		if ($this->waveCooldown > 0) {
			$this->waveCooldown--;
		} else {
			$this->waveCooldown = 2;
			$this->broadcastAnimation(new ArmSwingAnimation($this));
		}
	}

	public function jump(): void {
		if ($this->jumpCooldown > 0) {
			$this->jumpCooldown--;
		} else {
			$this->jumpCooldown = 10;
			$this->motion->y = $this->gravity * 4;
		}
	}

	public function dance(): void {
		if ($this->danceCooldown > 0) {
			$this->danceCooldown--;
		} else {
			$this->danceCooldown = 60;
			$this->hitDat(Emotes::getRandomEmote());
		}
	}

	public function copy(Player $player, string $emoteId): bool {
		if ($this->copyCooldown > 0) return false;
		if ($player === $this->getLookingAt()) {
			$this->actionTicks = 100;
			$this->copyCooldown = 200;
			$this->action = self::ACTION_NONE;

			$this->hitDat($emoteId);

			if (!Emotes::isSaved($emoteId))
				Emotes::saveEmote($emoteId);

			return true;
		}
		return false;
	}

	public function doAnimation(): void {
		if (!$this->hasLookingAt()) {
			$this->action = self::ACTION_NONE;

			if ($this->ticksLived % 40 == 0) $this->findLookingAt();
			return;
		}
		if ($this->lookingTicks >= 600) {
			$this->action = self::ACTION_NONE;
			$this->findLookingAt();
			return;
		}

		$this->lookingTicks++;
		$this->actionTicks++;
		$this->copyCooldown--;

		$looking = $this->getLookingAt();
		if ($this->lookingTicks % 2 == 0) {
			$x = $looking->getLocation()->x - $this->getLocation()->x;
			$y = $looking->getLocation()->y - $this->getLocation()->y;
			$z = $looking->getLocation()->z - $this->getLocation()->z;
			$this->setRotation(rad2deg(atan2(-$x, $z)), rad2deg(-atan2($y, sqrt($x * $x + $z * $z))));
		}

		if ($this->isSitting()) return;

		if ($this->actionTicks >= 200) {
			$this->actionTicks = 0;
			$this->action = mt_rand(self::ACTION_NONE, self::ACTION_DANCE);
		}

		switch ($this->action) {
			case self::ACTION_NONE:
				break;
			case self::ACTION_WAVE:
				$this->wave();
				break;
			case self::ACTION_JUMP:
				$this->jump();
				break;
			case self::ACTION_WAVEJUMP:
				$this->wave();
				$this->jump();
				break;
			case self::ACTION_DANCE:
				$this->dance();
				break;
		}
	}

	public function getLookingAt(): ?Player {
		return Server::getInstance()->getPlayerExact($this->lookingAt);
	}

	public function hasLookingAt(): bool {
		return $this->getLookingAt() != null && $this->getLookingAt()->getPosition()->distance($this->getPosition()) <= self::LOSE_DISTANCE && !$this->getLookingAt()->isVanished();
	}

	public function findLookingAt(): void {
		$this->lookingTicks = 0;
		$nearest = $this->getWorld()->getNearestEntity($this->getPosition(), self::FIND_DISTANCE, Player::class);
		if (!is_null($nearest)) {
			$this->lookingAt = $nearest instanceof Player ? $nearest->getName() : $nearest->getNameTag();
		}
	}

	public function tickBubble(): void {
		$this->bubbleticks++;
		switch ($this->bubblestatus) {
			case self::BUBBLE_SHOW:
				if ($this->bubbleticks % 140 == 0) {
					$this->bubbleticks = 0;
					$this->bubblestatus = self::BUBBLE_HIDE;
					$this->hideBubble();
				}
				break;
			case self::BUBBLE_HIDE:
				if ($this->bubbleticks % 20 == 0) {
					$this->bubbleticks = 0;
					$this->bubblestatus = self::BUBBLE_SHOW;
					$this->setBubbleText($this->getNextBubble());
					$this->showBubble();
				}
				break;
		}
	}

	public function showBubble(?Player $player = null): void {
		$players = $player == null ? $this->hasSpawned : [$player];

		$pks = [];

		$skin = (new LegacySkinAdapter())->toSkinData(new Skin("Standard_Custom", str_repeat("\x00", 8192), "", "geometry.humanoid.custom"));

		$uuid = Uuid::uuid4();

		$pk = new PlayerListPacket();
		$pk->type = PlayerListPacket::TYPE_ADD;
		$pk->entries = [PlayerListEntry::createAdditionEntry($uuid, $this->bubbleeid, $this->bubbletext, $skin)];
		$pks[] = $pk;

		$pk = new AddPlayerPacket();
		$pk->uuid = $uuid;
		$pk->username = $text = $this->bubbletext;
		$pk->actorRuntimeId = $this->bubbleeid;
		$pk->gameMode = 0;
		$pk->position = $this->bubblepos;
		$pk->item = ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet(VanillaBlocks::AIR()->asItem()));
		$flags = (1 << EntityMetadataFlags::IMMOBILE
		);
		$pk->abilitiesPacket = UpdateAbilitiesPacket::create(new AbilitiesData(0, 0, $this->bubbleeid, []));


		$collection = new EntityMetadataCollection();
		$collection->setLong(EntityMetadataProperties::FLAGS, $flags);
		$collection->setString(EntityMetadataProperties::NAMETAG, $this->bubbletext);
		$collection->setFloat(EntityMetadataProperties::SCALE, 0.01);
		$pk->metadata = $collection->getAll();

		$pk->syncedProperties = new PropertySyncData([], []);

		$pks[] = $pk;

		$pk = new PlayerListPacket();
		$pk->type = PlayerListPacket::TYPE_REMOVE;
		$pk->entries = [PlayerListEntry::createRemovalEntry($uuid)];
		$pks[] = $pk;

		foreach ($pks as $pk) foreach ($players as $player) $player->getNetworkSession()->sendDataPacket($pk);
		$this->getWorld()->addSound($this->bubblepos, new PopSound());
	}

	public function hideBubble(?Player $player = null) {
		$players = $player == null ? $this->hasSpawned : [$player];

		$pk = new RemoveActorPacket();
		$pk->actorUniqueId = $this->bubbleeid;
		foreach ($players as $player) $player->getNetworkSession()->sendDataPacket($pk);
	}

	public function attack(EntityDamageEvent $source): void {
		$source->cancel();
		if ($source instanceof EntityDamageByEntityEvent) {
			$player = $source->getDamager();
			if ($player instanceof Player) {
				$this->sendMessage($player, $this->getRandomDialogue());
			}
		}
	}

	public function entityBaseTick(int $tickDiff = 1): bool {
		if ($this->lastChunkHash !== ($hash = World::chunkHash($x = (int) $this->getPosition()->x >> 4, $z = (int) $this->getPosition()->z >> 4))) {
			$this->registerToChunk($x, $z);

			World::getXZ($this->lastChunkHash, $oldX, $oldZ);
			$this->unregisterFromChunk($oldX, $oldZ);

			$this->lastChunkHash = $hash;
		}
		$this->aliveTicks++;

		if ($this->aliveTicks % 1800 == 0) {
			$this->broadcastMessage($this->getNextDialogue());
		}
		$this->tickBubble();

		$this->doAnimation();

		return $this->isAlive();
	}

	public function spawnTo(PMPlayer $player): void {
		parent::spawnTo($player);
		//$this->sendSkin([$player]);
		if ($this->bubblestatus == self::BUBBLE_SHOW) $this->showBubble($player);
	}

	public function despawnFrom(PMPlayer $player, bool $send = true): void {
		parent::despawnFrom($player);
		$this->hideBubble($player);
	}

	public function canSaveWithChunk(): bool {
		return false;
	}

	public function canPickupXp(): bool {
		return true;
	}

	public function hitDat(string $emoteId): void {
		$pk = EmotePacket::create($this->getId(), $emoteId, 100, 1 << 1, "", EmotePacket::FLAG_MUTE_ANNOUNCEMENT);

		foreach ($this->getViewers() as $pl) $pl->getNetworkSession()->sendDataPacket($pk);
	}

	protected function initEntity(CompoundTag $nbt): void {
		parent::initEntity($nbt);
		$this->getWorld()->registerChunkLoader($this, (int) $this->getPosition()->x >> 4, (int) $this->getPosition()->z >> 4);
		$this->lastChunkHash = World::chunkHash((int) $this->getPosition()->x >> 4, (int) $this->getPosition()->z >> 4);
	}

	public function registerToChunk(int $chunkX, int $chunkZ) {
		if (!isset($this->loadedChunks[World::chunkHash($chunkX, $chunkZ)])) {
			$this->loadedChunks[World::chunkHash($chunkX, $chunkZ)] = true;
			$this->getWorld()->registerChunkLoader($this, $chunkX, $chunkZ);
		}
	}

	public function unregisterFromChunk(int $chunkX, int $chunkZ) {
		if (isset($this->loadedChunks[World::chunkHash($chunkX, $chunkZ)])) {
			unset($this->loadedChunks[World::chunkHash($chunkX, $chunkZ)]);
			$this->getWorld()->unregisterChunkLoader($this, $chunkX, $chunkZ);
		}
	}

	public function onChunkChanged(Chunk $chunk) {
	}

	public function onChunkLoaded(Chunk $chunk) {
	}

	public function onChunkUnloaded(Chunk $chunk) {
	}

	public function onChunkPopulated(Chunk $chunk) {
	}

	public function onBlockChanged(Vector3 $block) {
	}

	public function getLoaderId(): int {
		return $this->loaderId;
	}

	public function isLoaderActive(): bool {
		return !$this->isFlaggedForDespawn() && !$this->closed;
	}
}
