<?php

namespace core\utils;

use core\cosmetics\entity\Snowball;
use pocketmine\entity\{Entity, EntityFactory, EntityDataHelper, Human};
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\item\Item;

use core\lootboxes\entity\LootBox;
use core\techie\TechieBot;
use core\tutorial\entity\QuestionMark;
use core\utils\entity\AtIcon;
use core\utils\entity\TempExperienceOrb;
use core\utils\entity\TempItemEntity;
use core\utils\entity\Trophy;
use core\vote\entity\VoteBox;
use core\gadgets\entity\Cake;
use core\gadgets\entity\CakeBomb;
use core\gadgets\entity\Firework;
use core\gadgets\entity\SailingSub;
use core\gadgets\entity\CollisionItem;
use core\items\projectile\WindCharge;
use lobby\entity\DjSheep;
use lobby\entity\Jetski;
use lobby\gmbots\entity\{
	BattlesBot,
	PrisonBot,
	SkyBlockBot
};
use lobby\scavenger\entity\{
	Cheese,
	Cheeseburger,
	Glizzy,
	Shoes,
	Skull
};

use skyblock\combat\arenas\entity\MoneyBag;
use skyblock\combat\arenas\entity\SupplyDrop;
use skyblock\combat\arenas\entity\Turret;
use skyblock\entity\{
	ArmorStand,
	Clipboard,
	Controller,
	DollarSign,
	Earth,
    EnderPearl,
    FireworksEntity,
	Mallet,
	NoGravityItemEntity,
	XpBottle,
};
use skyblock\crates\entity\{
	IronCrate,
	GoldCrate,
	DiamondCrate,
	EmeraldCrate,
	DivineCrate,
	VoteCrate
};
use skyblock\islands\text\entity\TextEntity;
use skyblock\pets\types\IslandPet;
use skyblock\pets\types\island\{
	AllayPet,
	AxolotlPet,
	BeePet,
	CatPet,
	DogPet,
	FoxPet,
	RabbitPet,
	VexPet
};
use skyblock\spawners\entity\hostile\{
	Blaze,
	Breeze,
	CaveSpider,
	Creeper,
	Enderman,
	Husk,
	Skeleton,
	Spider,
	Witch,
	WitherSkeleton,
	Zombie,
};
use skyblock\spawners\entity\passive\{
	Pig,
	Chicken,
	Sheep,
	Cow,
	Mooshroom,
	IronGolem
};

final class EntityRegistry {

