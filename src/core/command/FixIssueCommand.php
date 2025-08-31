<?php

namespace core\command;

use core\command\type\CoreCommand;
use core\rank\Rank;
use core\utils\TextFormat;
use pocketmine\command\CommandSender;

class FixIssueCommand extends CoreCommand {

	public function __construct() {
		parent::__construct("fixissue", "Temp command to fix specific issues we may create");
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args): void {
		$sender->sendMessage(TextFormat::RI . "No issues to fix atm.");
	}
}
