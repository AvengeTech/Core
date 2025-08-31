<?php

namespace core\vote;

use core\AtPlayer as Player;
use core\Core;
use core\discord\objects\{
	Post,
	Embed,
	Field,
	Footer,
	Webhook
};
use core\session\component\{
	ComponentRequest,
	SaveableComponent
};
use core\session\mysqli\data\MySqlQuery;
use core\vote\prize\PrizeCluster;

class VoteComponent extends SaveableComponent {

	public int $lastVoted = 0;

	public int $monthlyStreak = 0;
	public int $totalStreak = 0;
	public int $highestStreak = 0;

	public function getName(): string {
		return "vote";
	}

	public function getLastVoted(): int {
		return $this->lastVoted;
	}

	public function pastVoteTime(): bool {
		//return false; //temp
		return (date("m") !== date("m", $this->getLastVoted()) &&
			((int) date("j")) !== 1
		) || date("j") > ((int) date("j", $this->getLastVoted())) + 1;
		//return abs(date("j", time()) - ((int) date("j", $this->getLastVoted()))) > 1;
	}

	public function getMonthlyStreak(): int {
		return $this->monthlyStreak;
	}

	public function getTotalStreak(): int {
		return $this->totalStreak;
	}

	public function getHighestStreak(): int {
		return $this->highestStreak;
	}

	public function resetStreak(): void {
		$this->monthlyStreak = 0;
		if ($this->getTotalStreak() > $this->getHighestStreak()) {
			$this->highestStreak = $this->getTotalStreak();
		}
		$this->totalStreak = 0;
		$this->setChanged();
	}

	public function addVote(bool $saveAsync = true): ?PrizeCluster {
		$expired = false;
		if (!$this->pastVoteTime()) {
			$this->monthlyStreak++;
			$this->totalStreak++;
			if ($this->getTotalStreak() > $this->getHighestStreak()) {
				$this->highestStreak = $this->getTotalStreak();
			}
		} else {
			$this->resetStreak();
			$this->monthlyStreak++;
			$this->totalStreak++;
			$expired = true;
		}

		$player = $this->getPlayer();

		switch (Structure::getDayType()) {
			case Structure::DAY_WEEKEND:
				$amt = 2;
				break;
			case Structure::DAY_FIRST:
				$amt = 4;
				break;
			case Structure::DAY_HOLIDAY:
				$amt = 3;
				break;
			case Structure::DAY_NORMAL:
				$amt = 1;
				break;
		}
		while ($amt > 0) {
			Core::getInstance()->getVote()->getPrizeVendor()->getDailyPrize()?->reward($player);
			$amt--;
		}

		$streak = $this->getMonthlyStreak();
		if ($streak == $this->getLastDayOfMonth()) {
			$prize = Core::getInstance()->getVote()->getPrizeVendor()->getLastDayPrize();
			$this->monthlyStreak = 0;
		} else {
			$prize = Core::getInstance()->getVote()->getPrizeVendor()->getPrizeFor($streak);
		}

		if ($this->monthlyStreak > date("d")) $this->monthlyStreak = date("d");
		if ($this->isLastOfMonth()) $this->monthlyStreak = 0;

		$this->lastVoted = time();

		$prize?->reward($player);
		$this->setChanged();

		$post = new Post("", "Vote Log", "[REDACTED]", false, "", [
			new Embed("", "rich", "**" . $player->getName() . "** claimed a vote reward!", "", "ffb106", new Footer("toast | " . date("F j, Y, g:ia", time()) . " EST"), "", "[REDACTED]", null, [
				new Field("Expired", $expired ? "YES" : "NO", true),
				new Field("Monthly", $this->getMonthlyStreak(), true),
				new Field("Total", $this->getTotalStreak(), true),
				new Field("Highest", $this->getHighestStreak(), true),
			])
		]);
		$post->setWebhook(Webhook::getWebhookByName("vote-log"));
		$post->send();

		$saveAsync ? $this->saveAsync() : $this->save();
		return $prize;
	}

	public function setVote(int $day = 0, int $total = 0, int $highest = 0): void {
		$this->lastVoted = time();
		$this->monthlyStreak = $day;
		$this->totalStreak = ($total == 0 ? $day : $total);
		if ($this->totalStreak > $this->highestStreak) {
			$this->highestStreak = $this->totalStreak;
		}
		if ($highest != 0) {
			$this->highestStreak = $highest;
		}
		$this->setChanged();
	}

	public function isLastOfMonth(): bool {
		return date("d") == $this->getLastDayOfMonth();
	}

	public function getLastDayOfMonth(): int {
		return date("t");
	}

