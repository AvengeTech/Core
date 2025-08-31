<?php

namespace core\utils;

use core\discord\objects\Post;
use core\discord\objects\Webhook;
use pocketmine\entity\Location;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\world\{
	Position,
	World
};

use core\utils\entity\{
	TempItemEntity,
	TempExperienceOrb,
	TempFallingBlock
};
use Exception;
use pmmp\thread\ThreadSafeArray;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\Tag;
use pocketmine\network\mcpe\protocol\serializer\BitSet;

class Utils {

	public static function dropTempItem(World $world, Vector3 $source, Item $item, ?Vector3 $motion = null, int $delay = 10, int $maxAge = 30): ?TempItemEntity {
		if ($item->isNull()) {
			return null;
		}

		$itemEntity = new TempItemEntity(Location::fromObject($source, $world, lcg_value() * 360, 0), $item);
		$itemEntity->setMaxAge($maxAge);

		$itemEntity->setPickupDelay($delay);
		$itemEntity->setMotion($motion ?? new Vector3(lcg_value() * 0.2 - 0.1, 0.2, lcg_value() * 0.2 - 0.1));
		$itemEntity->spawnToAll();

		return $itemEntity;
	}

	public static function dropTempExperience(World $world, Vector3 $pos, int $amount, int $maxAge = 200): array {
		$orbs = [];

		foreach (TempExperienceOrb::splitIntoOrbSizes($amount) as $split) {
			$orb = new TempExperienceOrb(Location::fromObject($pos, $world, lcg_value() * 360, 0), $split);

			$orb->setMotion(new Vector3((lcg_value() * 0.2 - 0.1) * 2, lcg_value() * 0.4, (lcg_value() * 0.2 - 0.1) * 2));
			$orb->setMaxAge($maxAge);
			$orb->spawnToAll();

			$orbs[] = $orb;
		}

		return $orbs;
	}


	public static function recursiveCopy(string $source, string $dest): void {
		@mkdir($dest, 0777);

		$files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);

