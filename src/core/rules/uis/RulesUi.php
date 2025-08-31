<?php

namespace core\rules\uis;

use pocketmine\utils\TextFormat;

use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;

use core\rules\{
	RuleManager,
	RuleCategory
};

use core\{
	Core,
	AtPlayer as Player
};

class RulesUi extends SimpleForm {

	public $plugin;
	public $manager;

	public $categories = [];

	public function __construct(Core $plugin, RuleManager $manager) {
		$this->plugin = $plugin;
		$this->manager = $manager;

		parent::__construct("Rules", "These are the rules. Read them. Practice them. Live by them.");

		$categories = $manager->getCategories();
		$tc = [];
		foreach ($categories as $category) {
			if (
				empty($category->getServers()) ||
				in_array(Core::getInstance()->getNetwork()->getServerType(), $category->getServers())
			) {
				$tc[] = $category;

				$this->addButton(new Button($category->getColor() . $category->getName() . "\n" . TextFormat::DARK_GRAY . TextFormat::ITALIC . "Tap to view!"));
			}
		}
		$this->categories = $tc;
	}

	public function handle($response, Player $player) {
		$categories = $this->categories;
		$category = $categories[$response] ?? null;
		if ($category instanceof RuleCategory) {
			$player->showModal(new CategoryUi($this->manager, $category));
		}
	}
}
