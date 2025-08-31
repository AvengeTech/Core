<?php

namespace core\entities\bots;

use pocketmine\Server;
use pocketmine\lang\Language;
use pocketmine\network\mcpe\convert\{
	LegacySkinAdapter,
	TypeConverter
};
use pocketmine\network\mcpe\protocol\types\{
	skin\SkinData,
	skin\SkinImage,
	entity\EntityIds,
	entity\EntityLink,
	entity\EntityMetadataCollection,
	entity\EntityMetadataFlags,
	entity\EntityMetadataProperties,
	entity\LongMetadataProperty,
	entity\PropertySyncData,
	inventory\ItemStackWrapper
};
use pocketmine\network\mcpe\protocol\{
	AddPlayerPacket,
	AddActorPacket,
	UpdateAbilitiesPacket,
	types\AbilitiesData,
	PlayerSkinPacket,
	PlayerListPacket,
	SetActorLinkPacket,
	types\PlayerListEntry,
	MovePlayerPacket,
	RemoveActorPacket,
	MobArmorEquipmentPacket
};
use pocketmine\console\ConsoleCommandSender;

use Ramsey\Uuid\Uuid;

use pocketmine\math\Vector3;
use pocketmine\entity\{
	Entity,
	Skin
};
use pocketmine\world\{
	World as Level,
	Position
};
use pocketmine\item\Item;

use core\{
	Core,
	AtPlayer as Player
};
use core\utils\TextFormat;
use core\network\ui\SelectWhichUi;
use pocketmine\item\VanillaItems;
use pocketmine\math\AxisAlignedBB;
use pocketmine\network\mcpe\protocol\types\entity\FloatMetadataProperty;

class Bot {

	const RENDER_DISTANCE = 40;
	public $skindir = "/[REDACTED]/skins/";

	public ?Vector3 $vector3 = null;

	public int $id;
	public ?int $sittingId = null;

	public array $spawnedTo = [];
	public array $packets = [
		"spawn" => [],
		"despawn" => [],
	];

	public function __construct(
		public string $name,
		public string $nametag,

		public float $x,
		public float $y,
		public float $z,
		public int $pitch,
		public int $yaw,
		public string $levelName,

		public Item $item,
		public array $armor,

		public bool $sitting,
		public bool $canTurn,
		public bool $useSkin,
		public string $skinName,

		public float $scale,
		public array $config
	) {
		$this->id = Entity::nextRuntimeId();
		if ($this->isSitting()) {
			$this->sittingId = Entity::nextRuntimeId();
		}

		/* Caching entity spawn/despawn packets */
		$uuid = Uuid::uuid4();
		$spawn = [];

		$skin = new Skin("Standard_Custom", $this->getSkinData(), "", "geometry.humanoid.custom");
		if ($this->useSkin()) {
			$pk = new PlayerListPacket();
			$pk->type = PlayerListPacket::TYPE_ADD;
			$pk->entries = [PlayerListEntry::createAdditionEntry($uuid, $this->id, $this->getNametag(), (new LegacySkinAdapter())->toSkinData($skin))];
			$spawn[] = $pk;
		}

		if ($this->isSitting()) {
			$pk = new AddActorPacket();
			$pk->actorRuntimeId = $this->sittingId;
			$pk->actorUniqueId = $this->sittingId;
			$pk->type = EntityIds::WOLF;
			$pk->yaw = $pk->headYaw = $this->getYaw();

			$pk->position = $this->getVector3()->add(0, 1.6, 0);
			$pk->metadata = [
				EntityMetadataProperties::FLAGS => new LongMetadataProperty(1 << EntityMetadataFlags::IMMOBILE | 1 << EntityMetadataFlags::SILENT | 1 << EntityMetadataFlags::INVISIBLE),
				EntityMetadataProperties::SCALE => new FloatMetadataProperty(0.01)
			];
			$pk->syncedProperties = new PropertySyncData([], []);
			$spawn[] = $pk;
		}

		$pk = new AddPlayerPacket();
		$pk->uuid = $uuid;
		$pk->username = $this->getNametag();
		$pk->actorRuntimeId = $this->getId();
		$pk->gameMode = 0;
		$pk->position = $this->getVector3();
		$pk->pitch = $this->getPitch();
		$pk->headYaw = $this->getYaw();
		$pk->yaw = $this->getYaw();
		$pk->item = ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet($item));
		$flags = (1 << EntityMetadataFlags::IMMOBILE
		);
		$pk->abilitiesPacket = UpdateAbilitiesPacket::create(new AbilitiesData(0, 0, $this->getId(), []));

