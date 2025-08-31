<?php

namespace core\rules\uis;

use pocketmine\utils\TextFormat;

use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;

use core\rules\{
	RuleManager,
	RuleCategory,
	Rule
};

use core\{
	Core,
	AtPlayer as Player
};

class CategoryUi extends SimpleForm {

	public $manager;
	public $category;

	public $rules = [];

	public function __construct(RuleManager $manager, RuleCategory $category) {
		$this->manager = $manager;
		$this->category = $category;

		parent::__construct($category->getColor() . "Rules", "Tap to read a rule.");

		$this->rules = $rules = $category->getRules(1);
		foreach ($rules as $rule) {
			$this->addButton(new Button($category->getColor() . $rule->getName() . "\n" . TextFormat::DARK_GRAY . TextFormat::ITALIC . "Tap to read!"));
		}
		$this->addButton(new Button("Go back"));
	}

	public function handle($response, Player $player) {
		$rules = $this->rules;
		$rule = $rules[$response] ?? null;
		if ($rule instanceof Rule) {
			$player->showModal(new RuleUi($this->manager, $this->category, $rule));
			return;
		}
		$player->showModal(new RulesUi(Core::getInstance(), $this->manager));
	}
}
