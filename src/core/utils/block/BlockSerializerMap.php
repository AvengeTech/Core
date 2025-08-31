<?php

namespace core\utils\block;

use core\block\Chest;
use core\block\EnderChest;
use core\utils\BlockRegistry as Registry;
use lobby\block\GoldPressurePlate as LobbyGPressurePlate;
use lobby\block\IronPressurePlate as LobbyIPressurePlate;
use pocketmine\block\Hopper;
use pocketmine\data\bedrock\block\BlockStateNames as State;
use pocketmine\data\bedrock\block\BlockTypeNames as Ids;
use pocketmine\data\bedrock\block\convert\BlockStateSerializerHelper as Helper;
use pocketmine\data\bedrock\block\convert\BlockStateWriter as Writer;
use skyblock\block\GoldPressurePlate as SkyblockGPressurePlate;
use skyblock\parkour\block\IronPressurePlate as SkyblockIPressurePlate;
use skyblock\islands\warp\block\StonePressurePlate as SkyblockSPressurePlate;
use core\utils\BlockTypeNames as CustomIds;
use pocketmine\block\Block;
use skyblock\block\BrownMushroomBlock;
use skyblock\block\RedMushroomBlock;

class BlockSerializerMap{

	public static function setup(string $serverType) : void{
		BlockSerializer::map(Registry::CHEST(), function(Chest $block) : Writer {
			return Writer::create(Ids::CHEST)
				->writeCardinalHorizontalFacing($block->getFacing());
		});
		BlockSerializer::map(Registry::ENDER_CHEST(), function(EnderChest $block) : Writer {
			return Writer::create(Ids::ENDER_CHEST)
				->writeCardinalHorizontalFacing($block->getFacing());
		});
		BlockSerializer::mapSimple(Registry::CONDUIT(), Ids::CONDUIT);
		BlockSerializer::mapSimple(Registry::NETHERREACTOR(), Ids::NETHERREACTOR);
		BlockSerializer::mapSimple(Registry::END_PORTAL(), Ids::END_PORTAL);
		BlockSerializer::mapSimple(Registry::END_GATEWAY(), Ids::END_GATEWAY);
		BlockSerializer::mapSimple(Registry::OBSIDIAN(), Ids::OBSIDIAN);
		BlockSerializer::mapSimple(Registry::BLOOD_INFUSED_OBSIDIAN(), CustomIds::BLOOD_INFUSED_OBSIDIAN);
		BlockSerializer::mapSimple(Registry::POLISHED_BLOOD_INFUSED_OBSIDIAN(), CustomIds::POLISHED_BLOOD_INFUSED_OBSIDIAN);
		BlockSerializer::mapSimple(Registry::GILDED_OBSIDIAN(), CustomIds::GILDED_OBSIDIAN);
		BlockSerializer::mapSimple(Registry::POLISHED_GILDED_OBSIDIAN(), CustomIds::POLISHED_GILDED_OBSIDIAN);
		BlockSerializer::mapSimple(Registry::POLISHED_OBSIDIAN(), CustomIds::POLISHED_OBSIDIAN);
		BlockSerializer::mapSimple(Registry::POLISHED_GLOWING_OBSIDIAN(), CustomIds::POLISHED_GLOWING_OBSIDIAN);
		BlockSerializer::mapSimple(Registry::PERIDOT(), CustomIds::PERIDOT);
		BlockSerializer::mapSimple(Registry::TOPAZ(), CustomIds::TOPAZ);
		BlockSerializer::mapSimple(Registry::PET_BOX(), CustomIds::PET_BOX);

		switch($serverType){
			case "lobby":
				BlockSerializer::map(Registry::IRON_PRESSURE_PLATE(), function(LobbyIPressurePlate $block) : Writer{
					return Writer::create(Ids::HEAVY_WEIGHTED_PRESSURE_PLATE)
					->writeInt(State::REDSTONE_SIGNAL, $block->getOutputSignalStrength());
				});
				BlockSerializer::map(Registry::GOLD_PRESSURE_PLATE(), function(LobbyGPressurePlate $block) : Writer{
					return Writer::create(Ids::LIGHT_WEIGHTED_PRESSURE_PLATE)
					->writeInt(State::REDSTONE_SIGNAL, $block->getOutputSignalStrength());
				});
				break;
			
			case "prison":
				break;

			case "skyblock":
				BlockSerializer::map(Registry::HOPPER(), function(Hopper $block) : Writer{
					return Writer::create(Ids::HOPPER)
						->writeBool(State::TOGGLE_BIT, $block->isPowered())
						->writeFacingWithoutUp($block->getFacing());
				});
				BlockSerializer::mapSimple(Registry::MOB_SPAWNER(), Ids::MOB_SPAWNER);
				BlockSerializer::mapSimple(Registry::AUTOMINER(), Ids::ELEMENT_95);
				BlockSerializer::mapSimple(Registry::DIMENSIONAL_BLOCK(), Ids::ELEMENT_105);
				BlockSerializer::mapSimple(Registry::ORE_GENERATOR(), Ids::ELEMENT_118);
				BlockSerializer::map(Registry::IRON_PRESSURE_PLATE(), function(SkyblockIPressurePlate $block) : Writer{
					return Writer::create(Ids::HEAVY_WEIGHTED_PRESSURE_PLATE)
					->writeInt(State::REDSTONE_SIGNAL, $block->getOutputSignalStrength());
				});
				BlockSerializer::map(Registry::GOLD_PRESSURE_PLATE(), function(SkyblockGPressurePlate $block) : Writer{
					return Writer::create(Ids::LIGHT_WEIGHTED_PRESSURE_PLATE)
					->writeInt(State::REDSTONE_SIGNAL, $block->getOutputSignalStrength());
				});
				BlockSerializer::map(Registry::STONE_PRESSURE_PLATE(), function(SkyblockSPressurePlate $block) : Writer{
					return Helper::encodeSimplePressurePlate($block, new Writer(Ids::STONE_PRESSURE_PLATE));
				});
				BlockSerializer::mapSimple(Registry::RED_MUSHROOM(), Ids::RED_MUSHROOM);
				BlockSerializer::map(Registry::RED_MUSHROOM_BLOCK(), function(RedMushroomBlock $block) : Writer{
					return Helper::encodeMushroomBlock($block, new Writer(Ids::RED_MUSHROOM_BLOCK));
				});
				BlockSerializer::mapSimple(Registry::BROWN_MUSHROOM(), Ids::BROWN_MUSHROOM);
				BlockSerializer::map(Registry::BROWN_MUSHROOM_BLOCK(), function(BrownMushroomBlock $block) : Writer{
					return Helper::encodeMushroomBlock($block, new Writer(Ids::BROWN_MUSHROOM_BLOCK));
				});
				break;

			case "pvp":
				break;

			case "faction":
				BlockSerializer::map(Registry::BEDROCK(), function(Block $block) : Writer{
					return Writer::create(Ids::BEDROCK)
						->writeBool(State::INFINIBURN_BIT, $block->burnsForever());
				});
				BlockSerializer::mapSimple(Registry::OBSIDIAN(), Ids::OBSIDIAN);
				break;
		}
	}
}