	// Compile all the entity registration into one file like it should've been in the first place smh.
	public static function setup(string $serverType = "core"): void {
		self::registerSimpleEntity("LootBox", LootBox::class);
		self::registerSimpleEntity("QuestionMark", QuestionMark::class);
		self::registerSimpleEntity("VoteBox", VoteBox::class);
		self::registerHumanEntity("TechieBot", TechieBot::class);
		self::registerSimpleEntity("AtIcon", AtIcon::class);
		self::registerSimpleEntity("TempItemEntity", TempItemEntity::class);
		self::registerSimpleEntity("TempExperienceOrb", TempExperienceOrb::class);

		self::registerSimpleEntity("Cake", Cake::class);
		self::registerSimpleEntity("CakeBomb", CakeBomb::class);
		self::registerSimpleEntity("Firework", Firework::class);
		self::registerSimpleEntity("SailingSub", SailingSub::class);
		self::registerSimpleEntity("CollisionItem", CollisionItem::class);

		self::registerSimpleEntity("Snowball", Snowball::class);
		self::registerSimpleEntity("WindCharge", WindCharge::class);

		switch ($serverType) {
			case "skyblock":
				self::registerSimpleEntity("EnderPearl", EnderPearl::class);
				self::registerSimpleEntity("ArmorStand", ArmorStand::class);
				self::registerSimpleEntity("Clipboard", Clipboard::class);
				self::registerSimpleEntity("Controller", Controller::class);
				self::registerSimpleEntity("DollarSign", DollarSign::class);
				self::registerSimpleEntity("Earth", Earth::class);
				self::registerSimpleEntity("Mallet", Mallet::class);
				self::registerCustomEntity("NoGravityItemEntity", NoGravityItemEntity::class, function (World $world, CompoundTag $nbt): NoGravityItemEntity {
					$itemTag = $nbt->getCompoundTag(NoGravityItemEntity::TAG_ITEM);
					if ($itemTag === null) {
						throw new SavedDataLoadingException("Expected \"" . NoGravityItemEntity::TAG_ITEM . "\" NBT tag not found");
					}

					$item = Item::nbtDeserialize($itemTag);
					if ($item->isNull()) {
						throw new SavedDataLoadingException("Item is invalid");
					}
					return new NoGravityItemEntity(EntityDataHelper::parseLocation($nbt, $world), $item, $nbt);
				});
				self::registerSimpleEntity("IronCrate", IronCrate::class);
				self::registerSimpleEntity("GoldCrate", GoldCrate::class);
				self::registerSimpleEntity("DiamondCrate", DiamondCrate::class);
				self::registerSimpleEntity("EmeraldCrate", EmeraldCrate::class);
				self::registerSimpleEntity("DivineCrate", DivineCrate::class);
				self::registerSimpleEntity("VoteCrate", VoteCrate::class);

				self::registerSimpleEntity("IslandPet", IslandPet::class);
				self::registerSimpleEntity("AllayPet", AllayPet::class);
				self::registerSimpleEntity("AxolotlPet", AxolotlPet::class);
				self::registerSimpleEntity("BeePet", BeePet::class);
				self::registerSimpleEntity("CatPet", CatPet::class);
				self::registerSimpleEntity("DogPet", DogPet::class);
				self::registerSimpleEntity("FoxPet", FoxPet::class);
				self::registerSimpleEntity("RabbitPet", RabbitPet::class);
				self::registerSimpleEntity("VexPet", VexPet::class);

				self::registerSimpleEntity("Blaze", Blaze::class);
				self::registerSimpleEntity("Breeze", Breeze::class);
				self::registerSimpleEntity("CaveSpider", CaveSpider::class);
				self::registerSimpleEntity("Creeper", Creeper::class);
				self::registerSimpleEntity("Enderman", Enderman::class);
				self::registerSimpleEntity("Husk", Husk::class);
				self::registerSimpleEntity("Skeleton", Skeleton::class);
				self::registerSimpleEntity("Spider", Spider::class);
				self::registerSimpleEntity("Witch", Witch::class);
				self::registerSimpleEntity("WitherSkeleton", WitherSkeleton::class);
				self::registerSimpleEntity("Zombie", Zombie::class);

				self::registerSimpleEntity("Pig", Pig::class);
				self::registerSimpleEntity("Chicken", Chicken::class);
				self::registerSimpleEntity("Sheep", Sheep::class);
				self::registerSimpleEntity("Cow", Cow::class);
				self::registerSimpleEntity("Mooshroom", Mooshroom::class);
				self::registerSimpleEntity("IronGolem", IronGolem::class);

				self::registerSimpleEntity("SupplyDrop", SupplyDrop::class);
				self::registerSimpleEntity("MoneyBag", MoneyBag::class);
				self::registerSimpleEntity("Turret", Turret::class);

				self::registerSimpleEntity("XpBottle", XpBottle::class);
				self::registerSimpleEntity("TextEntity", TextEntity::class);
				self::registerSimpleEntity("FireworksEntity", FireworksEntity::class);
				break;
			case "lobby":
				self::registerSimpleEntity("Jetski", Jetski::class);
				self::registerHumanEntity("DjSheep", DjSheep::class);
				self::registerSimpleEntity("BattlesBot", BattlesBot::class);
				self::registerSimpleEntity("PrisonBot", PrisonBot::class);
				self::registerSimpleEntity("SkyBlockBot", SkyBlockBot::class);
				self::registerSimpleEntity("Trophy", Trophy::class);
				self::registerSimpleEntity("Cheese", Cheese::class);
				self::registerSimpleEntity("Cheeseburger", Cheeseburger::class);
				self::registerSimpleEntity("Glizzy", Glizzy::class);
				self::registerSimpleEntity("Shoes", Shoes::class);
				self::registerSimpleEntity("Skull", Skull::class);
				break;
		}
	}

	private static function registerSimpleEntity(string $name, string $class): void {
		EntityFactory::getInstance()->register($class, function (World $world, CompoundTag $nbt) use ($class): Entity {
			return new $class(EntityDataHelper::parseLocation($nbt, $world), $nbt);
		}, [$name]);
	}

	private static function registerHumanEntity(string $name, string $class): void {
		EntityFactory::getInstance()->register($class, function (World $world, CompoundTag $nbt) use ($class): Human {
			return new $class(EntityDataHelper::parseLocation($nbt, $world), Human::parseSkinNBT($nbt), $nbt);
		}, [$name]);
	}

	private static function registerCustomEntity(string $name, string $class, callable $factory): void {
		EntityFactory::getInstance()->register($class, $factory, [$name]);
	}
}
