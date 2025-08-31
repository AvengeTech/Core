<?php

namespace core\utils;

use pocketmine\entity\{
	Human,
	Skin
};
use pocketmine\utils\BinaryStream;

use core\Core;

class CapeData {

	const MODE_64 = 0;
	const MODE_128 = 1;

	const CAPE_PATH = "/[REDACTED]/capes/";

	public function capeExists(string $capeName): bool {
		return file_exists(self::CAPE_PATH . "$capeName.png");
	}

	public function createCape(string $capeName) {
		$path = self::CAPE_PATH . "$capeName.png";

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

	public function getSkinWithCape(Human $entity, string $capeName): Skin {
		$skin = $entity->getSkin();
		if (!$this->capeExists($capeName)) return $skin;
		try {
			$capeData = $this->createCape($capeName);
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

	public function getSkinWithoutCape(Human $entity): Skin {
		$skin = $entity->getSkin();
		return new Skin(
			$skin->getSkinId(),
			$skin->getSkinData(),
			"",
			$skin->getGeometryName(),
			$skin->getGeometryData()
		);
	}

	public function skinToPNG(string $skinData, int $dim = 64) {
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

	public function skinToPNG128($img) {
		$newImg = imagecreatetruecolor(128, 128);
		//imagealphablending($newImg, false);
		//imagesavealpha($newImg, true);
		$green = imagecolorallocate($newImg, 0, 153, 0);
		imagefilledrectangle($newImg, 0, 0, 127, 127, $green);
		$black = imagecolorallocate($newImg, 0, 0, 0);
		imagecolortransparent($newImg, $green);

		imagecopymerge($newImg, $img, 0, 0, 0, 0, 64, 64, 100);

		//for($x = 0; $x < 64; $x++){
		//	for($y = 0; $y < 64; $y++){
		//		$rgb = imagecolorat($img, $x, $y);
		//		$r = ($rgb >> 16) & 0xFF;
		//		$g = ($rgb >> 8) & 0xFF;
		//		$b = $rgb & 0xFF;
		//		$color = imagecolorallocatealpha($newImg, $r, $g, $b, 1);
		//		imagesetpixel($newImg, $x, $y, $color);
		//	}
		//}

		imagedestroy($img);

		return $newImg;
	}

	public function getImageData($image): string {
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

	public function getResetSkin(Skin $playerSkin): Skin {
		return new Skin("Custom", $this->getImageData($this->skinToPNG($playerSkin->getSkinData())));
	}

	public function layerSkin(Skin $skin, string $cosmetic): Skin {
		if ($cosmetic === "") return $skin;
		$layer2 = self::skinToPNG($skin->getSkinData());
		$layer1 = imagecreatefrompng("/[REDACTED]/cosmetics/$cosmetic/$cosmetic.png");

		$mode = self::MODE_64;

		$width  = imagesx($layer2);
		$height = imagesy($layer2);

		imagepalettetotruecolor($layer2);
		imagepalettetotruecolor($layer1);
		imagesavealpha($layer1, true);
		imagealphablending($layer2, true);
		imagesavealpha($layer2, true);
		imagecopy($layer2, $layer1, 0, 0, 0, 0, 64, 64);

		$json = file_get_contents("/[REDACTED]/cosmetics/$cosmetic/$cosmetic.json");

		return new Skin("Custom", $this->getImageData($layer2), "", "geometry." . $cosmetic, $json);
	}
}
