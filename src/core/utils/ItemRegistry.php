<?php

namespace core\utils;

use Exception;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\data\bedrock\block\BlockTypeNames;
use pocketmine\item\ItemTypeIds as Ids;
use pocketmine\inventory\CreativeInventory;
use pocketmine\inventory\ArmorInventory;
use pocketmine\item\Item;
use pocketmine\item\TieredTool as PMTieredTool;
use pocketmine\item\ItemBlock;
use pocketmine\item\StringToItemParser;
use pocketmine\item\VanillaItems;
use pocketmine\item\ArmorTypeInfo;
use pocketmine\item\ItemIdentifier as IID;
use pocketmine\data\bedrock\item\ItemTypeNames as Names;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\world\format\io\GlobalItemDataHandlers;

use core\utils\ItemTypeIds as CustomIds;
use core\cosmetics\item\Bow;
use core\cosmetics\entity\Snowball;
use core\gadgets\item\Firework as FireworkGadget;
use core\items\ControlItem;
use core\items\Elytra;
use core\items\GoldenAppleEnchanted;
use core\items\type\Armor;
use core\items\type\{
	TieredTool,
	Hoe as HoeOverride,
	Pickaxe as PickaxeOverride,
	Shovel as ShovelOverride,
	Axe as AxeOverride,
	Sword as SwordOverride
};
use core\items\WindCharge;
use core\utils\item\component\CanDestroyInCreativeComponent;
use core\utils\item\component\CreativeCategoryComponent;
use core\utils\item\component\DisplayNameComponent;
use core\utils\item\component\HandEquippedComponent;
use core\utils\item\component\IconComponent;
use core\utils\item\component\ItemComponent;
use core\utils\item\component\MaxStackSizeComponent;
use core\utils\item\CreativeInventoryInfo as CreativeInfo;
use core\utils\item\ItemSerializerDeserializerMap;
use Error;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\DyeColorIdMap;
use pocketmine\item\Dye;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\network\mcpe\protocol\types\ItemComponentPacketEntry;
use pocketmine\network\mcpe\protocol\types\ItemTypeEntry;
use pvp\item\FlingBall;
use pvp\item\HealthPotItem;
use pvp\techits\item\TechitNote as PvPTechitNote;
use pvp\enchantments\effects\items\EffectItem as PvPEffectItem;

use skyblock\shop\item\SellWand;
use skyblock\techits\item\TechitNote as SkyBlockTechitNote;
use skyblock\generators\item\GenBooster;
use skyblock\crates\item\KeyNote as SkyblockKeyNote;
use skyblock\enchantments\item\UnboundTome as SkyblockUnboundTome;
use skyblock\item\ArmorStand as SkyBlockArmorStand;
use skyblock\item\ExpBottle as SkyBlockExpBottle;
use skyblock\enchantments\item\EnchantmentBook as SkyblockRedeemedBook;
use skyblock\enchantments\item\CustomDeathTag as SkyblockDeathTag;
use skyblock\enchantments\item\Nametag as SkyblockNametag;
use skyblock\enchantments\item\MaxBook as SkyblockMaxBook;
use skyblock\enchantments\effects\items\EffectItem as SkyBlockEffectItem;

use prison\shops\item\SaleBooster;
use prison\techits\item\TechitNote as PrisonTechitNote;
use prison\mysteryboxes\items\KeyNote as PrisonKeyNote;

use prison\enchantments\effects\items\EffectItem as PrisonEffectItem;
use prison\enchantments\book\RedeemableBook as PrisonRedeemableBook;
use prison\item\Nametag as PrisonNametag;
use prison\item\CustomDeathTag as PrisonDeathTag;
use prison\item\ExpBottle as PrisonExpBottle;
use prison\item\MineNuke;
use prison\item\HasteBomb;
use prison\item\ArmorStand as PrisonArmorStand;
use prison\enchantments\book\RedeemedBook as PrisonRedeemedBook;
use prison\fishing\item\FishingRod as PrisonFishingRod;
use prison\item\EssenceOfAscension as PrisonEssenceOfAscension;
use prison\item\EssenceOfKnowledge as PrisonEssenceOfKnowledge;
use prison\item\EssenceOfProgress;
use prison\item\EssenceOfSuccess as PrisonEssenceOfSuccess;
use prison\item\PouchOfEssence as PrisonPouchOfEssence;
use prison\item\UnboundTome as PrisonUnboundTome;
use RuntimeException;
use skyblock\fishing\item\FishingRod as SkyBlockFishingRod;
use skyblock\generators\item\HorizontalExtender;
use skyblock\generators\item\VerticalExtender;
use skyblock\item\EnderPearl;
use skyblock\item\FireworkRocket;
use skyblock\item\Fireworks;
use skyblock\item\EssenceOfAscension as SkyblockEssenceOfAscension;
use skyblock\item\EssenceOfKnowledge as SkyblockEssenceOfKnowledge;
use skyblock\item\EssenceOfSuccess as SkyblockEssenceOfSuccess;
use skyblock\item\PouchOfEssence as SkyblockPouchOfEssence;
use core\utils\ItemTypeNames as CustomNames;
use pocketmine\block\Block;
use pocketmine\block\BlockToolType;
use pocketmine\data\bedrock\item\BlockItemIdMap;
use pocketmine\inventory\CreativeGroup;
use pocketmine\item\Armor as PMArmor;
use pocketmine\item\Axe;
use pocketmine\item\Hoe;
use pocketmine\item\Pickaxe;
use pocketmine\item\Shears;
use pocketmine\item\Shovel;
use pocketmine\item\SpawnEgg;
use pocketmine\item\Sword;
use pocketmine\item\ToolTier;
use ReflectionClass;
use skyblock\enchantments\EnchantmentData as ED;
use skyblock\generators\item\Solidifier;
use skyblock\kits\item\KitPouch;
use skyblock\koth\item\KothPouch;
use skyblock\pets\item\EnergyBooster;
use skyblock\pets\item\GummyOrb;
use skyblock\pets\item\PetEgg;
use skyblock\pets\item\PetKey;

