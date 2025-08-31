<?php

namespace core\etc\pieces\skin;

use pocketmine\utils\{
	BinaryStream,
};
use pocketmine\entity\Skin as PSkin;

use Ramsey\Uuid\Uuid;

use core\{
	Core,
	AtPlayer as Player
};

class Skin {

	public $plugin;
	public static $dir = "/[REDACTED]/skins/";

	public function __construct(Core $plugin) {
		$this->plugin = $plugin;
		@mkdir(self::$dir);
		$plugin->getServer()->getCommandMap()->register("saveskin", new SaveSkin($plugin, "saveskin", "Save skin"));
	}

	public function skinExists(string $name): bool {
		return file_exists(self::$dir . strtolower($name) . ".dat");
	}

	public static function getSkinData(string $name): ?string {
		if (!file_exists(self::$dir . strtolower($name) . ".png")) return null;
		return self::getTextureFromFile(self::$dir . strtolower($name) . ".png");
	}

	public function saveSkin(Player $player, string $name): void {
		$id = $player->getSkin()->getSkinId();
		$data = $player->getSkin()->getSkinData();

		file_put_contents(self::$dir . strtolower($name) . ".dat", $data);
	}

	public static function getSavedSkinData(string $name): string {
		if (!file_exists(self::$dir . strtolower($name) . ".dat")) return "";
		return file_get_contents(self::$dir . strtolower($name) . ".dat");
	}

	//Player Skin Limiting//
	public function getStrippedSkin(PSkin $skin): PSkin {
		return $skin; //workaround?
		/*$skinData = $skin->getSkinData();
		if($this->getSkinTransparencyPercentage($skinData) > 73){
			$skinData = \str_repeat("\0xFF", 2048);
		}

		$capeData = $skin->getCapeData();
		$geometryName = "";
		$geometryData = "";

		return new PSkin($skin->getSkinId(), $skinData, $capeData, $geometryName, $geometryData);*/
	}

	public static function getSkinTransparencyPercentage(string $skinData): int {
		switch (\strlen($skinData)) {
			case 8192:
				$maxX = 64;
				$maxY = 32;
				break;
			case 16384:
				$maxX = 64;
				$maxY = 64;
				break;
			case 65536:
				$maxX = 128;
				$maxY = 128;
				break;
			default:
				throw new \InvalidArgumentException('Inappropriate skin data length: ' . \strlen($skinData));
		}

		$stream = new BinaryStream($skinData);
		$transparentPixels = 0;
		for ($y = 0; $y < $maxY; ++$y) {
			for ($x = 0; $x < $maxX; ++$x) {
				$stream->getByte();
				$stream->getByte();
				$stream->getByte();
				$a = 127 - (int) \floor($stream->getByte() / 2);
				if ($a > 0) {
					++$transparentPixels;
				}
			}
		}
		return (int) \round($transparentPixels * 100 / ($maxX * $maxY));
	}

	public static function fromResource(string $baseDir, string $modelName, $geometry = ""): PSkin {
		$geometryData = "";
		if ($geometry === "") {
			$customGeometry = json_decode(file_get_contents($baseDir . "/" . $modelName . ".geo.json"), true, 512, JSON_THROW_ON_ERROR);
			$geometry = $customGeometry["minecraft:geometry"][0]["description"]["identifier"];
			$geometryData = json_encode($customGeometry, JSON_THROW_ON_ERROR);
		}

		return new PSkin(UUID::uuid4(), self::getTextureFromFile($baseDir . "/" . $modelName . ".png"), "", $geometry, $geometryData);
	}

	public static function getTextureFromFile(string $file): string {
		$path = $file;
		$image = imagecreatefrompng($path);
		[
			$width,
			$height
		] = getimagesize($path);
		$bytes = "";
		for ($y = 0; $y < $height; ++$y) {
			for ($x = 0; $x < $width; ++$x) {
				$color = @imagecolorsforindex($image, @imagecolorat($image, $x, $y));
				$bytes .= chr($color["red"]) . chr($color["green"]) . chr($color["blue"]) . chr((($color['alpha'] << 1) ^ 0xff) - 1);
			}
		}
		imagedestroy($image);

		return $bytes;
	}
}
