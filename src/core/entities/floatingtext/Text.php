<?php

namespace core\entities\floatingtext;

use pocketmine\Server;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\{
	Entity,
	Skin
};
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\convert\{
	LegacySkinAdapter,
	TypeConverter
};
use pocketmine\network\mcpe\protocol\{
	AddPlayerPacket,
	UpdateAbilitiesPacket,
	PlayerListPacket,
	SetActorDataPacket,
	RemoveActorPacket,

	types\AbilitiesData,
	types\PlayerListEntry,
	types\entity\EntityMetadataCollection,
	types\entity\EntityMetadataFlags,
	types\entity\EntityMetadataProperties,
	types\entity\PropertySyncData,
	types\inventory\ItemStackWrapper
};
use pocketmine\world\World;

use Ramsey\Uuid\Uuid;

use core\{
	Core,
	AtPlayer as Player
};
use core\network\{
	Structure,
	Links
};
use core\utils\TextFormat;

class Text {

	const RENDER_DISTANCE = 25;

	public int $id;

	public $spawnedTo = [];

	public function __construct(
		public string $name,
		public string $text,
		public Vector3 $position,
		public string $worldName
	) {
		$this->id = Entity::nextRuntimeId();
	}

	public function getId(): int {
		return $this->id;
	}

	public function getName(): string {
		return $this->name;
	}