/**
 * GLOBAL
 * @method static Elytra ELYTRA()
 * @method static Item ENDER_EYE()
 * @method static Item EYE_OF_ENDER()
 * @method static ControlItem DOWN_ARROW()
 * @method static ControlItem UP_ARROW()
 * @method static ControlItem DIAGONAL_LINE_LEFT()
 * @method static ControlItem DIAGONAL_LINE_RIGHT()
 * @method static ControlItem VERTICAL_LINE()
 * @method static ControlItem DIAGONAL_LINE_LEFT_ACTIVE()
 * @method static ControlItem DIAGONAL_LINE_RIGHT_ACTIVE()
 * @method static ControlItem VERTICAL_LINE_ACTIVE()
 * 
 * 
 * LOBBY
 * @method static Bow BOW()
 * @method static FireworkGadget FIREWORKS()
 * @method static Snowball SNOWBALL()
 * 
 * 
 * SKYBLOCK
 * @method static SkyBlockArmorStand|PrisonArmorStand ARMOR_STAND()
 * @method static SellWand SELL_WAND()
 * @method static GenBooster GEN_BOOSTER()
 * @method static SkyblockMaxBook MAX_BOOK()
 * @method static EnderPearl ENDER_PEARL()
 * @method static FireworkRocket FIREWORK_ROCKET()
 * @method static Item WIND_CHARGE()
 * @method static Item BREEZE_ROD()
 * @method static VerticalExtender VERTICAL_EXTENDER()
 * @method static HorizontalExtender HORIZONTAL_EXTENDER()
 * @method static Item WHITE_MUSHROOM()
 * @method static Item WITHERED_BONE()
 * @method static Solidifier SOLIDIFIER()
 * @method static PetKey PET_KEY()
 * @method static EnergyBooster ENERGY_BOOSTER()
 * @method static GummyOrb GUMMY_ORB()
 * @method static PetEgg PET_EGG()
 * @method static KothPouch KOTH_POUCH()
 * @method static KitPouch KIT_POUCH()
 * @method static Item JEWEL_OF_THE_END()
 * 
 * PRISON
 * @method static SaleBooster SALE_BOOSTER()
 * @method static PrisonRedeemableBook REDEEMABLE_BOOK()
 * @method static MineNuke MINE_NUKE()
 * @method static HasteBomb HASTE_BOMB()
 * 
 * 
 * PVP
 * @method static FlingBall FLING_BALL()
 * @method static HealthPotItem HEALTH_POT()
 * 
 * 
 * MULTIPLE
 * @method static GoldenAppleEnchanted ENCHANTED_GOLDEN_APPLE()
 * @method static SkyBlockExpBottle|PrisonExpBottle EXPERIENCE_BOTTLE()
 * @method static PvPTechitNote|SkyBlockTechitNote|PrisonTechitNote TECHIT_NOTE()
 * @method static PrisonKeyNote|SkyblockKeyNote KEY_NOTE()
 * @method static PrisonEffectItem|PvPEffectItem|SkyBlockEffectItem ANIMATOR()
 * @method static PrisonEffectItem|PvPEffectItem EFFECT_ITEM()
 * @method static PrisonUnboundTome|SkyblockUnboundTome|Item UNBOUND_TOME()
 * @method static SkyblockRedeemedBook|PrisonRedeemedBook REDEEMED_BOOK()
 * @method static SkyblockDeathTag|PrisonDeathTag DEATH_TAG()
 * @method static SkyblockDeathTag|PrisonDeathTag CUSTOM_DEATH_TAG()
 * @method static PrisonNametag|SkyblockNametag NAMETAG()
 * @method static PrisonFishingRod|SkyBlockFishingRod FISHING_ROD()
 * @method static PrisonEssenceOfSuccess|SkyblockEssenceOfSuccess ESSENCE_OF_SUCCESS()
 * @method static PrisonEssenceOfKnowledge|SkyblockEssenceOfKnowledge ESSENCE_OF_KNOWLEDGE()
 * @method static PrisonPouchOfEssence|SkyblockPouchOfEssence POUCH_OF_ESSENCE()
 * @method static EssenceOfProgress ESSENCE_OF_PROGRESS()
 * @method static PrisonEssenceOfAscension|SkyblockEssenceOfAscension ESSENCE_OF_ASCENSION()
 * 
 */
class ItemRegistry {

	/** @var Item[] */
	private static array $_registry = [];

	public static array $typeMap = [];
	/** @var ItemTypeEntry[] */
	public static array $itemTypeEntries = [];
	/** @var ItemComponentPacketEntry[] */
	public static array $itemComponents = [];

