<?php

namespace core\utils;

use pocketmine\Server;
use pocketmine\console\ConsoleCommandSender;

use core\session\component\{
	ComponentRequest,
	SaveableComponent
};
use core\session\mysqli\data\{
	MySqlQuery,
	MySqlRequest
};

class LoginCommandsComponent extends SaveableComponent {

	public array $commands = [];

	public function getName(): string {
		return "login_commands";
	}

	public function getCommands(): array {
		return $this->commands;
	}

	public function executeCommands(): void {
		$player = $this->getPlayer();
		if ($player === null) return;
		if (count($this->getCommands()) === 0) return;

		$sender = new ConsoleCommandSender(($server = Server::getInstance()), $server->getLanguage());
		foreach ($this->getCommands() as $command) {
			$server->dispatchCommand($sender, str_replace("{player}", '"' . $player->getName() . '"', $command));
		}
		$this->commands = [];

		$this->deleteCommands();
	}

	public function deleteCommands(): void {
		$this->getSession()->getSessionManager()->sendStrayRequest(
			new MySqlRequest(
				"delete_login_commands_" . $this->getXuid(),
				new MySqlQuery("main", "DELETE FROM login_commands WHERE xuid=?", [$this->getXuid()])
			),
			function (MySqlRequest $request): void {
			}
		);
	}

	public function createTables(): void {
		$db = $this->getSession()->getSessionManager()->getDatabase();
		foreach ([
			"CREATE TABLE IF NOT EXISTS login_commands(xuid BIGINT(16) NOT NULL, command VARCHAR(255) NOT NULL);",
		] as $query) $db->query($query);
	}

	public function loadAsync(): void {
		$request = new ComponentRequest($this->getXuid(), $this->getName(), new MySqlQuery("main", "SELECT * FROM login_commands WHERE xuid=?", [$this->getXuid()]));
		$this->newRequest($request, ComponentRequest::TYPE_LOAD);
		parent::loadAsync();
	}

	public function finishLoadAsync(?ComponentRequest $request = null): void {
		$result = $request->getQuery()->getResult();
		$rows = (array) $result->getRows();
		if (count($rows) > 0) {
			foreach ($rows as $row) {
				$this->commands[] = $row["command"];
			}
		}

		parent::finishLoadAsync($request);
	}

	public function saveAsync(): void {
	}

	public function save(): bool {
		return parent::save();
	}

	public function getSerializedData(): array {
		return [
			"command" => $this->commands
		];
	}

	public function applySerializedData(array $data): void {
		$this->commands = $data["command"];
	}
}
