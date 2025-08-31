<?php

namespace core\network\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

use core\{
	Core,
	AtPlayer as Player
};
use core\command\type\CoreCommand;
use core\rank\Rank;
use core\ui\elements\simpleForm\Button;
use core\ui\windows\SimpleForm;
use core\utils\TextFormat;

class FormTest extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
		$this->setInGameOnly();
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args)
	{
		if (count($args) === 0) {
			$sender->sendMessage(TextFormat::RI . "Usage: /ft <title> <text> [buttons]");
			return;
		}

		$title = array_shift($args);
		$text = array_shift($args);
		$buttons = $args;

		$form = new SimpleForm($title, $text);
		foreach ($buttons as $button) {
			$form->addButton(new Button($button));
		}
		$sender->showModal($form);
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
