<?php

namespace core\utils;

use core\block\Chest;
use core\block\EnderChest;
use core\block\EndGateway;
use core\utils\conversion\LegacyBlockIds;

use pocketmine\block\Block;
use pocketmine\block\BlockBreakInfo as BreakInfo;
use pocketmine\block\BlockIdentifier as BID;
use pocketmine\block\BlockTypeIds as Ids;
use pocketmine\block\BlockTypeInfo as Info;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\block\VanillaBlocks;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\data\bedrock\block\BlockTypeNames as Names;
use pocketmine\item\Item;
use pocketmine\item\ToolTier;
use pocketmine\block\NetherReactor;
use pocketmine\block\WeightedPressurePlate;

use lobby\block\IronPressurePlate as LobbyIPressurePlate;
use lobby\block\GoldPressurePlate as LobbyGPressurePlate;
use pocketmine\block\Concrete;
use pocketmine\block\ConcretePowder;
use pocketmine\block\Element;
use pocketmine\block\Flower;
use pocketmine\block\MobHead;
use pocketmine\block\Sapling;
use pocketmine\block\StainedGlass;
use pocketmine\block\StainedHardenedClay;
use pocketmine\block\Wool;
use pocketmine\data\bedrock\block\BlockStateData as StateData;
use pocketmine\data\bedrock\DyeColorIdMap;
use pocketmine\data\bedrock\MobHeadTypeIdMap;
use pocketmine\utils\AssumptionFailedError;
use skyblock\hoppers\HopperBlock as Hopper;
use skyblock\spawners\block\MobSpawner;
use skyblock\spawners\tile\Spawner;
use skyblock\generators\block\AutoMiner;
use skyblock\generators\tile\AutoMiner as AutoMinerTile;
use skyblock\generators\block\OreGenerator;
use skyblock\generators\tile\OreGenerator as OreGeneratorTile;
use skyblock\generators\block\DimensionalBlock;
use skyblock\generators\tile\DimensionalTile;
use skyblock\block\GoldPressurePlate as SkyblockGPressurePlate;
use skyblock\parkour\block\IronPressurePlate as SkyblockIPressurePlate;
use skyblock\islands\warp\block\StonePressurePlate as SkyblockSPressurePlate;

use core\block\EndPortal;
use skyblock\hoppers\tile\HopperTile;

use core\block\tile\Chest as TileChest;
use core\block\tile\EnderChest as TileEnderChest;
use core\utils\block\BlockDeserializerMap;
use core\utils\block\BlockPalette;
use core\utils\block\BlockSerializerMap;
use core\utils\block\Material;
use core\utils\block\Model;
use core\utils\item\CreativeInventoryInfo;
use Error;
use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\BlockPaletteEntry;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use skyblock\pets\block\PetBox;
use core\utils\BlockTypeNames as CustomNames;
use faction\block\Bedrock;
use faction\block\Obsidian;
use faction\block\tile\BreakableBlockTile;
use pocketmine\block\BlockToolType as ToolType;
use pocketmine\block\BlockTypeTags as Tags;
use pocketmine\block\Opaque;
use skyblock\block\BrownMushroom;
use skyblock\block\BrownMushroomBlock;
use skyblock\block\RedMushroom;
use skyblock\block\RedMushroomBlock;

