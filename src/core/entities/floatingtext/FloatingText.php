<?php

namespace core\entities\floatingtext;

use pocketmine\math\Vector3;

use core\{
	Core,
	AtPlayer as Player
};

class FloatingText {

	public array $texts = []; ///
	public array $lc = [];

	public function __construct(public Core $plugin) {
		$ftarr = TextData::TEXT_DATA[$this->plugin->getNetwork()->getServerType()] ?? [];
		foreach ($ftarr as $name => $data) {
			$this->texts[$name] = new Text($name, $data["text"], new Vector3(...explode(",", $data["position"])), $data["level"]);
		}
		$ftarr = TextData::TEXT_DATA[$this->plugin->getNetwork()->getIdentifier()] ?? [];
		foreach ($ftarr as $name => $data) {
			$this->texts[$name] = new Text($name, $data["text"], new Vector3(...explode(",", $data["position"])), $data["level"]);
		}
	}

	public function tick(): void {
		foreach ($this->lc as $name => $lvl) {
			$player = $this->plugin->getServer()->getPlayerExact($name);
			unset($this->lc[$name]);
			if ($player instanceof Player) {
				$texts = $this->getTexts();
				foreach ($texts as $text) {
					if ($text->getWorldName() == $lvl) {
						$text->spawn($player);
					} else {
						$text->despawn($player);
					}
				}
			}
		}
	}

	public function onJoin(Player $player): void {
		$texts = $this->getTexts();
		foreach ($texts as $name => $text) {
			if ($text->getWorldName() == $player->getWorld()->getDisplayName()) {
				$text->spawn($player);
			} else {
				$text->despawn($player);
			}
		}
	}

	public function onQuit(Player $player): void {
		$texts = $this->getTexts();
		foreach ($texts as $name => $text) {
			if (isset($text->getSpawnedTo()[$player->getName()])) {
				$text->despawn($player);
			}
		}
	}

	public function changeLevel(Player $player, string $newlevel): void {
		$this->lc[$player->getName()] = $newlevel;
	}

	public function getTexts(): array {
		return $this->texts;
	}

	public function getText(string $name): ?Text {
		return $this->getTexts()[$name] ?? null;
	}
}
