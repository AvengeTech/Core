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

class DiscordSudo extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setHierarchy(Rank::HIERARCHY_HEAD_MOD);
	}

	public function handle(CommandSender $sender, string $commandLabel, array $args): void {
		$command = implode(" ", $args);
		$post = new Post($command, $sender->getName(), "[REDACTED]");
		$post->setWebhook(Webhook::getWebhookByName(Core::getInstance()->getNetwork()->getServerManager()->getThisServer()->getIdentifier()));
		$post->send();
		$sender->sendMessage(TextFormat::GI . "Message sent to Discord!");
	}
}
