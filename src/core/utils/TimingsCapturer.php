<?php

namespace core\utils;

class TimingsCapturer {

	public array $times = [];
	public array $previousTimes = [];

	public function start(string $identifier) {
		$this->times[$identifier] ??= [
			"start" => microtime(true),
			"end" => null
		];
	}

	public function end(string $identifier) {
		if (!isset($this->times[$identifier])) return;
		$this->times[$identifier]["end"] = microtime(true);
	}

	public function dumpAverages() {
		foreach ($this->previousTimes as $identifier => $times) {
			$avgTime = 0;
			$highestTime = PHP_FLOAT_MIN;
			$lowestTime = PHP_FLOAT_MAX;
			foreach ($times as $_ => $t) {
				$avgTime += ($v = $t['end'] - $t['start']);
				if ($v > $highestTime) $highestTime = $v;
				if ($v < $lowestTime) $lowestTime = $v;
			}
			$avgTime /= count($times);
			var_dump($identifier . " takes " . ($avgTime * 1000) . "ms on average <> Highest: " . (1000 * $highestTime) . "ms | Lowest: " . (1000 * $lowestTime) . "ms");
		}
	}

	public function finish() {
		foreach ($this->times as $identifier => $times) {
			if (is_null($times['end'])) continue;
			$this->previousTimes[$identifier] ??= [];
			$this->previousTimes[$identifier][] = $times;
			unset($this->times[$identifier]);
		}
	}
}
