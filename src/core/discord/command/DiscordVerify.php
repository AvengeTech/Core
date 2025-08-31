<?php

namespace core\discord\command;

use core\command\type\CoreCommand;
use core\rank\Rank;
use pocketmine\command\CommandSender;
use core\{
	Core,
	AtPlayer as Player
};
use core\utils\TextFormat;

class DiscordVerify extends CoreCommand {

	public function __construct(public Core $plugin, string $name, string $description) {
		parent::__construct($name, $description);
		$this->setAliases(["dv", "verify"]);
		$this->setInGameOnly();
	}

	public function handlePlayer(Player $sender, string $commandLabel, array $args) {
		$session = $sender->getSession()->getDiscord();
		if ($session->isVerified()) {
			Core::getInstance()->getDiscord()->userDataFromSnowflake($session->getSnowflake(), function (array $data) use ($sender): void {
				$username = ($data["username"] ?? "unknown") . "#" . ($data["discriminator"] ?? "0000");
				if ($sender->isConnected()) Core::broadcastToast(TextFormat::EMOJI_CONFETTI . TextFormat::AQUA . TextFormat::BOLD . " Discord linked!", TextFormat::GRAY . "Your Discord account is connected! (Username: " . TextFormat::YELLOW . $username . TextFormat::GRAY . ")", [$sender]);
			});
			return;
		}
		$session->verify(function (int $snowflake, bool $verified) use ($session, $sender): void {
			if ($verified) {
				Core::getInstance()->getDiscord()->userDataFromSnowflake($snowflake, function (array $data) use ($sender): void {
					$username = ($data["username"] ?? "unknown") . "#" . ($data["discriminator"] ?? "0000");
					if ($sender->isConnected()) Core::broadcastToast(TextFormat::EMOJI_CONFETTI . TextFormat::AQUA . TextFormat::BOLD . " Discord linked!", TextFormat::GRAY . "Your Discord account is connected! (Username: " . TextFormat::YELLOW . $username . TextFormat::GRAY . ")", [$sender]);
				});
				return;
			}
			$sender->sendMessage(TextFormat::RI . "You do not have a Discord account attached to your AvengeTech account! Join our Discord and type " . TextFormat::YELLOW . "/connect " . $session->getCode() . TextFormat::GRAY . " in any channel to link your account! " . TextFormat::YELLOW . "avengetech.net/discord");
		});
	}
}