/**
 * @method static Block CONDUIT()
 * @method static NetherReactor NETHERREACTOR()
 * @method static LobbyIPressurePlate|SkyblockIPressurePlate IRON_PRESSURE_PLATE()
 * @method static LobbyGPressurePlate|WeightedPressurePlate GOLD_PRESSURE_PLATE()
 * @method static SkyblockSPressurePlate STONE_PRESSURE_PLATE()
 * @method static Hopper HOPPER()
 * @method static MobSpawner MOB_SPAWNER()
 * @method static AutoMiner AUTOMINER()
 * @method static OreGenerator ORE_GENERATOR()
 * @method static DimensionalBlock DIMENSIONAL_BLOCK()
 * @method static EndPortal END_PORTAL()
 * @method static EndGateway END_GATEWAY()
 * 
 * @method static Chest CHEST()
 * @method static EnderChest ENDER_CHEST()
 * 
 * @method static PetBox PET_BOX()
 * 
 * @method static Opaque OBSIDIAN()
 * @method static Opaque BLOOD_INFUSED_OBSIDIAN()
 * @method static Opaque POLISHED_BLOOD_INFUSED_OBSIDIAN()
 * @method static Opaque GILDED_OBSIDIAN()
 * @method static Opaque POLISHED_GILDED_OBSIDIAN()
 * @method static Opaque POLISHED_OBSIDIAN()
 * @method static Opaque POLISHED_GLOWING_OBSIDIAN()
 * @method static Opaque PERIDOT()
 * @method static Opaque TOPAZ()
 * 
 * @method static RedMushroom RED_MUSHROOM()
 * @method static RedMushroomBlock RED_MUSHROOM_BLOCK()
 * @method static BrownMushroom BROWN_MUSHROOM()
 * @method static BrownMushroomBlock BROWN_MUSHROOM_BLOCK()
 * 
 * 
 * FACTIONS
 * @method static Bedrock BEDROCK()
 * @method static Obsidian OBSIDIAN()
 * 
 * CUSTOM BLOCKS CAN NOT HAVE CUSTOM IDS, use Ids::newId()
 */
class BlockRegistry{

	/** @var BlockPaletteEntry[] */
	public static array $blockPaletteEntries = [];
	/** @var Item[] */
	private static array $_registry = [];
	public static array $typeMap = [];