	public static function setup(string $serverType = "core"): void {
		self::typeMap();
		// DEFAULT //
		self::registerSimpleItem(new GoldenAppleEnchanted(new IID(Ids::ENCHANTED_GOLDEN_APPLE), "Enchanted Golden Apple"), ["enchanted_golden_apple", "egap"]);
		self::registerSimpleItem(new Elytra(new IID(TypeConverter::getInstance()->getItemTypeDictionary()->fromStringId(Names::ELYTRA)), 'Elytra', new ArmorTypeInfo(0, 433, ArmorInventory::SLOT_CHEST)), [Names::ELYTRA, 'elytra']);
		self::registerSimpleItem(new FireworkGadget(new IID(TypeConverter::getInstance()->getItemTypeDictionary()->fromStringId(Names::FIREWORK_ROCKET)), 'Fireworks'), [Names::FIREWORK_ROCKET, 'fireworks']);

		self::registerCustomItem(CustomNames::DOWN_ARROW, new ControlItem(new IID(CustomIds::DOWN_ARROW), "Down Arrow"), ['down_arrow']);
		self::registerCustomItem(CustomNames::UP_ARROW, new ControlItem(new IID(CustomIds::UP_ARROW), "Up Arrow"), ['up_arrow']);
		self::registerCustomItem(CustomNames::DIAGONAL_LINE_LEFT, new ControlItem(new IID(CustomIds::DIAGONAL_LINE_LEFT), "Diagonal Line 45°"), ['diagonal_line_left']);
		self::registerCustomItem(CustomNames::DIAGONAL_LINE_RIGHT, new ControlItem(new IID(CustomIds::DIAGONAL_LINE_RIGHT), "Diagonal Line -45°"), ['diagonal_line_right']);
		self::registerCustomItem(CustomNames::VERTICAL_LINE, new ControlItem(new IID(CustomIds::VERTICAL_LINE), "Vertical Line"), ['vertical_line']);
		self::registerCustomItem(CustomNames::DIAGONAL_LINE_LEFT_ACTIVE, new ControlItem(new IID(CustomIds::DIAGONAL_LINE_LEFT_ACTIVE), "Active Diagonal Line 45°"), ['diagonal_line_left_active']);
		self::registerCustomItem(CustomNames::DIAGONAL_LINE_RIGHT_ACTIVE, new ControlItem(new IID(CustomIds::DIAGONAL_LINE_RIGHT_ACTIVE), "Active Diagonal Line -45°"), ['diagonal_line_right_active']);
		self::registerCustomItem(CustomNames::VERTICAL_LINE_ACTIVE, new ControlItem(new IID(CustomIds::VERTICAL_LINE_ACTIVE), "Active Vertical Line"), ['vertical_line_active']);

		switch ($serverType) {
			case "lobby":
				break;

			case "prison":
				self::registerSimpleItem(new PrisonTechitNote(new IID(CustomIds::TECHIT_NOTE)), ['techit_note']);
				self::registerSimpleItem(new SaleBooster(new IID(Ids::GHAST_TEAR), "Sale Booster"), ['sale_booster']);
				self::registerSimpleItem(new PrisonKeyNote(new IID(Ids::PAPER), "Key Note"), ['key_note']);
				self::registerCustomItem(CustomNames::UNBOUND_TOME, new PrisonUnboundTome(new IID(CustomIds::UNBOUND_TOME), "Unbound Tome"), ['unbound_tome']);
				self::registerSimpleItem(new PrisonEffectItem(new IID(Ids::NETHER_STAR), "Animator"), ['effect_item', 'animator']);
				self::registerSimpleItem(new PrisonRedeemableBook(new IID(Ids::BOOK), "Redeemable Book"), ['redeemable_book']);
				self::registerSimpleItem(new PrisonNametag(new IID(CustomIds::NAMETAG), 'Nametag'), [Names::NAME_TAG, 'nametag']);
				self::registerSimpleItem(new PrisonDeathTag(new IID(Ids::SHULKER_SHELL), "Custom Death Tag"), ['death_tag', 'custom_death_tag']);
				self::registerSimpleItem(new PrisonExpBottle(new IID(Ids::EXPERIENCE_BOTTLE), "XP Bottle"), [Names::EXPERIENCE_BOTTLE, 'experience_bottle']);
				self::registerSimpleItem(new MineNuke(new IID(Ids::MAGMA_CREAM), "Mine Nuke"), ['mine_nuke']);
				self::registerSimpleItem(new HasteBomb(new IID(CustomIds::HASTE_BOMB), "Haste Bomb"), ['haste_bomb']);
				self::registerSimpleItem(new PrisonArmorStand(new IID(CustomIds::ARMOR_STAND), "Armor Stand"), [Names::ARMOR_STAND, 'armor_stand']);
				self::registerSimpleItem(new PrisonRedeemedBook(new IID(Ids::ENCHANTED_BOOK), "Redeemed Book"), ['redeemed_book']);
				self::registerSimpleItem(new PrisonFishingRod(new IID(Ids::FISHING_ROD), "Fishing Rod"), ['fishing_rod']);
				self::registerCustomItem(CustomNames::ESSENCE_OF_SUCCESS, new PrisonEssenceOfSuccess(new IID(CustomIds::ESSENCE_OF_SUCCESS), 'Essence of Success'), ['essence_of_success']);
				self::registerCustomItem(CustomNames::ESSENCE_OF_KNOWLEDGE, new PrisonEssenceOfKnowledge(new IID(CustomIds::ESSENCE_OF_KNOWLEDGE), 'Essence of Knowledge'), ['essence_of_knowledge']);
				self::registerCustomItem(CustomNames::POUCH_OF_ESSENCE, new PrisonPouchOfEssence(new IID(CustomIds::POUCH_OF_ESSENCE), 'Pouch of Essence'), ['pouch_of_essence']);
				self::registerCustomItem(CustomNames::ESSENCE_OF_PROGRESS, new EssenceOfProgress(new IID(CustomIds::ESSENCE_OF_PROGRESS), 'Essence of Progress'), ['essence_of_progress']);
				self::registerCustomItem(CustomNames::ESSENCE_OF_ASCENSION, new PrisonEssenceOfAscension(new IID(CustomIds::ESSENCE_OF_ASCENSION), 'Essence of Ascension'), ['essence_of_ascension']);
				break;

			case "skyblock":
				self::registerCustomItem(CustomNames::BREEZE_ROD, new Item(new IID(CustomIds::BREEZE_ROD), "Breeze Rod"), ["breeze_rod"]);
				self::registerSimpleItem(new WindCharge(new IID(CustomIds::WIND_CHARGE), "Wind Charge"), ["wind_charge"]);

				self::registerCustomItem(CustomNames::WHITE_MUSHROOM, new Item(new IID(CustomIds::WHITE_MUSHROOM), "White Mushroom"), ["white_mushroom"]);
				self::registerCustomItem(CustomNames::WITHERED_BONE, new Item(new IID(CustomIds::WITHERED_BONE), "Withered Bone"), ["withered_bone"]);
				self::registerCustomItem(CustomNames::PET_ENERGY_BOOSTER, new EnergyBooster(new IID(CustomIds::PET_ENERGY_BOOSTER), "Energy Booster"), ["energy_booster"]);
				self::registerCustomItem(CustomNames::PET_GUMMY_ORB, new GummyOrb(new IID(CustomIds::PET_GUMMY_ORB), "Gummy Orb"), ["gummy_orb"]);
				self::registerCustomItem(CustomNames::PET_KEY, new PetKey(new IID(CustomIds::PET_KEY), "Pet Key"), ["pet_key"]);
				self::registerCustomItem(CustomNames::SOLIDIFIER, new Solidifier(new IID(CustomIds::SOLIDIFIER), "Solidifier"), ["solidifier"]);

				self::registerCustomItem(CustomNames::VERTICAL_EXTENDER, new VerticalExtender(new IID(CustomIds::VERTICAL_EXTENDER), "Vertical Extender"), ["vertical_extender"]);
				self::registerCustomItem(CustomNames::HORIZONTAL_EXTENDER, new HorizontalExtender(new IID(CustomIds::HORIZONTAL_EXTENDER), "Horizontal Extender"), ["horizontal_extender"]);
				self::registerSimpleItem(new SkyBlockEffectItem(new IID(Ids::NETHER_STAR), "Animator"), ['effect_item', 'animator']);
				self::registerSimpleItem(new SkyBlockExpBottle(new IID(Ids::EXPERIENCE_BOTTLE), "Bottle o' Enchanting"), ['experience_bottle', 'bottle_o_enchanting']);
				self::registerSimpleItem(new Item(new IID(CustomIds::EYE_OF_ENDER), 'Eye of Ender'), ['eye_of_ender', 'ender_eye']);
				self::registerSimpleItem(new SkyBlockArmorStand(new IID(CustomIds::ARMOR_STAND), 'Armor Stand'), ['armor_stand']);
				self::registerCustomItem(CustomNames::SELL_WAND, new SellWand(new IID(CustomIds::SELL_WAND), 'Sell Wand'), ['sell_wand']);
				self::registerSimpleItem(new SkyBlockTechitNote(new IID(CustomIds::TECHIT_NOTE)), ['techit_note']);
				self::registerSimpleItem(new GenBooster(new IID(Ids::SCUTE), "Gen Booster"), ['gen_booster']);
				self::registerSimpleItem(new SkyblockKeyNote(new IID(Ids::PAPER), "Key Note"), ['key_note']);
				self::registerCustomItem(CustomNames::UNBOUND_TOME, new SkyblockUnboundTome(new IID(CustomIds::UNBOUND_TOME), "Unbound Tome"), ['unbound_tome']);
				self::registerSimpleItem(new SkyblockRedeemedBook(new IID(Ids::ENCHANTED_BOOK), "Redeemed Book"), ['redeemed_book']);
				self::registerSimpleItem(new SkyblockMaxBook(new IID(Ids::BOOK), "Max Book"), ['max_book', 'redeemable_book']);

				for ($i = 1; $i <= 5; $i++) {
					self::addToParser(strtolower(ED::rarityName($i)) . "_redeemed_book", self::REDEEMED_BOOK()->setRarity($i));
					self::addToParser(strtolower(ED::rarityName($i)) . "_max_book", self::MAX_BOOK()->setRarity($i));
				}

				self::registerSimpleItem(new SkyblockDeathTag(new IID(Ids::SHULKER_SHELL), "Custom Death Tag"), ['death_tag', 'custom_death_tag']);
				self::registerSimpleItem(new SkyblockNametag(new IID(CustomIds::NAMETAG), 'Nametag'), [Names::NAME_TAG, 'nametag']);
				self::registerSimpleItem(new SkyBlockFishingRod(new IID(Ids::FISHING_ROD), "Fishing Rod"), ['fishing_rod']);
				self::registerSimpleItem(new EnderPearl(new IID(Ids::ENDER_PEARL), "Ender Pearl"), ['ender_pearl']);
				self::registerSimpleItem(new FireworkRocket(new IID(CustomIds::FIREWORK_ROCKET), "Firework Rocket"), ["firework_rocket"]);
				self::registerCustomItem(CustomNames::ESSENCE_OF_SUCCESS, new SkyblockEssenceOfSuccess(new IID(CustomIds::ESSENCE_OF_SUCCESS), 'Essence of Success'), ['essence_of_success']);
				self::registerCustomItem(CustomNames::ESSENCE_OF_KNOWLEDGE, new SkyblockEssenceOfKnowledge(new IID(CustomIds::ESSENCE_OF_KNOWLEDGE), 'Essence of Knowledge'), ['essence_of_knowledge']);
				self::registerCustomItem(CustomNames::POUCH_OF_ESSENCE, new SkyblockPouchOfEssence(new IID(CustomIds::POUCH_OF_ESSENCE), 'Pouch of Essence'), ['pouch_of_essence']);
				self::registerCustomItem(CustomNames::ESSENCE_OF_ASCENSION, new SkyblockEssenceOfAscension(new IID(CustomIds::ESSENCE_OF_ASCENSION), 'Essence of Ascension'), ['essence_of_ascension']);

				foreach (DyeColor::getAll() as $color) {
					foreach (Fireworks::TYPES as $name => $id) {
						StringToItemParser::getInstance()->register($color->name() . "_" . $name . "_firework_rocket", fn() => self::FIREWORK_ROCKET()->setExplosion($id, $color)->setFlightDuration(1));
					}
				}

				self::registerCustomItem(Names::NPC_SPAWN_EGG, new PetEgg(new IID(CustomIds::PET_SPAWN_EGG)), ["pet_spawn_egg", "pet_egg"]);
				self::registerCustomItem(CustomNames::KOTH_POUCH, new KothPouch(new IID(CustomIds::KOTH_POUCH), 'Koth Pouch'), ["koth_pouch"]);
				self::registerCustomItem(CustomNames::KIT_POUCH, new KitPouch(new IID(CustomIds::KIT_POUCH), 'Kit Pouch'), ["kit_pouch"]);
				self::registerCustomItem(CustomNames::JEWEL_OF_THE_END, new Item(new IID(CustomIds::JEWEL_OF_THE_END), 'Jewel of the End'), ["jewel_of_the_end"]);
				break;

			case "pvp":
				self::registerSimpleItem(new HealthPotItem, ['health_pot']);
				self::registerCustomItem('fling_ball', new FlingBall(new IID(Ids::SNOWBALL), 'Fling Ball'), ['fling_ball']);
				self::registerCustomItem('techit_note', new PvPTechitNote(new IID(CustomIds::TECHIT_NOTE)), ['techit_note']);
				// self::registerCustomItem('effect_item', new PvPEffectItem(new IID(Ids::NETHER_STAR), "Animator"), ['effect_item', 'animator']);
				break;

			case "faction":
				self::registerCustomItem(CustomNames::UNBOUND_TOME, new Item(new IID(CustomIds::UNBOUND_TOME), "Test Item"), ["unbound_tome"]);
				break;
		}

		ItemSerializerDeserializerMap::setup($serverType);

		// Components
		self::registerComponents(CustomNames::UNBOUND_TOME, CustomIds::UNBOUND_TOME, [
			new DisplayNameComponent("Unbound Tome"),
			new IconComponent("avengetech:unbound_tome"),
			new MaxStackSizeComponent(64)
		]);
		self::registerComponents(CustomNames::ESSENCE_OF_SUCCESS, CustomIds::ESSENCE_OF_SUCCESS, [
			new DisplayNameComponent("Essence Of Success"),
			new IconComponent("avengetech:essence_of_success"),
			new MaxStackSizeComponent(64)
		]);
		self::registerComponents(CustomNames::ESSENCE_OF_KNOWLEDGE, CustomIds::ESSENCE_OF_KNOWLEDGE, [
			new DisplayNameComponent("Essence Of Knowledge"),
			new IconComponent("avengetech:essence_of_knowledge"),
			new MaxStackSizeComponent(64)
		]);
		self::registerComponents(CustomNames::ESSENCE_OF_PROGRESS, CustomIds::ESSENCE_OF_PROGRESS, [
			new DisplayNameComponent("Essence Of Progress"),
			new IconComponent("avengetech:essence_of_progress"),
			new MaxStackSizeComponent(64)
		]);
		self::registerComponents(CustomNames::ESSENCE_OF_ASCENSION, CustomIds::ESSENCE_OF_ASCENSION, [
			new DisplayNameComponent("Essence Of Ascension"),
			new IconComponent("avengetech:essence_of_ascension"),
			new MaxStackSizeComponent(64)
		]);
		self::registerComponents(CustomNames::POUCH_OF_ESSENCE, CustomIds::POUCH_OF_ESSENCE, [
			new DisplayNameComponent("Pouch Of Essence"),
			new IconComponent("avengetech:essence"),
			new MaxStackSizeComponent(64)
		]);
		self::registerComponents(CustomNames::BREEZE_ROD, CustomIds::BREEZE_ROD, [
			new DisplayNameComponent("Breeze Rod"),
			new IconComponent("avengetech:temp_breeze_rod"),
			new CreativeCategoryComponent(new CreativeInfo(CreativeInfo::CATEGORY_ITEMS, CreativeInfo::NONE)),
			new MaxStackSizeComponent(64)
		]);
		self::registerComponents(CustomNames::SELL_WAND, CustomIds::SELL_WAND, [
			new DisplayNameComponent("Sell Wand"),
			new IconComponent("avengetech:sell_wand"),
			new MaxStackSizeComponent(64),
			new HandEquippedComponent(true),
		]);
		self::registerComponents(CustomNames::VERTICAL_EXTENDER, CustomIds::VERTICAL_EXTENDER, [
			new DisplayNameComponent("Vertical Extender"),
			new IconComponent("avengetech:vertical_extender"),
			new MaxStackSizeComponent(8)
		]);
		self::registerComponents(CustomNames::HORIZONTAL_EXTENDER, CustomIds::HORIZONTAL_EXTENDER, [
			new DisplayNameComponent("Horizontal Extender"),
			new IconComponent("avengetech:horizontal_extender"),
			new MaxStackSizeComponent(8)
		]);
		self::registerComponents(CustomNames::SOLIDIFIER, CustomIds::SOLIDIFIER, [
			new DisplayNameComponent("Solidifier"),
			new IconComponent("avengetech:solidifier"),
			new MaxStackSizeComponent(64)
		]);
		self::registerComponents(CustomNames::WHITE_MUSHROOM, CustomIds::WHITE_MUSHROOM, [
			new DisplayNameComponent("White Mushroom"),
			new IconComponent("avengetech:white_mushroom"),
			new MaxStackSizeComponent(64)
		]);
		self::registerComponents(CustomNames::WITHERED_BONE, CustomIds::WITHERED_BONE, [
			new DisplayNameComponent("Withered Bone"),
			new IconComponent("avengetech:withered_bone"),
			new MaxStackSizeComponent(64)
		]);
		self::registerComponents(CustomNames::PET_ENERGY_BOOSTER, CustomIds::PET_ENERGY_BOOSTER, [
			new DisplayNameComponent("Pet Booster"),
			new IconComponent("avengetech:pet_booster"),
			new MaxStackSizeComponent(64)
		]);
		self::registerComponents(CustomNames::PET_KEY, CustomIds::PET_KEY, [
			new DisplayNameComponent("Pet Key"),
			new IconComponent("avengetech:pet_key"),
			new MaxStackSizeComponent(16)
		]);
		self::registerComponents(CustomNames::KIT_POUCH, CustomIds::KIT_POUCH, [
			new DisplayNameComponent("Kit Pouch"),
			new IconComponent("avengetech:kit_pouch"),
			new MaxStackSizeComponent(1)
		]);
		self::registerComponents(CustomNames::KOTH_POUCH, CustomIds::KOTH_POUCH, [
			new DisplayNameComponent("Koth Pouch"),
			new IconComponent("avengetech:koth_pouch"),
			new MaxStackSizeComponent(1)
		]);
		self::registerComponents(CustomNames::JEWEL_OF_THE_END, CustomIds::JEWEL_OF_THE_END, [
			new DisplayNameComponent("Jewel of the End"),
			new IconComponent("avengetech:ender_jewel"),
			new MaxStackSizeComponent(64)
		]);
		self::registerComponents(CustomNames::PET_GUMMY_ORB, CustomIds::PET_GUMMY_ORB, [
			new DisplayNameComponent("Gummy Orb"),
			new IconComponent("avengetech:gummy_orb"),
			new MaxStackSizeComponent(64)
		]);

		self::registerComponents(CustomNames::UP_ARROW, CustomIds::UP_ARROW, [
			new DisplayNameComponent("Up Arrow"),
			new IconComponent("avengetech:up_arrow"),
			new MaxStackSizeComponent(1)
		]);
		self::registerComponents(CustomNames::DOWN_ARROW, CustomIds::DOWN_ARROW, [
			new DisplayNameComponent("Down Arrow"),
			new IconComponent("avengetech:down_arrow"),
			new MaxStackSizeComponent(1)
		]);
		self::registerComponents(CustomNames::VERTICAL_LINE, CustomIds::VERTICAL_LINE, [
			new DisplayNameComponent("Vertical Line"),
			new IconComponent("avengetech:vertical_line"),
			new MaxStackSizeComponent(1)
		]);
		self::registerComponents(CustomNames::VERTICAL_LINE_ACTIVE, CustomIds::VERTICAL_LINE_ACTIVE, [
			new DisplayNameComponent("Active Vertical Line"),
			new IconComponent("avengetech:vertical_line_active"),
			new MaxStackSizeComponent(1)
		]);
		self::registerComponents(CustomNames::DIAGONAL_LINE_LEFT, CustomIds::DIAGONAL_LINE_LEFT, [
			new DisplayNameComponent("Diagonal Line 45°"),
			new IconComponent("avengetech:diagonal_line_left"),
			new MaxStackSizeComponent(1)
		]);
		self::registerComponents(CustomNames::DIAGONAL_LINE_LEFT_ACTIVE, CustomIds::DIAGONAL_LINE_LEFT_ACTIVE, [
			new DisplayNameComponent("Active Diagonal Line 45°"),
			new IconComponent("avengetech:diagonal_line_left_active"),
			new MaxStackSizeComponent(1)
		]);
		self::registerComponents(CustomNames::DIAGONAL_LINE_RIGHT, CustomIds::DIAGONAL_LINE_RIGHT, [
			new DisplayNameComponent("Diagonal Line -45°"),
			new IconComponent("avengetech:diagonal_line_right"),
			new MaxStackSizeComponent(1)
		]);
		self::registerComponents(CustomNames::DIAGONAL_LINE_RIGHT_ACTIVE, CustomIds::DIAGONAL_LINE_RIGHT_ACTIVE, [
			new DisplayNameComponent("Active Diagonal Line -45°"),
			new IconComponent("avengetech:diagonal_line_right_active"),
			new MaxStackSizeComponent(1)
		]);
	}

