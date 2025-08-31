<?php

namespace core\rules;

use core\Core;
use core\rules\commands\RulesCommand;

class Rules {

	public $plugin;

	public $rulemanager = null;

	public function __construct(Core $plugin) {
		$this->plugin = $plugin;

		$this->rulemanager = new RuleManager();
		$plugin->getServer()->getCommandMap()->register("rules", new RulesCommand($this->plugin, "rules", "View the server rules."));
	}

	public function getRuleManager(): ?RuleManager {
		return $this->rulemanager;
	}
}