	/**
	 * Gets the text value for a player
	 * @param Player $player
	 */
	public function getText(Player $player): string {
		$text = $this->text;
		$network = Core::getInstance()->getNetwork();
		if (stristr($text, "{totalplayers}") != false) $text = str_replace("{totalplayers}", $network->getServerManager()->getTotalPlayers(), $text);
		if (stristr($text, "{type}") != false) $text = str_replace("{type}", $network->getServerType(), $text);
		foreach (Structure::PORT_TO_IDENTIFIER as $port => $identifier) {
			if (stristr($text, "{" . $identifier . ":online}") != false) $text = str_replace("{" . $identifier . ":online}", ($network->isOnline($identifier) ? TextFormat::GREEN . "ONLINE" : TextFormat::RED . "OFFLINE"), $text);
			if (stristr($text, "{" . $identifier . ":players}") != false) $text = str_replace("{" . $identifier . ":players}", count($network->getPlayers($identifier)), $text);
			if (stristr($text, "{" . $identifier . ":maxplayers}") != false) $text = str_replace("{" . $identifier . ":maxplayers}", $network->getMaxPlayers($identifier), $text);
		}
		foreach (Structure::SERVER_TYPES as $type) {
			if (stristr($text, "{" . $type . "}") != false) $text = str_replace("{" . $type . "}", $network->getServerManager()->getPlayerCountByType($type), $text);
			if (stristr($text, "{case:" . $type . "}") != false) $text = str_replace("{case:" . $type . "}", $network->getCaseName($type), $text);
		}

		if (stristr($text, "{store}") != false) $text = str_replace("{store}", Links::SHOP, $text);
		if (stristr($text, "{shop}") != false) $text = str_replace("{shop}", Links::SHOP, $text);
		if (stristr($text, "{techits}") != false) $text = str_replace("{techits}", $player->getTechits(), $text);

		if (stristr($text, "{name}") != false) $text = str_replace("{name}", $player->getName(), $text);
		if (stristr($text, "{rank}") != false) $text = str_replace("{rank}", $player->getRank(), $text);

		$prison = Core::getInstance()->getServer()->getPluginManager()->getPlugin("Prison");
		if ($prison != null) {
			$bt = $prison->getBlockTournament();
			if ($player->hasGameSession()) {
				$session = $player->getGameSession()->getBlockTournament();
				if (stristr($text, "{btstarted}") != false) $text = str_replace("{btstarted}", $session->getStarted(), $text);
				if (stristr($text, "{btwins}") != false) $text = str_replace("{btwins}", $session->getWins(), $text);
				if (stristr($text, "{btmined}") != false) $text = str_replace("{btmined}", $session->getMined(), $text);
			}

			if (stristr($text, "{bts:") != false) {
				$replaced = false;
				$game = $bt->getGameManager()->getPlayerGame($player);
				if ($game !== null) {
					$places = $game->getPlaces();
					foreach ($places as $key => $place) {
						if (stristr($text, "{bts:" . ($key + 1) . "}") != false) {
							$text = str_replace("{bts:" . ($key + 1) . "}", TextFormat::RED . $place->getFormattedPlace() . TextFormat::GRAY . " | " . TextFormat::YELLOW . $place->getName() . TextFormat::GRAY . " | " . TextFormat::GREEN . $place->getBlocksMined() . " blocks", $text);
							$replaced = true;
							break;
						}
					}
				}
				if (!$replaced) {
					$text = TextFormat::RED . "No data.";
				}
			}


			$boxes = $prison->getMysteryBoxes();
			if ($player->hasGameSession()) {
				$session = $player->getGameSession()->getMysteryBoxes();
				foreach (["iron", "gold", "diamond", "vote"] as $tier) {
					if (stristr($text, "{keys:" . $tier . "}") != false) {
						$text = str_replace("{keys:" . $tier . "}", $session->getKeys($tier), $text);
					}
				}
			}

			if (stristr($text, "{bg}") != false) {
				$text = str_replace("{bg}", count($prison->getGangs()->getGangManager()->getBattleManager()->getBattles(true)) * 2, $text);
			}

			if (stristr($text, "{qm}") != false) {
				if ($player->hasGameSession()) {
					$session = $player->getGameSession()->getQuests();
					if (!$session->hasActiveQuest()) {
						if ($session->hasCooldown()) {
							$new = TextFormat::GRAY . "Available in " . TextFormat::RED . $session->getFormattedCooldown();
						} else {
							$new = TextFormat::GRAY . "Quest available!";
						}
					} else {
						$quest = $session->getCurrentQuest();
						if ($quest->isComplete()) {
							$new = TextFormat::GREEN . "Complete! Tap to turn in!";
						} else {
							$new = TextFormat::GOLD . "Quest in progress...";
						}
					}
					$text = str_replace("{qm}", $new, $text);
				}
			}
		}

		$skyblock = Core::getInstance()->getServer()->getPluginManager()->getPlugin("SkyBlock");
		if ($skyblock != null) {
			if (stristr($text, "{duel}") != false) $text = str_replace("{duel}", $skyblock->getDuels()->getTotalDueling(), $text);
			if (stristr($text, "{dqueue}") != false) $text = str_replace("{dqueue}", $skyblock->getDuels()->getTotalQueue(), $text);

			if (stristr($text, "{arena}") != false) $text = str_replace("{arena}", $skyblock->getCombat()->getArenas()->getTotalPlayers(), $text);
			if (stristr($text, "{warzone}") != false) $text = str_replace("{warzone}", $skyblock->getCombat()->getArenas()->getArena()->getName(), $text);
		}

		$pvp = Core::getInstance()->getServer()->getPluginManager()->getPlugin("PvP");
		if ($pvp != null) {
		}

		return $text;
	}

	public function getX(): float {
		return $this->getPosition()->getZ();
	}

	public function getY(): float {
		return $this->getPosition()->getY();
	}

	public function getZ(): float {
		return $this->getPosition()->getZ();
	}

	public function getPosition(): Vector3 {
		return $this->position;
	}

	public function getWorldName(): string {
		return $this->worldName;
	}

	public function getWorld(): ?World {
		return Server::getInstance()->getWorldManager()->getWorldByName($this->getWorldName());
	}

	public function getSpawnedTo(): array {
		return $this->spawnedTo;
	}

	public function isSpawnedTo(Player $player): bool {
		return isset($this->spawnedTo[$player->getName()]);
	}

