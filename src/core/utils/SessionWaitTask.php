<?php

namespace core\utils;

use core\AtPlayer;
use core\Core;
use core\network\Network;
use core\session\PlayerSession;
use core\user\User;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\Task;

class SessionWaitTask extends Task {

	protected string $gamertag;
	protected bool $done = false;

	public function __construct(protected User|AtPlayer|PlayerSession $who, protected \Closure $callback, protected int $key) {
		$this->gamertag = $who instanceof User ? $who->getGamertag() : $who->getUser()->getGamertag();
	}

	public function onRun(): void {
		if ($this->done) return;
		$found = false;
		foreach ((Core::getInstance()?->getNetwork()?->getServerManager()?->getServers() ?? []) as $ss) {
			$found = in_array($this->gamertag, $ss->getCluster()->getPlayers()) || $found;
		}
		if (!$found && (!$this->who instanceof PlayerSession || !$this->who->isSaving())) {
			Core::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask($this->callback), 30); // 1.5 second delay to make sure sessions have saved before starting data moves
			$this->done = true;
			AsyncStuff::cancelHandle($this->key);
		}
	}
}