	public static function setup(string $serverType = "core"): void {
		self::typeMap();

		self::registerBlock(
			Names::CHEST, 
			new Chest(new BID(Ids::CHEST, TileChest::class), "Chest", new Info(BreakInfo::axe(2.5))), 
			[Names::CHEST, "chest"]
		);
		self::registerBlock(
			Names::ENDER_CHEST, 
			new EnderChest(new BID(Ids::ENDER_CHEST, TileEnderChest::class), "Ender Chest", new Info(BreakInfo::pickaxe(22.5, ToolTier::WOOD, 3000.0))), 
			[Names::ENDER_CHEST, "ender_chest"]
		);
		self::registerBlock(
			Names::CONDUIT, 
			new Block(new BID(Ids::newId()), 'Conduit', new Info(BreakInfo::pickaxe(3.0, ToolTier::IRON(), 3.0))), 
			[Names::CONDUIT, 'conduit']
		);
		self::registerBlock(
			Names::NETHERREACTOR, 
			new NetherReactor(new BID(Ids::NETHER_REACTOR_CORE), "Nether Reactor Core", new Info(BreakInfo::pickaxe(3.0, ToolTier::WOOD(), 6))), 
			[Names::NETHERREACTOR, "netherreactor"]
		);
		self::registerBlock(
			Names::END_PORTAL, 
			new EndPortal(new BID(Ids::newId()), "End Portal", new Info(BreakInfo::indestructible())), 
			[Names::END_PORTAL, "end_portal"]
		);
		self::registerBlock(
			Names::END_GATEWAY, 
			new EndGateway(new BID(Ids::newId()), "End Gateway", new Info(BreakInfo::indestructible())), 
			[Names::END_GATEWAY, "end_gateway"]
		);
		self::registerBlock(
			Names::OBSIDIAN,
			new Opaque(new BID(Ids::OBSIDIAN), "Obsidian", new Info(BreakInfo::pickaxe(5.5, ToolTier::DIAMOND(), 50.0))),
			[Names::OBSIDIAN, "obsidian"]
		);
		self::registerCustomBlock(
			CustomNames::BLOOD_INFUSED_OBSIDIAN,
			new Opaque(new BID(Ids::newId()), "Blood Infused Obsidian", new Info(BreakInfo::pickaxe(5.5, ToolTier::DIAMOND(), 50.0))), 
			new Model([
				new Material(Material::TARGET_ALL, "blood_infused_obsidian", Material::RENDER_METHOD_OPAQUE)
			]), 
			["blood_infused_obsidian"]
		);
		self::registerCustomBlock(
			CustomNames::POLISHED_BLOOD_INFUSED_OBSIDIAN,
			new Opaque(new BID(Ids::newId()), "Polished Blood Infused Obsidian", new Info(BreakInfo::pickaxe(5.5, ToolTier::DIAMOND(), 50.0))), 
			new Model([
				new Material(Material::TARGET_ALL, "polished_blood_infused_obsidian", Material::RENDER_METHOD_OPAQUE)
			]), 
			["polished_blood_infused_obsidian"]
		);
		self::registerCustomBlock(
			CustomNames::GILDED_OBSIDIAN,
			new Opaque(new BID(Ids::newId()), "Gilded Obsidian", new Info(BreakInfo::pickaxe(5.5, ToolTier::DIAMOND(), 50.0))), 
			new Model([
				new Material(Material::TARGET_ALL, "gilded_obsidian", Material::RENDER_METHOD_OPAQUE)
			]), 
			["gilded_obsidian"]
		);
		self::registerCustomBlock(
			CustomNames::POLISHED_GILDED_OBSIDIAN,
			new Opaque(new BID(Ids::newId()), "Polished Gilded Obsidian", new Info(BreakInfo::pickaxe(5.5, ToolTier::DIAMOND(), 50.0))), 
			new Model([
				new Material(Material::TARGET_ALL, "polished_gilded_obsidian", Material::RENDER_METHOD_OPAQUE)
			]), 
			["polished_gilded_obsidian"]
		);
		self::registerCustomBlock(
			CustomNames::POLISHED_OBSIDIAN,
			new Opaque(new BID(Ids::newId()), "Polished Obsidian", new Info(BreakInfo::pickaxe(5.5, ToolTier::DIAMOND(), 50.0))), 
			new Model([
				new Material(Material::TARGET_ALL, "polished_obsidian", Material::RENDER_METHOD_OPAQUE)
			]), 
			["polished_obsidian"]
		);
		self::registerCustomBlock(
			CustomNames::POLISHED_GLOWING_OBSIDIAN,
			new Opaque(new BID(Ids::newId()), "Polished Glowing Obsidian", new Info(BreakInfo::pickaxe(5.5, ToolTier::DIAMOND(), 50.0))), 
			new Model([
				new Material(Material::TARGET_ALL, "polished_glowing_obsidian", Material::RENDER_METHOD_OPAQUE)
			]), 
			["polished_glowing_obsidian"]
		);
		self::registerCustomBlock(
			CustomNames::PERIDOT,
			new Opaque(new BID(Ids::newId()), "Peridot", new Info(BreakInfo::pickaxe(3.5, ToolTier::IRON(), 25.0))),
			new Model([
				new Material(Material::TARGET_ALL, "peridot_", Material::RENDER_METHOD_OPAQUE)
			]),
			["peridot"]
		);
		self::registerCustomBlock(
			CustomNames::TOPAZ,
			new Opaque(new BID(Ids::newId()), "Topaz", new Info(BreakInfo::pickaxe(3.5, ToolTier::IRON(), 25.0))),
			new Model([
				new Material(Material::TARGET_ALL, "topaz_ore", Material::RENDER_METHOD_OPAQUE)
			]),
			["topaz"]
		);

		// Overwritten Custom Blocks
		$customLastId = Ids::newId();

		if($serverType !== "skyblock"){
			self::registerCustomBlock(
				CustomNames::PET_BOX, 
				new Block(new BID($customLastId), "Pet Box", new Info(new BreakInfo(3.5))), 
				new Model([
					new Material(Material::TARGET_UP, "pet_box_top", Material::RENDER_METHOD_OPAQUE),
					new Material(Material::TARGET_DOWN, "pet_box_bottom", Material::RENDER_METHOD_OPAQUE),
					new Material(Material::TARGET_EAST, "pet_box_side", Material::RENDER_METHOD_OPAQUE),
					new Material(Material::TARGET_WEST, "pet_box_side", Material::RENDER_METHOD_OPAQUE),
					new Material(Material::TARGET_NORTH, "pet_box_side", Material::RENDER_METHOD_OPAQUE),
					new Material(Material::TARGET_SOUTH, "pet_box_front", Material::RENDER_METHOD_OPAQUE)
				]), 
				["pet_box"]
			);
		}

		switch ($serverType) {
			case "lobby":
				self::registerBlock(
					Names::HEAVY_WEIGHTED_PRESSURE_PLATE,
					new LobbyIPressurePlate(new BID(Ids::WEIGHTED_PRESSURE_PLATE_HEAVY), 'Iron Pressure Plate', new Info(BreakInfo::pickaxe(0.5, ToolTier::WOOD(), 0.5)), 20),
					[Names::HEAVY_WEIGHTED_PRESSURE_PLATE, 'iron_pressure_plate']
				);

				self::registerBlock(
					Names::LIGHT_WEIGHTED_PRESSURE_PLATE,
					new LobbyGPressurePlate(new BID(Ids::WEIGHTED_PRESSURE_PLATE_LIGHT), "Gold Pressure Plate", new Info(BreakInfo::pickaxe(0.5, ToolTier::WOOD(), 0.5)), 20),
					[Names::LIGHT_WEIGHTED_PRESSURE_PLATE, 'gold_pressure_plate']
				);
				break;

			case "prison":
				break;

			case "skyblock":
				self::registerBlock(
					Names::HOPPER,
					new Hopper(new BID(Ids::HOPPER, HopperTile::class), "Hopper", new Info(BreakInfo::pickaxe(3, ToolTier::WOOD(), 15))),
					[Names::HOPPER, 'hopper']
				);
				self::registerBlock(
					Names::MOB_SPAWNER, 
					new MobSpawner(new BID(Ids::MONSTER_SPAWNER, Spawner::class), "Mob Spawner", new Info(BreakInfo::pickaxe(5, ToolTier::WOOD()))), 
					['mob_spawner'], 
					false
				);
				self::registerBlock(
					Names::ELEMENT_95, 
					new AutoMiner(new BID(Ids::ELEMENT_AMERICIUM, AutoMinerTile::class), "AutoMiner", new Info(BreakInfo::pickaxe(2, ToolTier::WOOD())), "Am", 118, 0), 
					['autominer']
				);
				self::registerBlock(
					Names::ELEMENT_105, 
					new DimensionalBlock(new BID(Ids::ELEMENT_DUBNIUM, DimensionalTile::class), "Dimensional Block", new Info(BreakInfo::pickaxe(2, ToolTier::WOOD())), "Dm", 105, 0), 
					['dimensional_block']
				);
				self::registerBlock(
					Names::ELEMENT_118, 
					new OreGenerator(new BID(Ids::ELEMENT_OGANESSON, OreGeneratorTile::class), "Ore Generator", new Info(BreakInfo::pickaxe(2, ToolTier::WOOD())), "Og", 118, 0), 
					['ore_generator']
				);
				self::registerBlock(
					Names::HEAVY_WEIGHTED_PRESSURE_PLATE,
					new SkyblockIPressurePlate(new BID(Ids::WEIGHTED_PRESSURE_PLATE_HEAVY), 'Iron Pressure Plate', new Info(BreakInfo::pickaxe(0.5, ToolTier::WOOD(), 0.5)), 20),
					[Names::HEAVY_WEIGHTED_PRESSURE_PLATE, 'iron_pressure_plate'],
				);

				self::registerBlock(
					Names::LIGHT_WEIGHTED_PRESSURE_PLATE,
					new SkyblockGPressurePlate(new BID(Ids::WEIGHTED_PRESSURE_PLATE_LIGHT), "Gold Pressure Plate", new Info(BreakInfo::pickaxe(0.5, ToolTier::WOOD(), 0.5)), 20),
					[Names::LIGHT_WEIGHTED_PRESSURE_PLATE, 'gold_pressure_plate'],
				);
				self::registerBlock(
					Names::STONE_PRESSURE_PLATE,
					new SkyblockSPressurePlate(new BID(Ids::STONE_PRESSURE_PLATE), "Stone Pressure Plate", new Info(BreakInfo::pickaxe(0.5, ToolTier::WOOD())), 20),
					[Names::STONE_PRESSURE_PLATE, 'stone_pressure_plate'],
				);
				self::registerBlock(
					Names::RED_MUSHROOM, 
					new RedMushroom(new BID(Ids::RED_MUSHROOM), "Red Mushroom", new Info(BreakInfo::instant(), [Tags::POTTABLE_PLANTS])),
					["red_mushroom"]
				);
				self::registerBlock(
					Names::RED_MUSHROOM_BLOCK, 
					new RedMushroomBlock(new BID(Ids::RED_MUSHROOM_BLOCK), "Red Mushroom Block", new Info(BreakInfo::axe(0.2))),
					["red_mushroom_block"]
				);
				self::registerBlock(
					Names::BROWN_MUSHROOM, 
					new BrownMushroom(new BID(Ids::BROWN_MUSHROOM), "Brown Mushroom", new Info(BreakInfo::instant(), [Tags::POTTABLE_PLANTS])),
					["brown_mushroom"]
				);
				self::registerBlock(
					Names::BROWN_MUSHROOM_BLOCK, 
					new BrownMushroomBlock(new BID(Ids::BROWN_MUSHROOM_BLOCK), "Brown Mushroom Block", new Info(BreakInfo::axe(0.2))),
					["brown_mushroom_block"]
				);
				self::registerCustomBlock(
					CustomNames::PET_BOX, 
					new PetBox(new BID($customLastId), "Pet Box", new Info(new BreakInfo(3.5))), 
					new Model([
						new Material(Material::TARGET_UP, "pet_box_top", Material::RENDER_METHOD_OPAQUE),
						new Material(Material::TARGET_DOWN, "pet_box_bottom", Material::RENDER_METHOD_OPAQUE),
						new Material(Material::TARGET_EAST, "pet_box_side", Material::RENDER_METHOD_OPAQUE),
						new Material(Material::TARGET_WEST, "pet_box_side", Material::RENDER_METHOD_OPAQUE),
						new Material(Material::TARGET_NORTH, "pet_box_side", Material::RENDER_METHOD_OPAQUE),
						new Material(Material::TARGET_SOUTH, "pet_box_front", Material::RENDER_METHOD_OPAQUE)
					]), 
					["pet_box"]
				);
				break;

			case "pvp":
				break;

			case "faction":
				self::registerBlock(
					Names::BEDROCK,
					new Bedrock(new BID(IDs::BEDROCK, BreakableBlockTile::class), "Bedrock", new Info(new BreakInfo(-1.0, ToolType::NONE, 0, 1.0))),
					["bedrock"]
				);
				self::registerBlock(
					Names::OBSIDIAN,
					new Obsidian(new BID(IDs::OBSIDIAN, BreakableBlockTile::class), "Obsidian", new Info(BreakInfo::pickaxe(35.0, ToolTier::DIAMOND(), 1.0))),
					["obsidian"]
				);
				break;
		}

		BlockDeserializerMap::setup($serverType);
		BlockSerializerMap::setup($serverType);
	}

