<?php

namespace core\rank;

use core\AtPlayer as Player;
use core\utils\TextFormat;

class Redeemer {

	public $codes = [];

	public function __construct(public Rank $rank) {
	}

	public function getRank(): Rank {
		return $this->rank;
	}

	public function exists(string $code): bool {
		return isset($this->codes[$code]);
	}

	public function redeemed(string $code): bool {
		if (!$this->exists($code)) return false;
		return $this->codes[$code]["redeemed"];
	}

	public function addCode(string $code, string $prize): bool {
		if ($this->exists($code)) return false;

		$this->codes[$code] = [
			"redeemed" => false,
			"redeemedby" => "",
			"prize" => $prize,
		];
		return true;
	}

	public function getRedeemedBy(string $code): string {
		if (!$this->exists($code)) return "";
		if (!$this->redeemed($code)) return "";
		return $this->codes[$code]["redeemedby"];
	}

	public function getPrize(string $code): ?string {
		if (!$this->exists($code)) return "";
		return $this->codes[$code]["prize"];
	}

	public function redeemCode(string $code, Player $player): bool {
		if (!$this->exists($code)) return false;
		if ($this->redeemed($code)) return false;

		$prize = explode(":", $this->getPrize($code));
		switch ($prize[0]) {
			case "rank":
				$player->setRank($prize[1]);
				$player->sendMessage(TextFormat::GI . "Redeemed code " . TextFormat::AQUA . $code . TextFormat::GRAY . " and received " . TextFormat::YELLOW . $prize[1] . " rank!");
				break;
			case "techits":
				$player->addTechits($prize[1]);
				$player->sendMessage(TextFormat::GI . "Redeemed code " . TextFormat::AQUA . $code . TextFormat::GRAY . " and received " . TextFormat::AQUA . $prize[1] . " techits!");
				break;
		}

		foreach ($this->getRank()->plugin->getServer()->getOnlinePlayers() as $pl) {
			$pl->sendMessage(TextFormat::PI . TextFormat::YELLOW . $player->getName() . TextFormat::GRAY . " has redeemed code " . TextFormat::YELLOW . "'" . $code . "'" . TextFormat::GRAY . " and claimed " . TextFormat::GREEN . $prize[0] . " " . $prize[1] . "!");
		}

		$this->codes[$code]["redeemed"] = true;
		$this->codes[$code]["redeemedby"] = $player->getName();

		return true;
	}
}