		/** @var \SplFileInfo $fileInfo */
		foreach($files as $fileInfo){
			if($filePath = $fileInfo->getRealPath()){
				if($fileInfo->isFile()){
					copy($filePath, $dest . DIRECTORY_SEPARATOR . (in_array($fileInfo->getBasename(), ["level.dat"]) ? "" : "db" . DIRECTORY_SEPARATOR) . $fileInfo->getBasename());
				}else{
					mkdir($dest . DIRECTORY_SEPARATOR . $fileInfo->getBasename());
				}
			}
		}
	}

	public static function recursiveDelete(string $dir): bool {
		if (!file_exists($dir)) {
			return true;
		}

		if (!is_dir($dir)) {
			return unlink($dir);
		}

		foreach (scandir($dir) as $item) {
			if ($item == '.' || $item == '..') {
				continue;
			}

			if (!self::recursiveDelete($dir . DIRECTORY_SEPARATOR . $item)) {
				return false;
			}
		}
		return rmdir($dir);
	}


	public static function threadSafeToArray(ThreadSafeArray|array $threadSafe): array {
		$array = [];
		foreach ($threadSafe as $key => $value) {
			if ($value instanceof ThreadSafeArray) {
				$array[$key] = self::threadSafeToArray($value);
			} else {
				$array[$key] = $value;
			}
		}
		return $array;
	}


	public static function blockFlyBoom(Position $pos, float $force = 0.7, int $maxFlyingBlocks = 8): void {
		$blockTypes = [];
		for ($i = 0; $i < $maxFlyingBlocks * 2; $i++) {
			$newPos = $pos->add(mt_rand(-5, 5), mt_rand(-2, 0), mt_rand(-5, 5));
			$block = $pos->getWorld()->getBlock($newPos);
			if ($block->getTypeId() !== 0 && !isset($blockTypes[($tag = $block->getTypeId() . ":" . $block->getStateId())])) {
				$blockTypes[$tag] = $block;
			}
		}
		if (count($blockTypes) === 0) return;
		for ($i = 0; $i < $maxFlyingBlocks; $i++) {
			$entity = new TempFallingBlock(Location::fromObject($pos->add(0, 2, 0), $pos->getWorld()), $blockTypes[array_rand($blockTypes)]);

			$yaw = mt_rand(0, 360);
			$pitch = mt_rand(-10, 10);

			$motX = -sin($yaw / 180 * M_PI) * cos($pitch / 180 * M_PI);
			$motY = -sin($pitch / 180 * M_PI);
			$motZ = cos($yaw / 180 * M_PI) * cos($pitch / 180 * M_PI);
			$motV = (new Vector3($motX, $motY, $motZ))->multiply($force);

			$entity->setMotion($motV);
			$entity->spawnToAll();
		}
	}

	public static function arrayToString(array $array, int $index = 0): string {
		$finalString = "[\n";
		foreach ($array as $key => $value) {
			if (is_array($value)) $value = self::arrayToString($array, $index + 1);
			else {
				try {
					$value = strval($value);
				} catch (Exception $_) {
					try {
						$value = $value::class;
					} catch (Exception $e) {
						$value = "<skipped value: " . $e->getMessage() . ">";
					}
				}
			}
			$end = $key == array_key_last($array) ? "\n" : ",\n";
			$finalString .= str_repeat("	", $index + 1) . $key . " => " . $value . $end;
		}
		$finalString .= str_repeat("	", $index) . "]";
		return $finalString;
	}

	/**
	 * **BEWARE**
	 * DOES NOT CHECK FOR CIRCULAR DEPENDENCY
	 */
	public static function dumpVals(...$values) {
		var_dump(...$values);
		$backtrace = debug_backtrace();
		$calledFrom = $backtrace[array_key_first($backtrace)];
		$msg = "";
		foreach ($values as $k => $val) {
			if (is_array($val)) $v = self::arrayToString($val);
			else {
				try {
					$v = strval($val);
				} catch (\Exception $_) {
					try {
						$v = $val::class;
					} catch (\Exception $e) {
						$v = "<skipped value: " . $e->getMessage() . ">";
					}
				}
			}
			$msg .= "```\n" . $v . "\n```";
		}
		$post = new Post($msg, $calledFrom['file'] . ":" . $calledFrom['line']);
		$post->setWebhook(Webhook::getWebhookByName("other"));
		$post->send();
	}

	/**
	 * From Customies
	 */
	public static function getTagType($type): ?Tag {
		return match (true) {
			is_array($type) => self::getArrayTag($type),
			is_bool($type) => new ByteTag($type ? 1 : 0),
			is_float($type) => new FloatTag($type),
			is_int($type) => new IntTag($type),
			is_string($type) => new StringTag($type),
			$type instanceof CompoundTag => $type,
			default => null,
		};
	}

	/**
	 * From Customies
	 */
	private static function getArrayTag(array $array): Tag {
		if(array_keys($array) === range(0, count($array) - 1)) {
			return new ListTag(array_map(fn($value) => self::getTagType($value), $array));
		}
		$tag = CompoundTag::create();
		foreach($array as $key => $value){
			$tag->setTag($key, self::getTagType($value));
		}
		return $tag;
	}

	public static function arrayToBitSet(array $array, int $maxItems = -1): BitSet {
		if ($maxItems < 0) $maxItems = count($array);

		$set = new BitSet($maxItems * (PHP_INT_SIZE * 8), []);
		foreach ($array as $i => $k) {
			if (is_bool($k)) $set->set($i, $k);
			else $set->set($k, true);
		}

		return $set;
	}

	public static function getRemainingTimeSimplified(int $futureTimestamp): string {
		$now = time();
		$diff = $futureTimestamp - $now;

		if ($diff <= 0) {
			return "N/A";
		}

		$units = [
			'day'    => 86400,
			'hour'   => 3600,
			'minute' => 60,
			'second' => 1
		];

		$result = [];
		foreach ($units as $name => $seconds) {
			if ($diff >= $seconds) {
				$value = floor($diff / $seconds);
				$diff %= $seconds;
				$result[] = "$value $name" . ($value > 1 ? 's' : '');
			}
			if (count($result) == 2) break;
		}

		return implode(' ', $result);
	}
}