	public static function registerBlock(string $identifier, Block $block, array $stringToItemParserNames = [], bool $isCustom = false): void {
		foreach($stringToItemParserNames as $n) self::_registryRegister($n, $block);

		self::registerRuntimeBlockStateRegistry($block);

		($isCustom ? ItemRegistry::registerCustomItemBlock($identifier, $block, $stringToItemParserNames) : ItemRegistry::registerItemBlock($identifier, $block, $stringToItemParserNames));
	}

	/**
	 * Custom Block support but DOES NOT support custom blocks with custom models yet.
	 * 
	 * will add support for custom models in the future.
	 *
	 * @param string $identifier
	 * @param Block $block
	 * @param array $stringToItemParserNames
	 */
	public static function registerCustomBlock(string $identifier, Block $block, Model $model, array $stringToItemParserNames = []) : void{
		self::registerBlock($identifier, $block, $stringToItemParserNames, true);

		$propertiesTag = CompoundTag::create();
		$components = CompoundTag::create()
			->setTag("minecraft:light_emission", CompoundTag::create()
				->setByte("emission", $block->getLightLevel()))
			->setTag("minecraft:light_dampening", CompoundTag::create()
				->setByte("lightLevel", $block->getLightFilter()))
			->setTag("minecraft:destructible_by_mining", CompoundTag::create()
				->setFloat("value", $block->getBreakInfo()->getHardness() / 1.5))
			->setTag("minecraft:friction", CompoundTag::create()
				->setFloat("value", 1 - $block->getFrictionFactor()));
		
		foreach($model->toNBT() as $tagName => $tag){
			$components->setTag($tagName, $tag);
		}

		$blockState = CompoundTag::create()
		->setString(BlockStateData::TAG_NAME, $identifier)
		->setTag(BlockStateData::TAG_STATES, CompoundTag::create());
		BlockPalette::getInstance()->insertState($blockState);

		$creativeInfo ??= CreativeInventoryInfo::DEFAULT();
		$components->setTag("minecraft:creative_category", CompoundTag::create()
			->setString("category", $creativeInfo->getCategory())
			->setString("group", $creativeInfo->getGroup()));
		$propertiesTag
			->setTag("components",
				$components->setTag("minecraft:creative_category", CompoundTag::create()
					->setString("category", $creativeInfo->getCategory())
					->setString("group", $creativeInfo->getGroup())))
			->setTag("menu_category", CompoundTag::create()
				->setString("category", $creativeInfo->getCategory() ?? "")
				->setString("group", $creativeInfo->getGroup() ?? ""))
			->setInt("molangVersion", 1);

		self::$blockPaletteEntries[] = new BlockPaletteEntry($identifier, new CacheableNbt($propertiesTag));

		// 1.20.60 added a new "block_id" field which depends on the order of the block palette entries. Every time we
		// insert a new block, we need to re-sort the block palette entries to keep in sync with the client.
		usort(self::$blockPaletteEntries, static function(BlockPaletteEntry $a, BlockPaletteEntry $b): int {
			return strcmp(hash("fnv164", $a->getName()), hash("fnv164", $b->getName()));
		});
		foreach(self::$blockPaletteEntries as $i => $entry){
			/** @var CompoundTag $rootTag */
			$rootTag = $entry->getStates()->getRoot();
			$root = $rootTag->setTag("vanilla_block_data", CompoundTag::create()->setInt("block_id", 10000 + $i));
			self::$blockPaletteEntries[$i] = new BlockPaletteEntry($entry->getName(), new CacheableNbt($root));
		}
	}