	public static function registerSimpleItem(Item $item, array $stringToItemParserNames = []): void {
		if (CreativeInventory::getInstance()->contains($item)) CreativeInventory::getInstance()->remove($item);

		CreativeInventory::getInstance()->add($item);

		foreach ($stringToItemParserNames as $name) {
			self::_registryRegister($name, $item);
			self::addToParser($name, $item);
		}
	}

	public static function registerCustomItem(string $identifier, Item $item, array $stringToItemParserNames): void {
		self::registerCustomItemMapping($identifier, $item->getTypeId());
		self::registerSimpleItem($item, $stringToItemParserNames);
	}

	public static function registerItemBlock(string $identifier, Block $block, array $stringToItemParserNames): void {
		ItemSerializerDeserializerMap::map1to1Block($identifier, $block);

		if (CreativeInventory::getInstance()->contains($block->asItem())) CreativeInventory::getInstance()->remove($block->asItem());

		CreativeInventory::getInstance()->add($block->asItem());

		foreach ($stringToItemParserNames as $name) self::addToParser($name, $block->asItem());
	}

	public static function registerCustomItemBlock(string $identifier, Block $block, array $stringToItemParserNames): void {
		self::registerCustomItemMapping($identifier, $block->getIdInfo()->getBlockTypeId());
		self::registerItemBlock($identifier, $block, $stringToItemParserNames);

		self::$itemTypeEntries[$identifier] = new ItemTypeEntry(
			$identifier, $block->getIdInfo()->getBlockTypeId(), 
			false, 2,
			new CacheableNbt(CompoundTag::create())
		);

		$blockItemIdMap = BlockItemIdMap::getInstance();
		$reflection = new ReflectionClass($blockItemIdMap);

		$itemToBlockId = $reflection->getProperty("itemToBlockId");
		/** @var string[] $value */
		$value = $itemToBlockId->getValue($blockItemIdMap);
		$itemToBlockId->setValue($blockItemIdMap, $value + [$identifier => $identifier]);
	}

