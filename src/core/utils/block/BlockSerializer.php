<?php

declare(strict_types=1);

namespace core\utils\block;

use pocketmine\block\Block;
use pocketmine\block\Slab;
use pocketmine\block\Stair;
use pocketmine\block\Wood;
use pocketmine\data\bedrock\block\convert\BlockStateWriter as Writer;
use pocketmine\data\bedrock\block\convert\BlockStateSerializerHelper as Helper;
use pocketmine\world\format\io\GlobalBlockStateHandlers;

class BlockSerializer{

	public static function map(Block $block, \Closure $serializer) : void{
		$instance = GlobalBlockStateHandlers::getSerializer();

		try{
			$instance->map($block, $serializer);
		}catch(\InvalidArgumentException){
			$serializerProperty = new \ReflectionProperty($instance, "serializers");
			$serializerProperty->setAccessible(true);
			$value = $serializerProperty->getValue($instance);
			$value[$block->getTypeId()] = $serializer;
			$serializerProperty->setValue($instance, $value);
		}
	}

	public static function mapSimple(Block $block, string $identifier) : void{
		$instance = GlobalBlockStateHandlers::getSerializer();

		try{
			$instance->mapSimple($block, $identifier);
		}catch(\InvalidArgumentException){
			$serializerProperty = new \ReflectionProperty($instance, "serializers");
			$serializerProperty->setAccessible(true);
			$value = $serializerProperty->getValue($instance);
			$value[$block->getTypeId()] = fn() => Writer::create($identifier);
			$serializerProperty->setValue($instance, $value);
		}
	}

	public static function mapLog(Wood $wood, string $unstrippedIdentifier, string $strippedIdentifier) : void{
		$instance = GlobalBlockStateHandlers::getSerializer();

		try{
			$instance->mapLog($wood, $unstrippedIdentifier, $strippedIdentifier);
		}catch(\InvalidArgumentException){
			$serializerProperty = new \ReflectionProperty($instance, "serializers");
			$serializerProperty->setAccessible(true);
			$value = $serializerProperty->getValue($instance);
			$value[$wood->getTypeId()] = fn(Wood $block) => Helper::encodeLog($block, $unstrippedIdentifier, $strippedIdentifier);
			$serializerProperty->setValue($instance, $value);
		}
	}

	public static function mapSlab(Slab $slab, string $singleIdentifier, string $doubleIdentifier) : void{
		$instance = GlobalBlockStateHandlers::getSerializer();

		try{
			$instance->mapSlab($slab, $singleIdentifier, $doubleIdentifier);
		}catch(\InvalidArgumentException){
			$serializerProperty = new \ReflectionProperty($instance, "serializers");
			$serializerProperty->setAccessible(true);
			$value = $serializerProperty->getValue($instance);
			$value[$slab->getTypeId()] = fn(Slab $block) => Helper::encodeSlab($block, $singleIdentifier, $doubleIdentifier);
			$serializerProperty->setValue($instance, $value);
		}
	}

	public static function mapStairs(Stair $stair, string $identifier) : void{
		$instance = GlobalBlockStateHandlers::getSerializer();

		try{
			$instance->mapStairs($stair, $identifier);
		}catch(\InvalidArgumentException){
			$serializerProperty = new \ReflectionProperty($instance, "serializers");
			$serializerProperty->setAccessible(true);
			$value = $serializerProperty->getValue($instance);
			$value[$stair->getTypeId()] = fn(Stair $block) => Helper::encodeStairs($block, Writer::create($identifier));
			$serializerProperty->setValue($instance, $value);
		}
	}
}