		$collection = new EntityMetadataCollection();
		$collection->setLong(EntityMetadataProperties::FLAGS, $flags);
		$collection->setString(EntityMetadataProperties::NAMETAG, $this->getNametag());
		$collection->setFloat(EntityMetadataProperties::SCALE, $this->getScale());
		$collection->setGenericFlag(EntityMetadataFlags::RIDING, $this->isSitting());
		$collection->setByte(EntityMetadataProperties::ALWAYS_SHOW_NAMETAG, 1);
		$pk->metadata = $collection->getAll();
		$pk->syncedProperties = new PropertySyncData([], []);
		$spawn[] = $pk;

		if ($this->isSitting()) {
			$pk = new SetActorLinkPacket();
			$pk->link = new EntityLink($this->sittingId, $this->id, EntityLink::TYPE_RIDER, true, true, 0);
			$spawn[] = $pk;
		}

		if ($this->useSkin()) {
			$pk = new PlayerListPacket();
			$pk->type = PlayerListPacket::TYPE_REMOVE;
			$pk->entries = [PlayerListEntry::createRemovalEntry($uuid)];
			$spawn[] = $pk;

			$pk = new PlayerSkinPacket();
			$pk->uuid = $uuid;
			$pk->skin = $this->toSkinData($skin);
			$spawn[] = $pk;
		}

