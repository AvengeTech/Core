<?php

namespace core\utils;

use pocketmine\entity\{
	Human,
	Skin
};
use pocketmine\utils\BinaryStream;

use core\Core;

class SkinUtils{

	const TYPE_DEFAULT = 0;
	const TYPE_SLIM = 1;

	const CAPE_PATH = "/[REDACTED]/capes/";
	const COSMETIC_PATH = "/[REDACTED]/cosmetics/";

	const COSMETIC_IDENTIFIER = "geometry.cosmetics";

	const DEFAULT_BONES = [
		"root",
		"waist",
		"body",
		"head",
		"hat",
		"leftArm",
		"leftSleeve",
		"leftItem",
		"rightArm",
		"rightSleeve",
		"rightItem",
		"capeinside",
		"jacket",
		"cape",
		"leftLeg",
		"leftPants",
		"rightLeg",
		"rightPants"
	];

	public static function capeExists(string $capeName): bool {
		return file_exists(self::CAPE_PATH . "{$capeName}.png");
	}

	public static function createCape(string $capeName) {
		$path = self::CAPE_PATH . "{$capeName}.png";

		$img = \imagecreatefrompng($path);
		$rgba = '';
		for ($y = 0; $y < \imagesy($img); $y++) {
			for ($x = 0; $x < \imagesx($img); $x++) {
				$rgb = \imagecolorat($img, $x, $y);
				$r = ($rgb >> 16) & 0xFF;
				$g = ($rgb >> 8) & 0xFF;
				$b = $rgb & 0xFF;
				$rgba .= \chr($r) . \chr($g) . \chr($b) . \chr(255);
			}
		}
		\imagedestroy($img);

		return $rgba;
	}

	public static function getSkinWithCape(Skin $skin, string $capeName): Skin {
		if (!self::capeExists($capeName)) return $skin;
		try {
			$capeData = self::createCape($capeName);
		} catch (\Exception $e) {
			Core::getInstance()->getLogger()->logException($e);
			return $skin;
		}
		return new Skin(
			$skin->getSkinId(),
			$skin->getSkinData(),
			$capeData,
			$skin->getGeometryName(),
			$skin->getGeometryData()
		);
	}

	public static function getSkinWithoutCape(Human $entity): Skin {
		$skin = $entity->getSkin();
		return new Skin(
			$skin->getSkinId(),
			$skin->getSkinData(),
			"",
			$skin->getGeometryName(),
			$skin->getGeometryData()
		);
	}

	public static function skinToPNG(string $skinData, int $dim = 64) {
		// https://github.com/moskadev/EverybodyThonk/blob/master/src/supermaxalex/EverybodyThonk/SkinManager.php
		$img = imagecreatetruecolor($dim, $dim);
		imagealphablending($img, false);
		imagesavealpha($img, true);
		$stream = new BinaryStream($skinData);

		for ($y = 0; $y < $dim; ++$y) {
			for ($x = 0; $x < $dim; ++$x) {
				$r = $stream->getByte();
				$g = $stream->getByte();
				$b = $stream->getByte();
				$a = 127 - (int) floor($stream->getByte() / 2);

				$colour = imagecolorallocatealpha($img, $r, $g, $b, $a);
				imagesetpixel($img, $x, $y, $colour);
			}
		}

		return $img;
	}

	public static function getImageData($image): string {
		$skinbytes = "";
		for ($y = 0; $y < imagesy($image); $y++) {
			for ($x = 0; $x < imagesx($image); $x++) {
				$colorat = @imagecolorat($image, $x, $y);
				$a = ((~((int)($colorat >> 24))) << 1) & 0xff;
				$r = ($colorat >> 16) & 0xff;
				$g = ($colorat >> 8) & 0xff;
				$b = $colorat & 0xff;
				$skinbytes .= chr($r) . chr($g) . chr($b) . chr($a);
			}
		}
		imagedestroy($image);

		return $skinbytes;
	}

	public static function getResetSkin(Skin $playerSkin): Skin {
		return new Skin("Custom", self::getImageData(self::skinToPNG($playerSkin->getSkinData())));
	}

	public static function combineBones(array $cosmetics = [], int $type = self::TYPE_DEFAULT): array {
		$bones = json_decode(file_get_contents(self::COSMETIC_PATH . ($type == self::TYPE_DEFAULT ? "blank.json" : "blankSlim.json")), true);
		$bones["minecraft:geometry"][0]["description"]["identifier"] = self::COSMETIC_IDENTIFIER . ($type !== self::TYPE_DEFAULT ? "_slim" : "");
		foreach ($cosmetics as $cosmetic) {
			try {
				if (($exploded = explode("/", $cosmetic)) > 2) {
					$dir = self::COSMETIC_PATH . $exploded[0] . "/" . $exploded[1] . "/" . $exploded[1] . ".json";
				} else {
					$dir = self::COSMETIC_PATH . $cosmetic . "/" . $cosmetic . ".json";
				}

				$newBones = file_get_contents($dir);
				$newBones = json_decode($newBones, true);
				foreach ($newBones["minecraft:geometry"][0]["bones"] as $newBone) {
					if (!in_array($newBone["name"], self::DEFAULT_BONES)) {
						$bones["minecraft:geometry"][0]["bones"][] = $newBone;
					}
				}
			} catch (\Exception $e) {
			}
		}

		return $bones;
	}

	public static function combineSkinData(Skin $skin, array $cosmetics = []) {
		$layer2 = self::skinToPNG($skin->getSkinData());

		$width  = imagesx($layer2);
		$height = imagesy($layer2);

		foreach ($cosmetics as $cosmetic) {
			if (($exploded = explode("/", $cosmetic)) > 2) {
				$dir = self::COSMETIC_PATH . $exploded[0] . "/" . $exploded[1] . "/" . $exploded[1] . ".png";
			} else {
				$dir = self::COSMETIC_PATH . $cosmetic . "/" . $cosmetic . ".png";
			}
			$layer1 = imagecreatefrompng($dir);

			imagepalettetotruecolor($layer2);
			imagepalettetotruecolor($layer1);
			imagesavealpha($layer1, true);
			imagealphablending($layer2, true);
			imagesavealpha($layer2, true);
			imagecopy($layer2, $layer1, 0, 0, 0, 0, 64, 64);

			imagedestroy($layer1);
		}

		return self::getImageData($layer2);
	}

	public static function layerSkin(Skin $skin, array $cosmetics): Skin {
		if (count($cosmetics) === 0) return $skin;

		$newBones = json_encode(self::combineBones($cosmetics, ($slim = stripos($skin->getGeometryName(), "slim") !== false) ? self::TYPE_SLIM : self::TYPE_DEFAULT));

		//file_put_contents(self::COSMETIC_PATH . "test.geo.json", $newBones);
		$newSkinData = self::combineSkinData($skin, $cosmetics);

		return new Skin("Custom", $newSkinData, $skin->getCapeData(), self::COSMETIC_IDENTIFIER . ($slim ? "_slim" : ""), $newBones);
	}
}