	private static function registerRuntimeBlockStateRegistry(Block $block): void {
		$instance = RuntimeBlockStateRegistry::getInstance();

		try {
			$instance->register($block);
		} catch (\InvalidArgumentException) {
			$typeIndexProperty = new \ReflectionProperty($instance, "typeIndex");
			$typeIndexProperty->setAccessible(true);
			$value = $typeIndexProperty->getValue($instance);
			$value[$block->getTypeId()] = clone $block;
			$typeIndexProperty->setValue($instance, $value);

			$fillStaticArraysMethod = new \ReflectionMethod($instance, "fillStaticArrays");
			$fillStaticArraysMethod->setAccessible(true);

			foreach ([...$block->generateStatePermutations()] as $v) {
				/** @var Block $v */
				$fillStaticArraysMethod->invoke($instance, $v->getStateId(), $v);
			}
		}
	}

	public static function getBlockById(int $id, int $meta = -1): ?Block {
		if ($id < 0) return null;
		foreach (array_merge(VanillaBlocks::getAll(), self::_registryGetAll()) as $sid => $block) {
			try {
				$m = TypeConverter::getInstance()->getBlockTranslator()->getBlockStateDictionary()->getMetaFromStateId(TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId($block->getStateId()));
			} catch (AssumptionFailedError) {
				$m = 0;
			}
			if (($block->getTypeId() == $id) && ($meta < 0 || $m == $meta)) {
				return $block;
			}
		}
		return null;
	}

