<?php

namespace core\discord\command;

use pocketmine\Server;
use pocketmine\command\CommandSender;
use pocketmine\utils\{
	TextFormat,
	ObjectSet
};
use pocketmine\lang\{
	Language,
	Translatable
};
use pocketmine\plugin\Plugin;
use pocketmine\permission\{
	PermissibleBase,
	PermissionAttachment,
};
use core\Core;
use core\discord\objects\{
	Post,
	Webhook
};

class DiscordSender implements CommandSender {

	const BOT_SNOWFLAKE = 45;

	public $snowflake = 0;

	private $perm;
	public $lineHeight = null;

	public function __construct() {
		$this->perm = new PermissibleBase([]);
	}

	public function isPermissionSet($name): bool {
		return true;
	}

	public function hasPermission($name): bool {
		return true;
	}

	public function addAttachment(Plugin $plugin, string $name = null, bool $value = null): PermissionAttachment {
		return $this->perm->addAttachment($plugin, $name, $value);
	}

	public function removeAttachment(PermissionAttachment $attachment): void {
		$this->perm->removeAttachment($attachment);
	}

	public function recalculatePermissions(): array {
		return $this->perm->recalculatePermissions();
	}

	public function getEffectivePermissions(): array {
		return $this->perm->getEffectivePermissions();
	}

	public function isOp(): bool {
		return true;
	}

	public function setOp(bool $value): void {
	}

	public function getLanguage(): Language {
		return $this->getServer()->getLanguage();
	}

	public function setBasePermission($name, bool $grant): void {
	}
	public function unsetBasePermission($name): void {
	}
	public function getPermissionRecalculationCallbacks(): ObjectSet {
		return $this->perm->getPermissionRecalculationCallbacks();
	}

	public function getScreenLineHeight(): int {
		return $this->lineHeight ?? PHP_INT_MAX;
	}

	public function setScreenLineHeight(?int $height): void {
		if ($height !== null and $height < 1) {
			throw new \InvalidArgumentException("Line height must be at least 1");
		}
		$this->lineHeight = $height;
	}

	public function getSnowflake(): int {
		return $this->snowflake;
	}

	public function sendMessage($message): void {
		if ($this->getSnowflake() == self::BOT_SNOWFLAKE) return;
		if ($message instanceof Translatable) $message = $message->getText();
		$post = new Post($this->getTag() . " | " . TextFormat::clean($message), $this->getName());
		$post->setWebhook(Webhook::getWebhookByName(Core::getInstance()->getNetwork()->getIdentifier()));
		$post->send();
	}

	public function getServer(): Server {
		return Server::getInstance();
	}

	public function getName(): string {
		return "DiscordSender - " . Core::getInstance()->getNetwork()->getIdentifier();
	}

	public function getTag(): string {
		return $this->isBot() ? "BOT" : "<@" . $this->getSnowflake() . ">";
	}

	public function isBot(): bool {
		return $this->getSnowflake() === self::BOT_SNOWFLAKE;
	}
}
