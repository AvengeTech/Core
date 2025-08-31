<?php

namespace core\discord\command;

use core\command\type\CoreCommand;
use pocketmine\command\CommandSender;
use core\Core;
use core\utils\TextFormat;

class DiscordCommand extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setAliases(["discord"]);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args)
	{
		$sender->sendMessage(TextFormat::GI . "Join our discord server! " . TextFormat::YELLOW . "avengetech.net/discord");
	}
}
