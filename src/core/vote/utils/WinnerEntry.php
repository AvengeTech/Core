<?php

namespace core\vote\utils;

use core\AtPlayer as Player;
use core\Core;
use core\session\mysqli\data\MySqlQuery;
use core\session\stray\StrayRequest;
use core\user\User;

class WinnerEntry {

	const PRIZE_1 = 3;
	const PRIZE_2 = 4;
	const PRIZE_3 = 5;

	const RANK_ORDER = [
		"endermite" => 1,
		"blaze" => 2,
		"ghast" => 3,
		"enderman" => 4,
		"wither" => 5,
		"enderdragon" => 6,
		"warden" => 7,
	];

	public int $month;
	public int $year;

	public function __construct(
		public User $user,

		public int $site,
		int $month,

		public int $prize,
		public bool $claimed = false
	) {
		$this->month = ($month == 0 ? date("n") : (date("n") - 1));
		$this->year = date("Y");
		if ($this->month == 0) {
			$this->month = 12;
			$this->year = $this->year - 1;
		}
	}

	public function getUser(): User {
		return $this->user;
	}

	public function getXuid(): int {
		return $this->getUser()->getXuid();
	}

	public function getSite(): int {
		return $this->site;
	}

	public function getMonth(): int {
		return $this->month;
	}

	public function getYear(): int {
		return $this->year;
	}

	public function getPrize(): int {
		return $this->prize;
	}

	public function getPrizeByPrize(): int {
		switch ($this->getPrize()) {
			case 1:
			default:
				return self::PRIZE_1;
			case 2:
				return self::PRIZE_2;
			case 3:
				return self::PRIZE_3;
		}
	}

	public function hasClaimed(): bool {
		return $this->claimed;
	}

	public function transfer(User $user): void {
		if ($user === $this->getUser()) return;
		$this->user = $user;
		$column = "winner" . $this->getPrize();
		Core::getInstance()->getSessionManager()->sendStrayRequest(new StrayRequest("vote_transfer_winner_" . $user->getXuid(), new MySqlQuery(
			"main",
			"UPDATE vote_month_winners SET " . $column . "=? WHERE site=? AND month=? AND year=?",
			[$user->getXuid(), $this->getSite(), $this->getMonth(), $this->getYear()]
		)), function (StrayRequest $request): void {
		});
	}

	public function claim(Player $player): void {
		$column = "winner" . ($prize = $this->getPrize()) . "claimed";
		Core::getInstance()->getSessionManager()->sendStrayRequest(new StrayRequest("vote_claim_winner_" . $player->getXuid(), new MySqlQuery(
			"main",
			"UPDATE vote_month_winners SET " . $column . "=? WHERE site=? AND month=? AND year=?",
			[1, $this->getSite(), $this->getMonth(), $this->getYear()]
		)), function (StrayRequest $request): void {
		});
		$this->claimed = true;
		if ($player->getRankHierarchy() >= $player->getRankHierarchy("enderdragon")) {
			$player->getSession()->getRank()->addSub(31);
		} else {
			$player->setRank($this->getNewRank($player));
		}
	}

	public function getNewRank(Player $player): string {
		$rank = $player->getRank();
		$prize = $this->getPrizeByPrize();
		if ($rank == "default" || !isset(self::RANK_ORDER[$rank])) {
			switch ($prize) {
				default:
				case self::PRIZE_1:
					return "ghast";
				case self::PRIZE_2:
					return "enderman";
				case self::PRIZE_3:
					return "wither";
			}
		} else {
			$val = min(6, self::RANK_ORDER[$rank] + $prize);
			foreach (self::RANK_ORDER as $rank => $v) {
				if ($v == $val) return $rank;
			}
		}
		return "ghast";
	}

	public function getRankDifference(string $rank): int {
		$rv = 0;
		foreach (self::RANK_ORDER as $r => $id) {
			if ($rank == $r) {
				$rv = $id;
				break;
			}
		}
		return $this->getPrize() - $rv;
	}

	public function getRankName(): string {
		foreach (self::RANK_ORDER as $text => $id) {
			if ($id == $this->getPrizeByPrize()) return $text;
		}
		return "";
	}

	public function verify(\Closure $closure): void {
		$column = "winner" . $this->getPrize();
		$cc = $column . "claimed";
		Core::getInstance()->getSessionManager()->sendStrayRequest(new StrayRequest("vote_transfer_winner_" . $this->getXuid(), new MySqlQuery(
			"main",
			"SELECT " . $column . ", " . $cc . " FROM vote_month_winners WHERE site=? AND month=? AND year=?",
			[$this->getSite(), $this->getMonth(), $this->getYear()]
		)), function (StrayRequest $request) use ($closure, $column, $cc): void {
			$result = $request->getQuery()->getResult()->getRows()[0];
			$xuid = $result[$column];
			$claimed = $result[$cc];
			Core::getInstance()->getUserPool()->useUser($xuid, function (User $user) use ($closure, $claimed): void {
				$this->user = $user;
				$this->claimed = (bool) $claimed;
				$closure($user, (bool) $claimed);
			});
		});
	}
}
