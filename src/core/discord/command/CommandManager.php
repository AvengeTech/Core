<?php

namespace core\discord\command;

use pmmp\thread\ThreadSafeArray;

use core\Core;
use core\session\mysqli\data\MySqlQuery;
use core\session\stray\StrayRequest;

class CommandManager {

	public DiscordSender $sender;

	public bool $fetching = false;
	public array $commands = [];

	public function __construct() {
		$sender = new DiscordSender();
		foreach ([
			"core.staff", "prison.staff", "skyblock.staff",
			"core.tier3", "prison.tier3", "skyblock.tier3",
		] as $permission) {
			$sender->addAttachment(Core::getInstance(), $permission, true);
		}
		$this->sender = $sender;
	}

	public function tick(): void {
		if (!$this->isFetching())
			$this->fetchCommands();
		if (!empty($this->getCommands()))
			$this->processCommands();
	}

	public function isFetching(): bool {
		return $this->fetching;
	}

	public function setFetching(bool $fetching = true): void {
		$this->fetching = $fetching;
	}

	public function getSender(int $snowflake = DiscordSender::BOT_SNOWFLAKE): DiscordSender {
		$sender = $this->sender;
		$sender->snowflake = $snowflake;
		return $sender;
	}

	public function getCommands(): array {
		return $this->commands;
	}

	public function fetchCommands(): void {
		$this->setFetching();
		$identifier = Core::getInstance()->getNetwork()->getThisServer()->getIdentifier();
		Core::getInstance()->getSessionManager()->sendStrayRequest(new StrayRequest(
			"discord_fetch_commands",
			new MySqlQuery("main", "SELECT * FROM discord_commands WHERE identifier=?", [$identifier]),
		), function (StrayRequest $request) use ($identifier): void {
			$rows = (array) $request->getQuery()->getResult()->getRows();
			$commands = [];
			foreach ($rows as $row) {
				$commands[] = new CommandEntry($row["snowflake"], $row["command"]);
			}
			$this->commands = array_merge($commands, $this->getCommands());

			if (($count = count($commands)) > 0) {
				Core::getInstance()->getSessionManager()->sendStrayRequest(new StrayRequest(
					"discord_delete_fetched_commands",
					new MySqlQuery("main", "DELETE FROM discord_commands WHERE identifier=?", [$identifier])
				), function (StrayRequest $request) use ($count): void {
					$this->setFetching(false);
				});
			} else {
				$this->setFetching(false);
			}
		});
	}

	public function returnCommands(array $commands): void {
		$this->setFetching(false);
		$this->commands = array_merge($commands, $this->getCommands());
	}

	public function processCommands(): bool {
		$commands = $this->getCommands();
		if (empty($commands)) return false;
		$this->commands = [];
		foreach ($commands as $command) {
			$command->execute();
		}
		return true;
	}

	public function forceCommandsTo($command, ThreadSafeArray|array|string $identifiers, int $snowflake = DiscordSender::BOT_SNOWFLAKE, bool $async = true): void {
		if (!is_array($command)) {
			$command = [$command];
		}
		if ($async) {
			$request = new StrayRequest("discord_force_" . $snowflake, []);
			foreach ($command as $cmd) {
				if (is_string($identifiers)) {
					$request->addQuery(new MySqlQuery("cmd_" . $cmd . "_" . $snowflake . "_" . $identifiers, "INSERT INTO discord_commands(snowflake, identifier, command) VALUES(?, ?, ?)", [$snowflake, $identifiers, $cmd]));
				} else {
					foreach ($identifiers as $identifier) {
						$request->addQuery(new MySqlQuery("cmd_" . $cmd . "_" . $snowflake . "_" . $identifier, "INSERT INTO discord_commands(snowflake, identifier, command) VALUES(?, ?, ?)", [$snowflake, $identifier, $cmd]));
					}
				}
			}
			Core::getInstance()->getSessionManager()->sendStrayRequest($request, function (StrayRequest $request) use ($command): void {
				echo count($command) . " commands inserted into mysql", PHP_EOL;
			});
		} else {
		}
	}
}