		if (!empty($this->armor)) {
			$pk = new MobArmorEquipmentPacket();
			$pk->actorRuntimeId = $this->getId();
			$pk->head = ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet($this->armor[0] ?? VanillaItems::AIR()));
			$pk->chest = ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet($this->armor[1] ?? VanillaItems::AIR()));
			$pk->legs = ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet($this->armor[2] ?? VanillaItems::AIR()));
			$pk->feet = ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet($this->armor[3] ?? VanillaItems::AIR()));
			$spawn[] = $pk;
		}
		$this->setPackets("spawn", $spawn);

		$despawn = [];
		$pk = new RemoveActorPacket();
		$pk->actorUniqueId = $this->getId();
		$despawn[] = $pk;
		if ($this->isSitting()) {
			$pk = new RemoveActorPacket();
			$pk->actorUniqueId = $this->sittingId;
			$despawn[] = $pk;
		}

		$this->setPackets("despawn", $despawn);
	}

	public function getId(): int {
		return $this->id;
	}

	public function getSittingId(): ?int {
		return $this->sittingId;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getNametag(): string {
		return $this->nametag;
	}

	public function getX(): float {
		return $this->x;
	}

	public function getY(): float {
		return $this->y;
	}

	public function getZ(): float {
		return $this->z;
	}

	public function getVector3(): Vector3 {
		return ($this->vector3 == null ? $this->vector3 = new Vector3($this->getX(), $this->getY(), $this->getZ()) : $this->vector3);
	}

	public function getPitch(): int {
		return $this->pitch;
	}

	public function getYaw(): int {
		return $this->yaw;
	}

	public function getWorldName(): string {
		return $this->levelName;
	}

	public function getWorld(): ?Level {
		return Server::getInstance()->getWorldManager()->getWorldByName($this->getWorldName());
	}

	public function isSitting(): bool {
		return $this->sitting;
	}

	public function canTurn(): bool {
		return $this->canTurn;
	}

	public function getItem(): Item {
		return $this->item;
	}

	public function useSkin(): bool {
		return $this->useSkin;
	}

	public function getSkinName(): string {
		return $this->skinName;
	}

	public function getSkinData(): string {
		$dir = $this->skindir . $this->getSkinName() . ".dat";
		return file_get_contents($dir);
		//return str_repeat("0", 8192);
	}

	public function toSkinData(Skin $skin): SkinData {
		$capeData = $skin->getCapeData();
		$capeImage = $capeData === "" ? new SkinImage(0, 0, "") : new SkinImage(32, 64, $capeData);
		$geometryName = $skin->getGeometryName();
		if ($geometryName === "") {
			$geometryName = "geometry.humanoid.custom";
		}
		return new SkinData(
			$skin->getSkinId(),
			"", //TODO: playfab ID
			json_encode(["geometry" => ["default" => $geometryName]]),
			SkinImage::fromLegacy($skin->getSkinData()),
			[],
			$capeImage,
			$skin->getGeometryData()
		);
	}

	public function getScale(): float {
		return $this->scale;
	}

	public function getConfig(): array {
		return $this->config;
	}

	public function getSpawnedTo(): array {
		return $this->spawnedTo;
	}

	public function isSpawnedTo(Player $player): bool {
		return isset($this->spawnedTo[$player->getName()]);
	}

	public function spawn(Player $player): bool {
		$botPos = $this->getVector3();
		$distance = $botPos->distance($player->getPosition());
		if ($distance <= self::RENDER_DISTANCE) {
			$this->spawnedTo[$player->getName()] = true;
			foreach ($this->getPackets("spawn") as $pk) $player->getNetworkSession()->sendDataPacket($pk);
			return true;
		}
		return false;
	}

	public function move(Player $player): void {
		$botPos = $this->getVector3();
		$distance = $botPos->distance($player->getPosition());
		if ($this->isSpawnedTo($player)) {
			if ($distance <= 10 && $this->canTurn()) {
				$x = $botPos->x - $player->getPosition()->x;
				$y = $botPos->y - $player->getPosition()->y;
				$z = $botPos->z - $player->getPosition()->z;
				$yaw = asin($x / sqrt($x * $x + $z * $z)) / 3.14 * 180;
				$pitch = round(asin($y / sqrt($x * $x + $z * $z + $y * $y)) / 3.14 * 180);
				if ($z > 0) $yaw = -$yaw + 180;

				$pk = new MovePlayerPacket();
				$pk->actorRuntimeId = $this->getId();
				$pk->position = $botPos->add(0, 1.62, 0);
				$pk->yaw = $yaw;
				$pk->pitch = $pitch;
				$pk->headYaw = $yaw;
				$pk->mode = 0;
				$pk->onGround = true;
				$player->getNetworkSession()->sendDataPacket($pk);
			} elseif ($distance > self::RENDER_DISTANCE) {
				$this->despawn($player);
			}
		} else {
			if (
				$player->getWorld() === $this->getWorld() &&
				$distance <= self::RENDER_DISTANCE
			) {
				$this->spawn($player);
			}
		}
	}

	public function getBB(): AxisAlignedBB {
		[$height, $width] = [1.8 * $this->getScale() + ($this->isSitting() ? 0.75 : 0), 0.6 * $this->getScale()];
		return new AxisAlignedBB(
			$this->getX() - $width / 2,
			$this->getY(),
			$this->getZ() - $width / 2,
			$this->getX() + $width / 2,
			$this->getY() + $height,
			$this->getZ() + $width / 2
		);
	}

	public function despawn(Player $player): void {
		unset($this->spawnedTo[$player->getName()]);
		foreach ($this->getPackets("despawn") as $pk) $player->getNetworkSession()->sendDataPacket($pk);
	}

	public function getPackets(string $id): array {
		return $this->packets[$id] ?? [];
	}

	public function setPackets(string $id, array $packets): void {
		$this->packets[$id] = $packets;
	}

	public function interact(Player $player): void {
		/**
		 * Config examples:
		 * $config = [
		 *    "type" => "statue",
		 *    "name" => "DisplayName",
		 *    "messages" => [
		 *        "Message will appear when hit.",
		 *        "Blah blah bloo"
		 *    ]
		 * ];
		 *
		 * $config = [
		 *    "type" => "command",
		 *    "commands" => [
		 *        "kill",
		 *        "tell m4l0ne23 bleh"
		 *    ]
		 * ];
		 *
		 * $config = [
		 *    "type" => "transfer",
		 *    "servers" => [
		 *        "kitpvp-1",
		 *        "kitpvp-2",
		 *        "kitpvp-3"
		 *    ]
		 * ];
		 *
		 * $config = [
		 *    "type" => "teleport",
		 *    "x" => 128,
		 *    "y" => 100,
		 *    "z" => 128,
		 *    "level" => "world"
		 * ];
		 **/
		$config = $this->getConfig();
		if ($config["type"] == "statue") {
			if (!empty($config["messages"])) {
				$name = ($config["name"] == "{name}" ? $player->getName() : $config["name"]);
				$player->sendMessage(TextFormat::YELLOW . $name . ": " . TextFormat::GRAY . $config["messages"][mt_rand(0, count($config["messages"]) - 1)]);
			}
			return;
		}
		if ($config["type"] == "command") {
			if (($msg = $config["message"] ?? null) !== null) $player->sendMessage($msg);
			$sender = ($config["console"] ?? false) ? new ConsoleCommandSender(Server::getInstance(), new Language("eng")) : $player;
			foreach ($config["commands"] as $command) {
				Server::getInstance()->dispatchCommand($sender, str_replace("{player}", $player->getName(), $command));
			}
			return;
		}
		$network = Core::getInstance()->getNetwork();
		if ($config["type"] == "transfer") {
			$type = $config["server"];
			$servers = $network->getServerManager()->getServersByType($type);
			foreach ($servers as $key => $server) {
				if ($server->isPrivate() && !$player->isStaff()) {
					unset($servers[$key]);
				}
			}
			if (count($servers) == 1) {
				$server = array_shift($servers);
				if (!$server->isOnline()) {
					$player->sendMessage(TextFormat::RI . "This server is offline! Please try to connect later.");
					return;
				}
				if (!$server->canTransfer($player)) {
					$player->sendMessage(TextFormat::RI . "Cannot connect to this server, either full or private!");
					return;
				}
				$server->transfer($player, TextFormat::GI . "Connected to " . $server->getId());
				return;
			}
			$player->showModal(new SelectWhichUi($player, $type));
			return;
		}
		if ($config["type"] == "teleport") {
			$player->teleport(new Position($config["x"], $config["y"], $config["z"], Server::getInstance()->getWorldManager()->getWorldByName($config["level"])));
		}
	}
}
