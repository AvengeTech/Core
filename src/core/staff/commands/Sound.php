<?php

namespace core\staff\commands;

use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\AtPlayer as Player;
use core\Core;
use core\utils\{
	TextFormat,
	GenericSound,
	PlaySound
};

class Sound extends CoreCommand {

	public \core\Core $plugin;

	public function __construct(\core\Core $plugin, $name, $description) {
		$this->plugin = $plugin;
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_STAFF);
		$this->setInGameOnly();
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args)
	{
		if (count($args) < 1) {
			$sender->sendMessage(TextFormat::RI . "Usage: /sound <id:pitch> [ex. /sound 2 or /sound mob.whatever.sound");
			return;
		}

		$ns = [];
		$ls = [];

		foreach ($args as $sound) {
			if (strstr($sound, ",")) {
				$sounds = explode(",", $sound);
				foreach ($sounds as $sound) {
					$sound = explode(":", $sound);
					if (count($sound) > 1) {
						if (is_numeric($sound[0])) {
							$sender->getWorld()->addSound($sender->getPosition(), new GenericSound($sender->getPosition(), $sound[0], 2, $sound[1]), [$sender]);
							$ns[] = $sound[0];
						} else {
							$sender->getWorld()->addSound($sender->getPosition(), new PlaySound($sender->getPosition(), $sound[0], 100, (int) $sound[1]), [$sender]);
							$ls[] = $sound[0];
						}
					} else {
						if (is_numeric($sound[0])) {
							$sender->getWorld()->addSound($sender->getPosition(), new GenericSound($sender->getPosition(), $sound[0]), [$sender]);
							$ns[] = $sound[0];
						} else {
							$sender->getWorld()->addSound($sender->getPosition(), new PlaySound($sender->getPosition(), $sound[0]), [$sender]);
							$ls[] = $sound[0];
						}
					}
				}
			} else {
				$sound = explode(":", $sound);
				if (count($sound) > 1) {
					if (is_numeric($sound[0])) {
						$sender->getWorld()->addSound($sender->getPosition(), new GenericSound($sender->getPosition(), $sound[0], 2, $sound[1]), [$sender]);
						$ns[] = $sound[0];
					} else {
						$sender->getWorld()->addSound($sender->getPosition(), new PlaySound($sender->getPosition(), $sound[0], 100, (int) $sound[1]), [$sender]);
						$ls[] = $sound[0];
					}
				} else {
					if (is_numeric($sound[0])) {
						$sender->getWorld()->addSound($sender->getPosition(), new GenericSound($sender->getPosition(), $sound[0]), [$sender]);
						$ns[] = $sound[0];
					} else {
						$sender->getWorld()->addSound($sender->getPosition(), new PlaySound($sender->getPosition(), $sound[0]), [$sender]);
						$ls[] = $sound[0];
					}
				}
			}
		}

		if (count($ns) + count($ls) > 1) {
			$ss = (empty($ns) ? "" : TextFormat::YELLOW . implode(TextFormat::GRAY . ", " . TextFormat::YELLOW, $ns) . TextFormat::GRAY . ", ") . (empty($ls) ? "" : TextFormat::YELLOW . implode(TextFormat::GRAY . ", " . TextFormat::YELLOW, $ls));
			$sender->sendMessage(TextFormat::GI . "Played the following sounds: " . $ss);
			return;
		}
		$sound = empty($ns) ? array_shift($ls) : array_shift($ns);
		$sender->sendMessage(TextFormat::GI . "Played sound with ID: " . TextFormat::YELLOW . $sound);
		return;
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
