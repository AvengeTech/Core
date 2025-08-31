<?php

namespace core\utils\command;

use core\AtPlayer as Player;
use core\Core;
use core\command\type\CoreCommand;
use core\ui\windows\CustomForm;
use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;
use core\utils\TextFormat;
use core\rank\Rank;
use pocketmine\command\CommandSender;
use pocketmine\plugin\Plugin;

class TestNewForm extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setInGameOnly();
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args)
	{
		$type = array_shift($args) ?? "simple";
		if ($type === "simple") {
			$form = new SimpleForm(TextFormat::ESCAPE . "m" . TextFormat::ESCAPE . "a TITLE", "Test custom form");
			for ($i = 1; $i <= 5; $i++) $form->addButton(new Button("Test button $i"));
		} else {
			$form = new CustomForm(TextFormat::ESCAPE . "m" . TextFormat::ESCAPE . "a TITLE", "Test custom form");
		}
		$sender->showModal($form);
	}

	public function getPlugin(): Plugin {
		return $this->plugin;
	}
}
