<?php

namespace core\discord\command;

use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\command\CommandSender;

use core\{
	Core,
	AtPlayer as Player
};
use core\discord\objects\{
	Post,
	Webhook
};
use core\utils\TextFormat;

class DiscordSend extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_STAFF);
		$this->setAliases(["ds"]);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args): void {
		if (count($args) <= 1) {
			$sender->sendMessage(TextFormat::RI . "Usage: /discordsend <username> <message>");
			return;
		}
		$name = array_shift($args);
		$message = str_replace(":brk:", PHP_EOL, implode(" ", $args));
		$post = new Post($message, $name, "[REDACTED]");
		$post->setWebhook(Webhook::getWebhookByName("other"));
		$post->send();
		$sender->sendMessage(TextFormat::GI . "Message sent to Discord!");
	}
}
