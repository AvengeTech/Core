<?php

namespace core\utils;

/**
 * This class simplifies creation of asynchronous (un-threaded)
 * tasks.
 * Example:
 *    $array = []; // a very big array
 *    AsyncIterator::iterate(function() use($array) : Generator{
 *        foreach($array as $key => $value){
 *            // do something with $key, $value on main thread
 *            yield true;
 *        }
 *        yield false; // quit async task
 *    }, 5);    // foreach loop is broken to process 5 entries per tick
 *            // or 5 "yield true;"s per tick.
 */

use Closure;

use core\Core;
use pocketmine\scheduler\TaskScheduler;

final class AsyncIterator {

	/** @var TaskScheduler */
	private static $scheduler;

	public static function init(Core $plugin): void {
		self::$scheduler = $plugin->getScheduler();
	}

	public static function iterate(Closure $generator, int $entries_per_tick = 10, int $sleep_time = 1): void {
		self::$scheduler->scheduleRepeatingTask($task = new AsyncIteratorTask($generator, $entries_per_tick), $sleep_time);
	}
}
