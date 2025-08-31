<?php

namespace core\rules\uis;

use core\ui\windows\SimpleForm;
use core\ui\elements\simpleForm\Button;

use core\rules\{
	RuleManager,
	RuleCategory,
	Rule
};

use core\AtPlayer as Player;

class RuleUi extends SimpleForm {

	public $manager;
	public $category;

	public function __construct(RuleManager $manager, RuleCategory $category, Rule $rule) {
		$this->manager = $manager;
		$this->category = $category;

		parent::__construct($category->getColor() . $rule->getName(), $rule->getDescription());

		$this->addButton(new Button("Go back"));
	}

	public function handle($response, Player $player) {
		$player->showModal(new CategoryUi($this->manager, $this->category));
	}
}