	/** @param ItemComponent[] $itemComponents */
	public static function registerComponents(string $identifier, int $typeId, array $itemComponents): void {
		$components = CompoundTag::create();
		$properties = CompoundTag::create();

		foreach($itemComponents as $component){
			$tag = Utils::getTagType($component->getValue());
			if ($tag === null) {
				throw new RuntimeException("Failed to get tag type for component " . $component->getName());
			}
			if ($component->isProperty()) {
				$properties->setTag($component->getName(), $tag);
				continue;
			}
			$components->setTag($component->getName(), $tag);
		}
		$components->setTag("item_properties", $properties);

		self::$itemTypeEntries[$identifier] = new ItemTypeEntry(
			$identifier, $typeId, 
			true, 1, 
			new CacheableNbt(
				CompoundTag::create()
				->setTag("components", $components)
				->setInt("id", $typeId)
				->setString("name", $identifier)
			)
		);
	}

	public static function addToParser(string $name, Item $item): void {
		try {
			StringToItemParser::getInstance()->register($name, fn() => clone $item);
		} catch (\InvalidArgumentException) {
			StringToItemParser::getInstance()->override($name, fn() => clone $item);
		}
	}

	private static function registerCustomItemMapping(string $identifier, int $itemId): void {
		$dictionary = TypeConverter::getInstance()->getItemTypeDictionary();
		$reflection = new \ReflectionClass($dictionary);

		$intToString = $reflection->getProperty("intToStringIdMap");
		/** @var int[] $value */
		$value = $intToString->getValue($dictionary);
		$intToString->setValue($dictionary, $value + [$itemId => $identifier]);

		$stringToInt = $reflection->getProperty("stringToIntMap");
		/** @var int[] $value */
		$value = $stringToInt->getValue($dictionary);
		$stringToInt->setValue($dictionary, $value + [$identifier => $itemId]);
	}

