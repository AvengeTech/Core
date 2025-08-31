<?php

namespace core\vote\sites;

use pocketmine\Server;

use core\Core;
use core\AtPlayer as Player;
use core\session\mysqli\data\MySqlQuery;
use core\session\stray\StrayRequest;
use core\utils\TextFormat;
use core\vote\Structure;
use core\vote\tasks\VotePageQuery;
use core\vote\uis\VoteSuccessUi;
use core\vote\utils\WinnerEntry;

abstract class VoteSite {

	const PAGE_COUNT = 10;

	const MONTH_CURRENT = 0;
	const MONTH_PREVIOUS = 1;

	public array $requests = [];

	public array $checks = [];

	public array $top = [
		self::MONTH_CURRENT => [],
		self::MONTH_PREVIOUS => []
	];

	public array $winCache = [];

	public function __construct(
		public int $id,

		public string $apikey,
		public string $checklink,
		public string $claimlink
	) {
	}

	public function getId(): int {
		return $this->id;
	}

	public function getApiKey(): string {
		return $this->apikey;
	}

	public function getCheckLinkData(): string {
		return $this->checklink;
	}

	public function getClaimLinkData(): string {
		return $this->claimlink;
	}

	public function giveVotePrizes(Player $player): void {
		$session = $player->getSession()->getVote();

		Core::getInstance()->getVote()->addVote($player);

		$this->claim($player);

		$extra = "";
		switch (Structure::getDayType()) {
			case Structure::DAY_WEEKEND:
				$extra = " " . TextFormat::YELLOW . "(It's double vote weekend! All vote prizes are DOUBLED!)";
				break;
			case Structure::DAY_FIRST:
				$extra = " " . TextFormat::YELLOW . "(It's the first of the month! All vote prizes are QUADRUPLED!!)";
				break;
			case Structure::DAY_HOLIDAY:
				$h = Structure::getHoliday();
				$extra = " " . TextFormat::YELLOW . "(It's " . $h . ", so all vote prizes are TRIPLED!!)";
				break;
			case Structure::DAY_NORMAL:
				break;
		}
		$msg = TextFormat::YI . TextFormat::LIGHT_PURPLE . $player->getName() . " voted at " . TextFormat::AQUA . "avengetech.net/vote" . TextFormat::LIGHT_PURPLE . " and got a ton of prizes! Type /vote to see how YOU can get a ton of prizes for FREE too!" . $extra;
		/**foreach (Server::getInstance()->getOnlinePlayers() as $p) {
			$p->sendMessage($msg);
		}*/
		Core::announceToSS($msg);

		$prize = $session->addVote();
		$player->showModal(new VoteSuccessUi($player, $prize));
	}

	public function getCheckLink(Player $player) {
		return str_replace(
			["{apikey}", "{player}"],
			[$this->getApiKey(), str_replace(" ", "%20", $player->getName())],
			$this->getCheckLinkData()
		);
	}

	public function getClaimLink(Player $player) {
		return str_replace(
			["{apikey}", "{player}"],
			[$this->getApiKey(), str_replace(" ", "%20", $player->getName())],
			$this->getClaimLinkData()
		);
	}

	public function hasVoteCheck(Player $player) {
		return isset($this->checks[$player->getName()]);
	}

	public function sendVoteCheck(Player $player) {
		$this->checks[$player->getName()] = true;
		$pool = Core::getInstance()?->getAsyncPool() ?? Server::getInstance()->getAsyncPool();
		$pool->submitTask(new VotePageQuery($this->getCheckLink($player), $player->getName(), true, $this->getId()));
	}

	public function claim(Player $player) {
		$this->checks[$player->getName()] = true;
		$pool = Core::getInstance()?->getAsyncPool() ?? Server::getInstance()->getAsyncPool();
		$pool->submitTask(new VotePageQuery($this->getClaimLink($player), $player->getName(), false, $this->getId()));
	}

	public function updateTop(int $month = self::MONTH_CURRENT): void {
	}

	public function updateReturn(string $data, int $month = self::MONTH_CURRENT): void {
	}

	public function getTopCache(int $month = self::MONTH_CURRENT, int $page = 1): array {
		$cache = array_chunk($this->top[$month], self::PAGE_COUNT, true);
		return $cache[$page - 1] ?? [];
	}

