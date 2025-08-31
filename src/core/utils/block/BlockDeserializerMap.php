<?php

namespace core\utils\block;

use core\utils\BlockRegistry as Registry;
use lobby\block\GoldPressurePlate as LobbyGPressurePlate;
use lobby\block\IronPressurePlate as LobbyIPressurePlate;
use pocketmine\block\Block;
use pocketmine\data\bedrock\block\BlockStateNames as State;
use pocketmine\data\bedrock\block\BlockTypeNames as Ids;
use pocketmine\data\bedrock\block\convert\BlockStateDeserializerHelper as Helper;
use pocketmine\data\bedrock\block\convert\BlockStateReader as Reader;
use skyblock\block\GoldPressurePlate as SkyblockGPressurePlate;
use skyblock\parkour\block\IronPressurePlate as SkyblockIPressurePlate;
use skyblock\islands\warp\block\StonePressurePlate as SkyblockSPressurePlate;
use core\utils\BlockTypeNames as CustomIds;
use skyblock\block\BrownMushroomBlock;
use skyblock\block\RedMushroomBlock;

class BlockDeserializerMap{

	public static function setup(string $serverType) : void{
		BlockDeserializer::map(Ids::CHEST, function(Reader $in) : Block{
			return Registry::CHEST()
				->setFacing($in->readCardinalHorizontalFacing());
		});
		BlockDeserializer::map(Ids::ENDER_CHEST, function(Reader $in) : Block{
			return Registry::ENDER_CHEST()
				->setFacing($in->readCardinalHorizontalFacing());
		});
		BlockDeserializer::mapSimple(Ids::CONDUIT, Registry::CONDUIT());
		BlockDeserializer::mapSimple(Ids::NETHERREACTOR, Registry::NETHERREACTOR());
		BlockDeserializer::mapSimple(Ids::END_PORTAL, Registry::END_PORTAL());
		BlockDeserializer::mapSimple(Ids::END_GATEWAY, Registry::END_GATEWAY());
		BlockDeserializer::mapSimple(Ids::OBSIDIAN, Registry::OBSIDIAN());
		BlockDeserializer::mapSimple(CustomIds::BLOOD_INFUSED_OBSIDIAN, Registry::BLOOD_INFUSED_OBSIDIAN());
		BlockDeserializer::mapSimple(CustomIds::POLISHED_BLOOD_INFUSED_OBSIDIAN, Registry::POLISHED_BLOOD_INFUSED_OBSIDIAN());
		BlockDeserializer::mapSimple(CustomIds::GILDED_OBSIDIAN, Registry::GILDED_OBSIDIAN());
		BlockDeserializer::mapSimple(CustomIds::POLISHED_GILDED_OBSIDIAN, Registry::POLISHED_GILDED_OBSIDIAN());
		BlockDeserializer::mapSimple(CustomIds::POLISHED_OBSIDIAN, Registry::POLISHED_OBSIDIAN());
		BlockDeserializer::mapSimple(CustomIds::POLISHED_GLOWING_OBSIDIAN, Registry::POLISHED_GLOWING_OBSIDIAN());
		BlockDeserializer::mapSimple(CustomIds::PERIDOT, Registry::PERIDOT());
		BlockDeserializer::mapSimple(CustomIds::TOPAZ, Registry::TOPAZ());
		BlockDeserializer::mapSimple(CustomIds::PET_BOX, Registry::PET_BOX());

		switch($serverType){
			case "lobby":
				BlockDeserializer::map(Ids::HEAVY_WEIGHTED_PRESSURE_PLATE, function(Reader $in) : LobbyIPressurePlate{
					return Helper::decodeWeightedPressurePlate(Registry::IRON_PRESSURE_PLATE(), $in);
				});
				BlockDeserializer::map(Ids::LIGHT_WEIGHTED_PRESSURE_PLATE, function(Reader $in) : LobbyGPressurePlate{
					return Helper::decodeWeightedPressurePlate(Registry::GOLD_PRESSURE_PLATE(), $in);
				});
				break;
			
			case "prison":
				break;

			case "skyblock":
				BlockDeserializer::map(Ids::HOPPER, function(Reader $in) : Block{
					return Registry::HOPPER()
						->setPowered($in->readBool(State::TOGGLE_BIT))
						->setFacing($in->readFacingWithoutUp());
				});
				BlockDeserializer::mapSimple(Ids::MOB_SPAWNER, Registry::MOB_SPAWNER());
				BlockDeserializer::mapSimple(Ids::ELEMENT_95, Registry::AUTOMINER());
				BlockDeserializer::mapSimple(Ids::ELEMENT_105, Registry::DIMENSIONAL_BLOCK());
				BlockDeserializer::mapSimple(Ids::ELEMENT_118, Registry::ORE_GENERATOR());
				BlockDeserializer::map(Ids::HEAVY_WEIGHTED_PRESSURE_PLATE, function(Reader $in) : SkyblockIPressurePlate {
					return Helper::decodeWeightedPressurePlate(Registry::IRON_PRESSURE_PLATE(), $in);
				});
				BlockDeserializer::map(Ids::LIGHT_WEIGHTED_PRESSURE_PLATE, function(Reader $in) : SkyblockGPressurePlate {
					return Helper::decodeWeightedPressurePlate(Registry::GOLD_PRESSURE_PLATE(), $in);
				});
				BlockDeserializer::map(Ids::STONE_PRESSURE_PLATE, function(Reader $in) : SkyblockSPressurePlate {
					return Helper::decodeSimplePressurePlate(Registry::STONE_PRESSURE_PLATE(), $in);
				});
				BlockDeserializer::mapSimple(Ids::RED_MUSHROOM, Registry::RED_MUSHROOM());
				BlockDeserializer::map(Ids::RED_MUSHROOM_BLOCK, function(Reader $in) : RedMushroomBlock{
					return Helper::decodeMushroomBlock(Registry::RED_MUSHROOM_BLOCK(), $in);
				});
				BlockDeserializer::mapSimple(Ids::BROWN_MUSHROOM, Registry::BROWN_MUSHROOM());
				BlockDeserializer::map(Ids::BROWN_MUSHROOM_BLOCK, function(Reader $in) : BrownMushroomBlock{
					return Helper::decodeMushroomBlock(Registry::BROWN_MUSHROOM_BLOCK(), $in);
				});
				break;

			case "pvp":
				break;

			case "faction":
				BlockDeserializer::map(Ids::BEDROCK, function(Reader $in) : Block{
					return Registry::BEDROCK()
						->setBurnsForever($in->readBool(State::INFINIBURN_BIT));
				});
				BlockDeserializer::mapSimple(Ids::OBSIDIAN, Registry::OBSIDIAN());
				break;
		}
	}
}