	public function spawn(Player $player): void {
		$this->spawnedTo[$player->getName()] = true;

		$skin = (new LegacySkinAdapter())->toSkinData(new Skin("Standard_Custom", str_repeat("\x00", 8192), "", "geometry.humanoid.custom"));

		$uuid = Uuid::uuid4();
		$text = $this->getText($player);

		$pk = new PlayerListPacket();
		$pk->type = PlayerListPacket::TYPE_ADD;
		$pk->entries = [PlayerListEntry::createAdditionEntry($uuid, $this->id, $text, $skin)];
		$player->getNetworkSession()->sendDataPacket($pk);

		$pk = new AddPlayerPacket();
		$pk->uuid = $uuid;
		$pk->username = $text;
		$pk->actorRuntimeId = $this->getId();
		$pk->position = $this->getPosition()->add(0, 0.25, 0);
		$pk->gameMode = 0;
		$pk->item = ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet(VanillaBlocks::AIR()->asItem()));
		$flags = (
			1 << EntityMetadataFlags::IMMOBILE
		);

		$collection = new EntityMetadataCollection();
		$collection->setLong(EntityMetadataProperties::FLAGS, $flags);
		$collection->setString(EntityMetadataProperties::NAMETAG, $text);
		$collection->setFloat(EntityMetadataProperties::SCALE, 0.01);
		$collection->setByte(EntityMetadataProperties::ALWAYS_SHOW_NAMETAG, 1);
		$pk->metadata = $collection->getAll();
		$pk->syncedProperties = new PropertySyncData([], []);

		$pk->abilitiesPacket = UpdateAbilitiesPacket::create(new AbilitiesData(0, 0, $this->getId(), []));
		$player->getNetworkSession()->sendDataPacket($pk);

		$pk = new PlayerListPacket();
		$pk->type = PlayerListPacket::TYPE_REMOVE;
		$pk->entries = [PlayerListEntry::createRemovalEntry($uuid)];
		$player->getNetworkSession()->sendDataPacket($pk);
	}

	public function move(Player $player): void {
		$pos = $this->getPosition();
		$distance = $pos->distance($player->getPosition());
		if ($this->isSpawnedTo($player)) {
			if ($distance > self::RENDER_DISTANCE) {
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

	public function update(?Player $player = null): void {
		if ($player == null) {
			foreach ($this->getSpawnedTo() as $name => $value) {
				$player = Server::getInstance()->getPlayerExact($name);
				if ($player instanceof Player && $player->getPosition()->distance($this->getPosition()) <= self::RENDER_DISTANCE) {
					$text = $this->getText($player);

					$pk = new SetActorDataPacket();
					$pk->actorRuntimeId = $this->getId();
					$flags = (1 << EntityMetadataFlags::IMMOBILE
					);

					$collection = new EntityMetadataCollection();
					$collection->setLong(EntityMetadataProperties::FLAGS, $flags);
					$collection->setString(EntityMetadataProperties::NAMETAG, $text);
					$collection->setFloat(EntityMetadataProperties::SCALE, 0.01);
					$pk->metadata = $collection->getAll();
					$pk->syncedProperties = new PropertySyncData([], []);
					$player->getNetworkSession()->sendDataPacket($pk);
				}
			}
		} else {
			if ($player->getPosition()->distance($this->getPosition()) <= self::RENDER_DISTANCE) {
				$text = $this->getText($player);

				$pk = new SetActorDataPacket();
				$pk->actorRuntimeId = $this->getId();
				$flags = (1 << EntityMetadataFlags::IMMOBILE
				);
				$collection = new EntityMetadataCollection();
				$collection->setLong(EntityMetadataProperties::FLAGS, $flags);
				$collection->setString(EntityMetadataProperties::NAMETAG, $text);
				$collection->setFloat(EntityMetadataProperties::SCALE, 0.01);
				$pk->metadata = $collection->getAll();
				$pk->syncedProperties = new PropertySyncData([], []);
				$player->getNetworkSession()->sendDataPacket($pk);
			}
		}
	}

	public function despawn(Player $player): void {
		unset($this->spawnedTo[$player->getName()]);
		$pk = new RemoveActorPacket();
		$pk->actorUniqueId = $this->getId();
		$player->getNetworkSession()->sendDataPacket($pk);
	}
}