	public function getTotalTopCachePages(int $month = self::MONTH_CURRENT): int {
		return count(array_chunk(($this->top[$month] ?? []), self::PAGE_COUNT));
	}

	public function hasNextTopCachePage(int $month = self::MONTH_CURRENT, int $page = 1): bool {
		return !empty($this->getTopCache($month, $page + 1));
	}

	public function getWinners(int $month = self::MONTH_PREVIOUS, bool $force = false, ?\Closure $closure = null): void {
		if (isset($this->winCache[$month]) && !$force) {
			if ($closure !== null) $closure($this->winCache[$month]);
			return;
		}

		$site = $this->getId();
		$mm = ($month == self::MONTH_CURRENT ? date("n") : (date("n") - 1));
		$year = date("Y");
		if ($mm == 0) {
			$mm = 12;
			$year = $year - 1;
		}

		Core::getInstance()->getSessionManager()->sendStrayRequest(new StrayRequest("vote_get_winners_" . $site . "_" . $mm, new MySqlQuery(
			"main",
			"SELECT * FROM vote_month_winners WHERE site=? AND month=? AND year=?",
			[$site, $mm, $year]
		)), function (StrayRequest $request) use ($month, $closure): void {
			$result = $request->getQuery()->getResult()->getRows()[0] ?? [];
			if (count($result) == 0) {
				if (date("j") == 1 && $month == self::MONTH_PREVIOUS) {
					$this->pickWinners($month, function () use ($month, $closure): void {
						$this->getWinners($month, false, $closure);
					});
				}
			} else {
				$winner1 = $result["winner1"];
				$winner2 = $result["winner2"];
				$winner3 = $result["winner3"];
				Core::getInstance()->getUserPool()->useUsers([$winner1, $winner2, $winner3], function (array $users) use ($month, $closure, $result, $winner1, $winner2, $winner3): void {
					$entries = $this->winCache[$month] = [
						new WinnerEntry($users[$winner3], $this->getId(), $month, 3, (bool) $result["winner3claimed"]),
						new WinnerEntry($users[$winner2], $this->getId(), $month, 2, (bool) $result["winner2claimed"]),
						new WinnerEntry($users[$winner1], $this->getId(), $month, 1, (bool) $result["winner1claimed"]),
					];
					if ($closure !== null) $closure($entries);
				});
			}
		});
	}

	public function pickWinners(int $month = self::MONTH_PREVIOUS, ?\Closure $closure = null): void {
		$top = array_keys($this->getTopCache($month));
		if(count($top) === 0) return;
		
		$winners = [];
		while (count($winners) < 3) {
			$sel = $top[array_rand($top)];
			if (in_array($sel, $winners))
				continue;
			$winners[] = $sel;
		}

		$site = $this->getId();
		$mm = ($month == self::MONTH_CURRENT ? date("n") : (date("n") - 1));
		$year = date("Y");
		if ($mm == 0) {
			$mm = 12;
			$year = $year - 1;
		}

		Core::getInstance()->getUserPool()->useUsers($winners, function (array $users) use ($month, $closure, $mm, $year): void {
			$winner1 = array_shift($users);
			$winner2 = array_shift($users);
			$winner3 = array_shift($users);

			Core::getInstance()->getSessionManager()->sendStrayRequest(new StrayRequest("vote_set_winners_" . $this->getId() . "_" . $mm, new MySqlQuery(
				"main",
				"INSERT INTO vote_month_winners(
					site, month, year,
					winner1, winner1claimed,
					winner2, winner2claimed,
					winner3, winner3claimed
				) VALUES(
					?, ?, ?,
					?, ?,
					?, ?,
					?, ?
				)",
				[
					$this->getId(), $mm, $year,
					$winner1->getXuid(), 0,
					$winner2->getXuid(), 0,
					$winner3->getXuid(), 0
				]
			)), function (StrayRequest $request) use ($month, $closure, $winner1, $winner2, $winner3): void {
				$this->winCache[$month] = [
					new WinnerEntry($winner3, $this->getId(), $month, 3, false),
					new WinnerEntry($winner2, $this->getId(), $month, 2, false),
					new WinnerEntry($winner1, $this->getId(), $month, 1, false),
				];
				if ($closure !== null) $closure();
			});
		});
	}

	public abstract function returnVoteCheck(Player $player, $data);
}
