<?php

namespace core\utils;

use Closure;
use Generator;

use pocketmine\scheduler\Task;

final class AsyncIteratorTask extends Task {

	/** @var Generator<mixed> */
	private $generator;

	/** @var int */
	private $entries_per_tick;

	public function __construct(Closure $generator, int $entries_per_tick) {
		$this->generator = (static function () use ($generator): Generator {
			yield true;
			yield from $generator();
		})();
		$this->entries_per_tick = $entries_per_tick;
	}

	public function onRun(): void {
		for ($i = 0; $i < $this->entries_per_tick; ++$i) { // TPS muncher loop, tick entries stack up infinitely and cause performance issues and exponentially extended delays in certain block updates
			if (!$this->generator->send(true) || !$this->generator->valid()) {
				$this->getHandler()->cancel();
				break;
			}
		}
	}
}