	public static function getItemById(int $id, int $meta = -1, int $count = 1): ?Item {
		if ($id < 0) return BlockRegistry::getBlockById(-$id, $meta)?->asItem();
		foreach (array_merge(VanillaItems::getAll(), self::_registryGetAll()) as $sid => $item) {
			try {
				$d = GlobalItemDataHandlers::getSerializer()->serializeType($item);
			} catch (Exception) {
				$d = new SavedItemData('air');
			}
			if (($item->getTypeId() == $id) && ($meta < 0 || $d->getMeta() == $meta)) {
				//var_dump($prefix . $sid);
				return $item->setCount($count);
			}
		}
		return null;
	}

	private static function typeMap(): void {
		self::$typeMap = [
			'dye' => function (int $colorId): Dye {
				return VanillaItems::DYE()->setColor(DyeColorIdMap::getInstance()->fromId($colorId) ?? DyeColor::WHITE());
			}
		];
	}

	public static function getItem(string $id, int $meta = -1, int $count = 1): ?Item {
		if (strlen(trim($id)) < 1) return null;
		if (str_starts_with($id, 'minecraft:')) {
			$stripped = trim(str_replace('minecraft:', "", $id));
			if (isset(self::$typeMap[$stripped])) {
				return (self::$typeMap[$stripped])($meta);
			}
			$_e = explode('_', $stripped);
			while (count($_e) < 2) $_e[] = "";
			$start = $_e[0];
			$endType = $_e[1];
			if (isset(self::$typeMap[$start])) {
				return (self::$typeMap[$start])($meta, $endType);
			}
		}
		// var_dump(self::class . "::getItem ID => $id");
		foreach (array_merge(VanillaItems::getAll(), self::_registryGetAll()) as $sid => $item) {
			try {
				$d = GlobalItemDataHandlers::getSerializer()->serializeType($item);
			} catch (Exception $e) {
				$d = new SavedItemData('air');
			}
			if ((strtolower(substr($d->getName(), strlen('minecraft:'))) == strtolower($id) || strtolower($d->getName()) == strtolower($id) || 'minecraft:' . strtolower($sid) == strtolower($id) || strtolower($sid) == strtolower($id)) && ($meta < 0 || $d->getMeta() == $meta)) {
				$i = clone (self::_registryGet(strtolower($id)) ?? $item);
				// var_dump(self::class . "::getItem ITEM CLASS => " . $i::class);
				$i->setCount($count);
				return $i;
			}
		}
		return null;
	}

