<?php

namespace core\command\type;

use core\AtPlayer;
use core\Core;
use core\discord\command\DiscordSender;
use core\rank\Rank;
use core\utils\TextFormat;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\lang\Translatable;

abstract class CoreCommand extends Command {

	protected int $hierarchy = 0;
	protected bool $ingameOnly = false;
	protected bool $discordAccessible = true;

	public function __construct(string $name, Translatable|string $description = "", Translatable|string|null $usageMessage = null, array $aliases = []) {
		parent::__construct($name, $description, $usageMessage, $aliases);
		$this->setHierarchy(0);
	}

	public function setHierarchy(int $hierarchy): void {
		$this->hierarchy = $hierarchy;
		$where = strtolower(Core::thisServer()->getType());
		if ($where == "idle") $where = "core";
		switch (true) {
			case $hierarchy >= Rank::HIERARCHY_HEAD_MOD:
				$this->setPermission("$where.tier3");
				break;
			case $hierarchy >= Rank::HIERARCHY_STAFF:
				$this->setPermission("$where.staff");
				break;
			default:
				$this->setPermission("$where.perm");
				break;
		}
	}

	public function getHierarchy(): int {
		return $this->hierarchy;
	}

	public function setInGameOnly(bool $ingameOnly = true): void {
		$this->ingameOnly = $ingameOnly;
	}

	public function inGameOnly(): bool {
		return $this->ingameOnly;
	}

	public function setDiscordAccessible(bool $accessible = true): void {
		$this->discordAccessible = $accessible;
	}

	public function isDiscordAccessible(): bool {
		return $this->discordAccessible;
	}

	public function hasPermission(CommandSender $sender): bool {
		if ($sender instanceof AtPlayer) {
			if (!($sender->getSession()?->getStaff()->isLoaded() ?? false)) return false;
			else return $sender->getRankHierarchy() >= $this->hierarchy;
		}
		if ($sender instanceof DiscordSender) return $this->isDiscordAccessible();
		return !$this->inGameOnly();
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) {
		if (!$this->hasPermission($sender)) {
			$sender->sendMessage(TextFormat::RI . "You do not have permission to run this command.");
			return;
		}
		if ($sender instanceof AtPlayer) {
			$this->handlePlayer($sender, $commandLabel, $args);
			return;
		} elseif ($sender instanceof ConsoleCommandSender) {
			$this->handleConsole($sender, $commandLabel, $args);
			return;
		} elseif ($sender instanceof DiscordSender) {
			$this->handleDiscord($sender, $commandLabel, $args);
			return;
		}
		$this->handle($sender, $commandLabel, $args);
	}

	# To be overridden and used by subclasses #
	public function handle(CommandSender $sender, string $commandLabel, array $args) {
	}
	public function handlePlayer(AtPlayer $sender, string $commandLabel, array $args) {
		$this->handle($sender, $commandLabel, $args);
	}
	public function handleConsole(ConsoleCommandSender $sender, string $commandLabel, array $args) {
		$this->handle($sender, $commandLabel, $args);
	}
	public function handleDiscord(DiscordSender $sender, string $commandLabel, array $args) {
		$this->handle($sender, $commandLabel, $args);
	}
}
