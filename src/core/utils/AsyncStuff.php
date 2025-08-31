<?php

namespace core\utils;

use core\AtPlayer;
use core\Core;
use core\session\PlayerSession;
use core\user\User;
use pocketmine\scheduler\AsyncTask;
use pocketmine\scheduler\TaskHandler;
use Task;

class AsyncStuff {

	/** @var TaskHandler[] */
	public static array $handlers = [];

	public static function waitUntilOffline(User|AtPlayer|PlayerSession $who, \Closure $callable): void {
		$t = (int)hrtime(true);
		self::$handlers[$t] = Core::getInstance()->getScheduler()->scheduleRepeatingTask(new SessionWaitTask($who, $callable, $t), 5);
	}

	public static function cancelHandle(int $key): void {
		if (!isset(self::$handlers[$key])) return;
		self::$handlers[$key]->cancel();
		unset(self::$handlers[$key]);
	}
}