	public static function findItem(int|string $id, bool $forceBlock = false): ?Item {
		try {
			if ($forceBlock) throw new Error;
			return self::__callStatic($id, []);
		} catch (Exception | Error $e) {
			try {
				$blockType = BlockRegistry::__callStatic($id, [])->asItem();
				if (is_null($blockType)) throw new Error;
				else return $blockType;
			} catch (Exception | Error $e) {
				try {
					$blockType = VanillaBlocks::__callStatic($id, [])->asItem();
					if (is_null($blockType)) throw new Error;
					else return $blockType;
				} catch (Exception | Error $e) {
					try {
						if ($forceBlock) throw new Error;
						return VanillaItems::__callStatic($id, []);
					} catch (Exception | Error $e) {
						if (is_numeric($id)) return self::getItemById($id);
						return null;
					}
				}
			}
		}
	}

	private static function _registryRegister(string $identifier, Item $item) {
		$identifier = strtoupper($identifier);

		self::$_registry[$identifier] = $item;
	}

	private static function _registryGetAll(): array {
		return self::$_registry;
	}

	private static function _registryGet(string $identifier): ?Item {
		$identifier = strtoupper($identifier);

		return self::$_registry[$identifier] ?? null;
	}

	/** @return Item[] */
	public static function getAll(): array {
		return self::_registryGetAll();
	}