	public function createTables(): void {
		$db = $this->getSession()->getSessionManager()->getDatabase();
		foreach ([
			"CREATE TABLE IF NOT EXISTS vote_streak(xuid BIGINT(16) NOT NULL PRIMARY KEY, lastvoted INT NOT NULL DEFAULT 0, monthly INT NOT NULL DEFAULT 0, total INT NOT NULL DEFAULT 0, highest INT NOT NULL DEFAULT 0)",
			"CREATE TABLE IF NOT EXISTS vote_month_winners(
				site INT NOT NULL,
				month INT NOT NULL, year INT NOT NULL,
				winner1 BIGINT(16) NOT NULL, winner1claimed TINYINT(1) NOT NULL DEFAULT '0',
				winner2 BIGINT(16) NOT NULL, winner2claimed TINYINT(1) NOT NULL DEFAULT '0',
				winner3 BIGINT(16) NOT NULL, winner3claimed TINYINT(1) NOT NULL DEFAULT '0',
				PRIMARY KEY(site, month, year)
			)"
		] as $query) $db->query($query);
	}

	public function loadAsync(): void {
		$request = new ComponentRequest($this->getXuid(), $this->getName(), new MySqlQuery("main", "SELECT * FROM vote_streak WHERE xuid=?", [$this->getXuid()]));
		$this->newRequest($request, ComponentRequest::TYPE_LOAD);
		parent::loadAsync();
	}

	public function finishLoadAsync(?ComponentRequest $request = null): void {
		$result = $request->getQuery()->getResult();
		$rows = (array) $result->getRows();
		if (count($rows) > 0) {
			$data = array_shift($rows);
			$this->lastVoted = $data["lastvoted"];
			$this->monthlyStreak = $data["monthly"];
			$this->totalStreak = $data["total"];
			$this->highestStreak = $data["highest"];
		}
		parent::finishLoadAsync($request);
	}

	public function verifyChange(): bool {
		$player = $this->getPlayer();
		$verify = $this->getChangeVerify();
		return
			$this->getLastVoted() !== $verify["lastVoted"] ||
			$this->getMonthlyStreak() !== $verify["monthly"] ||
			$this->getTotalStreak() !== $verify["total"] ||
			$this->getHighestStreak() !== $verify["highest"];
	}

	public function saveAsync(): void {
		if (!$this->hasChanged() || !$this->isLoaded()) return;

		$this->setChangeVerify([
			"lastVoted" => $this->getLastVoted(),
			"monthly" => $this->getMonthlyStreak(),
			"total" => $this->getTotalStreak(),
			"highest" => $this->getHighestStreak(),
		]);

		$player = $this->getPlayer();
		$uuid = $player instanceof Player ? $player->getUniqueId()->toString() : "dddd";
		$request = new ComponentRequest($this->getXuid(), $this->getName(), new MySqlQuery(
			"main",
			"INSERT INTO vote_streak(xuid, lastvoted, monthly, total, highest) VALUES(?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE lastvoted=VALUES(lastvoted), monthly=VALUES(monthly), total=VALUES(total), highest=VALUES(highest)",
			[$this->getXuid(), $this->getLastVoted(), $this->getMonthlyStreak(), $this->getTotalStreak(), $this->getHighestStreak()]
		));
		$this->newRequest($request, ComponentRequest::TYPE_SAVE);
		parent::saveAsync();
	}

	public function save(): bool {
		if (!$this->hasChanged() || !$this->isLoaded()) return false;

		$xuid = $this->getXuid();
		$last = $this->getLastVoted();
		$monthly = $this->getMonthlyStreak();
		$total = $this->getTotalStreak();
		$highest = $this->getHighestStreak();

		$db = $this->getSession()->getSessionManager()->getDatabase();
		$stmt = $db->prepare("INSERT INTO vote_streak(xuid, lastvoted, monthly, total, highest) VALUES(?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE lastvoted=VALUES(lastvoted), monthly=VALUES(monthly), total=VALUES(total), highest=VALUES(highest)");
		$stmt->bind_param("iiiii", $xuid, $last, $monthly, $total, $highest);
		$stmt->execute();
		$stmt->close();

		return parent::save();
	}

	public function getSerializedData(): array {
		return [
			"lastVoted" => $this->getLastVoted(),
			"monthly" => $this->getMonthlyStreak(),
			"total" => $this->getTotalStreak(),
			"highest" => $this->getHighestStreak(),
		];
	}

	public function applySerializedData(array $data): void {
		$this->lastVoted = $data["lastVoted"];
		$this->monthlyStreak = $data["monthly"];
		$this->totalStreak = $data["total"];
		$this->highestStreak = $data["highest"];
	}
}
