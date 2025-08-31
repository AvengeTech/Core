<?php

namespace core\utils\item;

use Closure;
use core\utils\ItemRegistry;
use pocketmine\block\Block;
use pocketmine\data\bedrock\block\BlockTypeNames;
use pocketmine\data\bedrock\item\ItemTypeNames as Names;
use pocketmine\data\bedrock\item\SavedItemData as Data;
use pocketmine\item\Item;
use core\utils\ItemTypeNames as CustomNames;

class ItemSerializerDeserializerMap{

	public static function setup(string $serverType) : void{
		self::map1to1Item(Names::ELYTRA, ItemRegistry::ELYTRA());
		self::map1to1Item(Names::FIREWORK_ROCKET, ItemRegistry::FIREWORKS());
		self::map1to1Item(Names::ENCHANTED_GOLDEN_APPLE, ItemRegistry::ENCHANTED_GOLDEN_APPLE());
		self::map1to1Item(CustomNames::DOWN_ARROW, ItemRegistry::DOWN_ARROW());
		self::map1to1Item(CustomNames::UP_ARROW, ItemRegistry::UP_ARROW());
		self::map1to1Item(CustomNames::DIAGONAL_LINE_LEFT, ItemRegistry::DIAGONAL_LINE_LEFT());
		self::map1to1Item(CustomNames::DIAGONAL_LINE_RIGHT, ItemRegistry::DIAGONAL_LINE_RIGHT());
		self::map1to1Item(CustomNames::VERTICAL_LINE, ItemRegistry::VERTICAL_LINE());
		self::map1to1Item(CustomNames::DIAGONAL_LINE_LEFT_ACTIVE, ItemRegistry::DIAGONAL_LINE_LEFT_ACTIVE());
		self::map1to1Item(CustomNames::DIAGONAL_LINE_RIGHT_ACTIVE, ItemRegistry::DIAGONAL_LINE_RIGHT_ACTIVE());
		self::map1to1Item(CustomNames::VERTICAL_LINE_ACTIVE, ItemRegistry::VERTICAL_LINE_ACTIVE());

		switch($serverType){
			case "lobby":
				break;

			case "prison":
				self::map1to1Item(Names::EMPTY_MAP, ItemRegistry::TECHIT_NOTE());
				self::map1to1Item(Names::GHAST_TEAR, ItemRegistry::SALE_BOOSTER());
				self::map1to1Item(Names::PAPER, ItemRegistry::KEY_NOTE());
				self::map1to1Item(CustomNames::UNBOUND_TOME, ItemRegistry::UNBOUND_TOME());
				self::map1to1Item(Names::NETHER_STAR, ItemRegistry::ANIMATOR());
				self::map1to1Item(Names::BOOK, ItemRegistry::REDEEMABLE_BOOK());
				self::map1to1Item(Names::NAME_TAG, ItemRegistry::NAMETAG());
				self::map1to1Item(Names::SHULKER_SHELL, ItemRegistry::DEATH_TAG());
				self::map1to1Item(Names::EXPERIENCE_BOTTLE, ItemRegistry::EXPERIENCE_BOTTLE());
				self::map1to1Item(Names::MAGMA_CREAM, ItemRegistry::MINE_NUKE());
				self::map1to1Item(Names::FIREWORK_STAR, ItemRegistry::HASTE_BOMB());
				self::map1to1Item(Names::ARMOR_STAND, ItemRegistry::ARMOR_STAND());
				self::map1to1Item(Names::ENCHANTED_BOOK, ItemRegistry::REDEEMED_BOOK());
				self::map1to1Item(Names::FISHING_ROD, ItemRegistry::FISHING_ROD());
				self::map1to1Item(CustomNames::ESSENCE_OF_SUCCESS, ItemRegistry::ESSENCE_OF_SUCCESS());
				self::map1to1Item(CustomNames::ESSENCE_OF_KNOWLEDGE, ItemRegistry::ESSENCE_OF_KNOWLEDGE());
				self::map1to1Item(CustomNames::POUCH_OF_ESSENCE, ItemRegistry::POUCH_OF_ESSENCE());
				self::map1to1Item(CustomNames::ESSENCE_OF_PROGRESS, ItemRegistry::ESSENCE_OF_PROGRESS());
				self::map1to1Item(CustomNames::ESSENCE_OF_ASCENSION, ItemRegistry::ESSENCE_OF_ASCENSION());
				break;

			case "skyblock":
				// vanilla
				self::map1to1Item(CustomNames::BREEZE_ROD, ItemRegistry::BREEZE_ROD());
				self::map1to1Item(Names::WIND_CHARGE, ItemRegistry::WIND_CHARGE());

				self::map1to1Item(Names::NETHER_STAR, ItemRegistry::ANIMATOR());
				self::map1to1Item(Names::EXPERIENCE_BOTTLE, ItemRegistry::EXPERIENCE_BOTTLE());
				self::map1to1Item(Names::ENDER_EYE, ItemRegistry::ENDER_EYE());
				self::map1to1Item(Names::ARMOR_STAND, ItemRegistry::ARMOR_STAND());
				self::map1to1Item(CustomNames::SELL_WAND, ItemRegistry::SELL_WAND());
				self::map1to1Item(Names::EMPTY_MAP, ItemRegistry::TECHIT_NOTE());
				self::map1to1Item(Names::TURTLE_SCUTE, ItemRegistry::GEN_BOOSTER());
				self::map1to1Item(Names::PAPER, ItemRegistry::KEY_NOTE());
				self::map1to1Item(CustomNames::UNBOUND_TOME, ItemRegistry::UNBOUND_TOME());
				self::map1to1Item(Names::ENCHANTED_BOOK, ItemRegistry::REDEEMED_BOOK());
				self::map1to1Item(Names::SHULKER_SHELL, ItemRegistry::DEATH_TAG());
				self::map1to1Item(Names::NAME_TAG, ItemRegistry::NAMETAG());
				self::map1to1Item(Names::BOOK, ItemRegistry::MAX_BOOK());
				self::map1to1Item(Names::FISHING_ROD, ItemRegistry::FISHING_ROD());
				self::map1to1Item(Names::ENDER_PEARL, ItemRegistry::ENDER_PEARL());
				self::map1to1Item(Names::FIREWORK_ROCKET, ItemRegistry::FIREWORK_ROCKET());
				self::map1to1Item(CustomNames::ESSENCE_OF_SUCCESS, ItemRegistry::ESSENCE_OF_SUCCESS());
				self::map1to1Item(CustomNames::ESSENCE_OF_KNOWLEDGE, ItemRegistry::ESSENCE_OF_KNOWLEDGE());
				self::map1to1Item(CustomNames::POUCH_OF_ESSENCE, ItemRegistry::POUCH_OF_ESSENCE());
				self::map1to1Item(CustomNames::ESSENCE_OF_ASCENSION, ItemRegistry::ESSENCE_OF_ASCENSION());
				self::map1to1Item(CustomNames::VERTICAL_EXTENDER, ItemRegistry::VERTICAL_EXTENDER());
				self::map1to1Item(CustomNames::HORIZONTAL_EXTENDER, ItemRegistry::HORIZONTAL_EXTENDER());
				self::map1to1Item(CustomNames::SOLIDIFIER, ItemRegistry::SOLIDIFIER());
				self::map1to1Item(CustomNames::WHITE_MUSHROOM, ItemRegistry::WHITE_MUSHROOM());
				self::map1to1Item(CustomNames::WITHERED_BONE, ItemRegistry::WITHERED_BONE());
				self::map1to1Item(CustomNames::PET_ENERGY_BOOSTER, ItemRegistry::ENERGY_BOOSTER());
				self::map1to1Item(CustomNames::PET_GUMMY_ORB, ItemRegistry::GUMMY_ORB());
				self::map1to1Item(CustomNames::PET_KEY, ItemRegistry::PET_KEY());
				self::map1to1Item(Names::NPC_SPAWN_EGG, ItemRegistry::PET_EGG());
				self::map1to1Item(CustomNames::KOTH_POUCH, ItemRegistry::KOTH_POUCH());
				self::map1to1Item(CustomNames::KIT_POUCH, ItemRegistry::KIT_POUCH());
				self::map1to1Item(CustomNames::JEWEL_OF_THE_END, ItemRegistry::JEWEL_OF_THE_END());
				break;

			case "pvp":
				self::map1to1Item(Names::SPLASH_POTION, ItemRegistry::HEALTH_POT());
				self::map1to1Item("fling_ball", ItemRegistry::FLING_BALL());
				self::map1to1Item("techit_note", ItemRegistry::TECHIT_NOTE());
				break;

			case "faction":
				self::map1to1Item(CustomNames::UNBOUND_TOME, ItemRegistry::UNBOUND_TOME());
				break;
		}
	}

	public static function map1to1Item(string $identifier, Item $item) : void{
		ItemDeserializer::map($identifier, fn() => clone $item);
		ItemSerializer::map($item, fn() => new Data($identifier));
	}

	public static function map1to1ItemWithMeta(string $identifier, Item $item, Closure $deserializeMeta, Closure $serializeMeta) : void{
		ItemDeserializer::map($identifier, function(Data $data) use($item, $deserializeMeta) : Item{
			$result = clone $item;
			$deserializeMeta($result, $data->getMeta());
			return $result;
		});
		ItemSerializer::map($item, function(Item $item) use($identifier, $serializeMeta) : Data{
			/** @phpstan-var TItem $item */
			$meta = $serializeMeta($item);
			return new Data($identifier, $meta);
		});
	}

	public static function map1to1Block(string $identifier, Block $block) : void{
		ItemDeserializer::mapBlock($identifier, fn() => clone $block);
		ItemSerializer::mapBlock($block, fn() => new Data($identifier));
	}
}