	private static function typeMap() : void{
		self::$typeMap = [
			'stained_clay' => function (int $colorId): StainedHardenedClay {
				return VanillaBlocks::STAINED_CLAY()->setColor(DyeColorIdMap::getInstance()->fromId($colorId));
			},
			'stained_glass' => function (int $colorId): StainedGlass {
				return VanillaBlocks::STAINED_GLASS()->setColor(DyeColorIdMap::getInstance()->fromId($colorId));
			},
			'skull' => function (int $headType): MobHead {
				return VanillaBlocks::MOB_HEAD()->setMobHeadType(MobHeadTypeIdMap::getInstance()->fromId($headType));
			},
			'wool' => function (int $colorId): Wool {
				return VanillaBlocks::WOOL()->setColor(DyeColorIdMap::getInstance()->fromId($colorId));
			},
			'concrete_powder' => function (int $colorId): ConcretePowder {
				return VanillaBlocks::CONCRETE_POWDER()->setColor(DyeColorIdMap::getInstance()->fromId($colorId));
			},
			'concrete' => function (int $colorId): Concrete {
				return VanillaBlocks::CONCRETE()->setColor(DyeColorIdMap::getInstance()->fromId($colorId));
			},
			'element' => function (int $meta, int $elementNum): Element {
				$elements = [
					118 => BlockRegistry::ORE_GENERATOR(),
					95 => BlockRegistry::AUTOMINER(),
					105 => BlockRegistry::DIMENSIONAL_BLOCK()
				];
				if (isset($elements[$elementNum])) return $elements[$elementNum];
				foreach (array_filter(array_merge(VanillaBlocks::getAll(), self::getAll()), function (Block $b): bool {
					return $b instanceof Element;
				}) as $_ => $block) {
					/** @var Element $block */
					if ($block->getAtomicWeight() == $elementNum) return $block;
				}
			},
			'sapling' => function (int $meta): Sapling {
				foreach (array_filter(array_merge(VanillaBlocks::getAll(), self::getAll()), function (Block $b): bool {
					return $b instanceof Sapling;
				}) as $_ => $block) {
					/** @var Sapling $block */
					$m = LegacyBlockIds::stateIdToMeta($block->getStateId());
					if ($m == $meta) return $block;
				}
				return VanillaBlocks::OAK_SAPLING();
			},
			'red_flower' => function (int $meta): Flower {
				foreach (array_filter(array_merge(VanillaBlocks::getAll(), self::getAll()), function (Block $b): bool {
					return $b instanceof Flower;
				}) as $_ => $block) {
					/** @var Flower $block */
					$m = LegacyBlockIds::stateIdToMeta($block->getStateId());
					if ($m == $meta) {
						// var_dump("$m == $meta");
						return $block;
					}
				}
				var_dump("No flower found for $meta");
				return VanillaBlocks::RED_TULIP();
			}
		];
	}