	public static function __callStatic($name, $arguments) {
		$b = self::_registryGet($name);

		if (!is_null($b)) return clone $b;

		throw new Error("Item \"" . $name . "\" does not exist within the ItemRegistry");
	}


	#region CUSTOM TOOL/ARMOR STUFF
	public static function convertToETool(PMTieredTool $tool): TieredTool|PMTieredTool {
		if ($tool->getNamedTag()->getByte('buildertools', false)) return $tool;
		if (!$tool instanceof PMTieredTool) return $tool;
		$class = match ($tool::class) {
			Axe::class => AxeOverride::class,
			Hoe::class => HoeOverride::class,
			Pickaxe::class => PickaxeOverride::class,
			Shovel::class => ShovelOverride::class,
			Sword::class => SwordOverride::class,
			default => TieredTool::class
		};
		/** @var AxeOverride|HoeOverride|PickaxeOverride|ShovelOverride|SwordOverride|TieredTool $newTool */
		$newTool = new $class(new IID($tool->getTypeId()), $tool->getVanillaName(), $tool->getTier());
		$newTool->setTier($tool->getTier());
		$newTool->setCustomName($tool->getName());
		$newTool->setNamedTag($tool->getNamedTag());
		$newTool->setLore($tool->getLore());
		$newTool->setDamage($tool->getDamage());
		if ($newTool instanceof TieredTool) {
			$vanillaItem = VanillaItems::getAll()[str_replace([" ", "GOLD"], ["_", "GOLDEN"], str_replace("GOLDEN", "GOLD", strtoupper($tool->getVanillaName())))];
			switch (true) {
				case $vanillaItem instanceof Pickaxe:
					$newTool->setToolType(BlockToolType::PICKAXE);
					break;
				case $vanillaItem instanceof Axe:
					$newTool->setToolType(BlockToolType::AXE);
					break;
				case $vanillaItem instanceof Shovel:
					$newTool->setToolType(BlockToolType::SHOVEL);
					break;
				case $vanillaItem instanceof Hoe:
					$newTool->setToolType(BlockToolType::HOE);
					break;
				case $vanillaItem instanceof Shears:
					$newTool->setToolType(BlockToolType::SHEARS);
					break;
				case $vanillaItem instanceof Sword:
					$newTool->setToolType(BlockToolType::SWORD);
					break;
				default:
					$newTool->setToolType(BlockToolType::NONE);
					break;
			}
			$newTool->setHarvestLevel($tool->getTier()->getHarvestLevel());
		}
		return $newTool;
	}

	public static function convertToPMTool(TieredTool $tool) : TieredTool|PMTieredTool{
		if($tool->getNamedTag()->getByte('buildertools', false)) return $tool;
		$reflector = new ReflectionClass($tool);
		$itemReflect = new ReflectionClass(Item::class);
		foreach ($reflector->getProperties() as $property) {
			$property->setAccessible(true);
		}
		foreach ($itemReflect->getProperties() as $property) {
			$property->setAccessible(true);
		}

		$newTool = match(true){
			TieredTool::isAxe($tool) => new Axe($itemReflect->getProperty('identifier')->getValue($tool), $tool->getVanillaName(), $tool->getTier()),
			TieredTool::isHoe($tool) => new Hoe($itemReflect->getProperty('identifier')->getValue($tool), $tool->getVanillaName(), $tool->getTier()),
			TieredTool::isPickaxe($tool) => new Pickaxe($itemReflect->getProperty('identifier')->getValue($tool), $tool->getVanillaName(), $tool->getTier()),
			TieredTool::isShovel($tool) => new Shovel($itemReflect->getProperty('identifier')->getValue($tool), $tool->getVanillaName(), $tool->getTier()),
			TieredTool::isSword($tool) => new Sword($itemReflect->getProperty('identifier')->getValue($tool), $tool->getVanillaName(), $tool->getTier()),
			default => new PMTieredTool($itemReflect->getProperty('identifier')->getValue($tool), $tool->getVanillaName(), $tool->getTier())
		};

		$newTool->setCustomName($tool->getName());
		$newTool->setNamedTag($tool->getNamedTag());
		$newTool->setLore($tool->getLore());
		$newTool->setDamage($tool->getDamage());

		return $newTool;
	}

	public static function fixFuckedItem(Item &$item): Item {
		if (!($item instanceof PMTieredTool || $item instanceof TieredTool)) return $item;
		if ($item->getNamedTag()->getByte('buildertools', false)) return $item;

		if ($item instanceof TieredTool && (TieredTool::isAxe($item) || TieredTool::isHoe($item) || TieredTool::isPickaxe($item) || TieredTool::isShovel($item) || TieredTool::isSword($item))) {
			$item = self::convertToETool(self::convertToPMTool($item));
		}
		return $item;
	}

	public static function convertToEArmor(PMArmor $armor): Armor|PMArmor {
		if ($armor instanceof Elytra) return $armor;
		$reflector = new ReflectionClass($armor);
		$itemReflect = new ReflectionClass(Item::class);
		foreach ($reflector->getProperties() as $property) {
			$property->setAccessible(true);
		}
		foreach ($itemReflect->getProperties() as $property) {
			$property->setAccessible(true);
		}
		$info = $reflector->getProperty('armorInfo')->getValue($armor);
		$newArmor = new Armor($itemReflect->getProperty('identifier')->getValue($armor), $armor->getVanillaName(), $info);
		$newArmor->setCustomName($armor->getName());
		$newArmor->setNamedTag($armor->getNamedTag());
		$newArmor->setLore($armor->getLore());
		$newArmor->setDamage($armor->getDamage());
		return $newArmor;
	}
	#endregion
}
