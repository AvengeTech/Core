<?php

namespace core\utils\block;

use pocketmine\block\Block;
use pocketmine\block\Slab;
use pocketmine\block\Stair;
use pocketmine\block\utils\SlabType;
use pocketmine\block\Wood;
use pocketmine\data\bedrock\block\BlockStateNames as StateNames;
use pocketmine\data\bedrock\block\convert\BlockStateReader as Reader;
use pocketmine\data\bedrock\block\convert\BlockStateDeserializerHelper as Helper;
use pocketmine\world\format\io\GlobalBlockStateHandlers;

class BlockDeserializer{


	public static function map(string $identifier, \Closure $deserializer): void {
		$instance = GlobalBlockStateHandlers::getDeserializer();

		try {
			$instance->map($identifier, $deserializer);
		} catch (\InvalidArgumentException) {
			$deserializerProperty = new \ReflectionProperty($instance, "deserializeFuncs");
			$deserializerProperty->setAccessible(true);
			$value = $deserializerProperty->getValue($instance);
			$value[$identifier] = $deserializer;
			$deserializerProperty->setValue($instance, $value);
		}
	}

	public static function mapSimple(string $identifier, Block $block): void {
		$instance = GlobalBlockStateHandlers::getDeserializer();

		try {
			$instance->mapSimple($identifier, fn() => clone $block);
		} catch (\InvalidArgumentException) {
			$deserializerProperty = new \ReflectionProperty($instance, "deserializeFuncs");
			$deserializerProperty->setAccessible(true);
			$value = $deserializerProperty->getValue($instance);
			$value[$identifier] = fn() => clone $block;
			$deserializerProperty->setValue($instance, $value);
		}
	}

	public static function mapSlab(string $singleIdentifier, string $doubleIdentifier, Slab $slab) : void{
		$instance = GlobalBlockStateHandlers::getDeserializer();
		$deserializer = fn() => clone $slab;

		try{
			$instance->mapSlab($singleIdentifier, $doubleIdentifier, $deserializer);
		}catch(\InvalidArgumentException){
			$deserializerProperty = new \ReflectionProperty($instance, "deserializeFuncs");
			$deserializerProperty->setAccessible(true);
			$value = $deserializerProperty->getValue($instance);

			$value[$singleIdentifier] = fn(Reader $in) : Slab => $deserializer($in)->setSlabType($in->readSlabPosition());
			$deserializerProperty->setValue($instance, $value);

			$value[$doubleIdentifier] = function(Reader $in) use ($deserializer) : Slab{
				$in->ignored(StateNames::MC_VERTICAL_HALF);

				return $deserializer($in)->setSlabType(SlabType::DOUBLE());
			};
			$deserializerProperty->setValue($instance, $value);
		}
	}

	public static function mapStairs(string $identifier, Stair $stair) : void{
		$instance = GlobalBlockStateHandlers::getDeserializer();
		$deserializer = fn() => clone $stair;

		try{
			$instance->mapStairs($identifier, $deserializer);
		}catch(\InvalidArgumentException){
			$deserializerProperty = new \ReflectionProperty($instance, "deserializeFuncs");
			$deserializerProperty->setAccessible(true);
			$value = $deserializerProperty->getValue($instance);
			$value[$identifier] = fn(Reader $in) : Stair => Helper::decodeStairs($deserializer(), $in);
			$deserializerProperty->setValue($instance, $value);
		}
	}

	public static function mapLog(string $unstrippedIdentifier, string $strippedIdentifier, Wood $log) : void{
		$instance = GlobalBlockStateHandlers::getDeserializer();
		$deserializer = fn() => clone $log;

		try{
			$instance->mapLog($unstrippedIdentifier, $strippedIdentifier, $deserializer);
		}catch(\InvalidArgumentException){
			$isStripped = false;

			foreach([$unstrippedIdentifier, $strippedIdentifier] as $identifier)
			{
				$deserializerProperty = new \ReflectionProperty($instance, "deserializeFuncs");
				$deserializerProperty->setAccessible(true);
				$value = $deserializerProperty->getValue($instance);
				$value[$identifier] = fn(Reader $in) => Helper::decodeLog($deserializer(), $isStripped, $in);
				$deserializerProperty->setValue($instance, $value);

				$isStripped = true;
			}
		}
	}
}