	public static function getBlock(string $id, int $meta = -1): ?Block {
		$id = str_replace('[block]', '', $id);
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
		// var_dump(self::class . "::getBlock ID => $id");
		foreach (array_merge(VanillaBlocks::getAll(), self::_registryGetAll()) as $sid => $block) {
			try {
				$m = TypeConverter::getInstance()->getBlockTranslator()->getBlockStateDictionary()->getMetaFromStateId(TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId($block->getStateId()));
				$d = TypeConverter::getInstance()->getBlockTranslator()->getBlockStateDictionary()->generateDataFromStateId(TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId($block->getStateId()));
			} catch (AssumptionFailedError) {
				$m = 0;
				$d = new StateData($id, [], 0);
			}

			if (("minecraft:" . strtolower($sid) == strtolower($id) || strtolower($d->getName()) == strtolower($id) || strtolower($sid) == strtolower($id)) && ($meta < 0 || $m == $meta)) {
				$b = (self::_registryGet(strtolower($id)) ?? $block);
				// var_dump(self::class . "::getBlock BLOCK CLASS => " . $b::class . " <-> " . $sid);
				return clone (self::_registryGet(strtolower($id)) ?? $block);
			}
		}
		return null;
	}

	private static function _registryRegister(string $identifier, Block $block) : void{
		$identifier = strtoupper($identifier);
		
		self::$_registry[$identifier] = $block;
	}

	private static function _registryGetAll(): array {
		return self::$_registry;
	}

	private static function _registryGet(string $identifier) : ?Block {
		$identifier = strtoupper($identifier);

		return self::$_registry[$identifier] ?? null;
	}

	/** @return Block[] */
	public static function getAll() : array { return self::_registryGetAll(); }

	public static function __callStatic($name, $arguments){
		$b = self::_registryGet($name);

		if (!is_null($b)) return clone $b;

		throw new Error("Block \"" . $name . "\" does not exist within the BlockRegistry");
	}
}
