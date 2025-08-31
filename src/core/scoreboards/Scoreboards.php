<?php

namespace core\scoreboards;

use core\{
	Core,
	AtPlayer as Player
};
use core\scoreboards\commands\ToggleCommand;
use core\utils\TextFormat;
use core\vote\Vote;

class Scoreboards {

	const TYPE_ANNOUNCEMENT = 0;
	const TYPE_UPTIME = 1;
	const TYPE_PLAYER_COUNT = 2;
	const TYPE_VOTE_PARTY = 3;
	const TYPE_ALL = 4;

	const MODE_SPACER = 0;
	const MODE_ANNOUNCE = 1;

	const CHAR_CUTOFF = 17;

	public array $announcements;
	public int $announcement = 0;
	public int $progress = 0;
	public int $mode = self::MODE_SPACER;

	public array $lineCache = ScoreboardObject::DEFAULT_LINES;

	public array $scoreboards = [];
	public int $ticks = 0;

	public bool $pch = true;

	public bool $lobby = false;

	public function __construct(public Core $plugin) {
		$this->announcements = array_merge(DisplayData::ANNOUNCEMENTS[strtolower(Core::getInstance()->getNetwork()->getServerType())] ?? [], DisplayData::ANNOUNCEMENTS["all"]);
		$this->lineCache[0] = TextFormat::ICON_AVENGETECH . TextFormat::YELLOW . " Playing: " . TextFormat::AQUA . Core::thisServer()->getType();
		$this->updateLineCache(self::TYPE_ALL);

		$this->lobby = Core::thisServer()->getType() === "lobby";

		$this->plugin->getServer()->getCommandMap()->register("togglescoreboard", new ToggleCommand($plugin, "togglescoreboard", "Toggle the Scoreboard!"));
	}

	public function tick(): void {
		$this->ticks++;

		if ($this->ticks % 20 == 0){
			$this->updateLineCache(self::TYPE_UPTIME);
			$this->updateLineCache(self::TYPE_VOTE_PARTY);
		}
		if ($this->ticks % 100 == 0) $this->updateLineCache(self::TYPE_PLAYER_COUNT);

		if ($this->ticks % 5 == 0) {
			$this->updateLineCache(self::TYPE_ANNOUNCEMENT);

			$lineCache = $this->getLineCache();
			foreach ($this->getScoreboards() as $xuid => $scoreboard) {
				if ($scoreboard->getPlayer()->isConnected()) $scoreboard->update($lineCache);
			}
		}
	}

	public function getAnnouncements(): array {
		return $this->announcements;
	}

	public function getAnnouncementKey(): int {
		return $this->announcement;
	}

	public function getProgress(): int {
		return $this->progress;
	}

	public function getMode(): int {
		return $this->mode;
	}

	public function setMode(int $mode): void {
		$this->mode = $mode;
	}

	public function getLineCache(): array {
		return $this->lineCache;
	}

	public function updateLineCache(int $type): void {
		$network = Core::getInstance()->getNetwork();
		switch ($type) {
			case self::TYPE_ANNOUNCEMENT:
				if ($this->getMode() == self::MODE_SPACER) {
					$this->lineCache[10] = str_repeat(" ", self::CHAR_CUTOFF);
					$this->progress--;
					if ($this->progress <= 0) {
						$this->announcement++;
						if ($this->announcement >= count($this->announcements)) {
							$this->announcement = 0;
						}
						$this->progress = 0;
						$this->setMode(self::MODE_ANNOUNCE);
					}
				} else {
					$text = $this->announcements[$this->announcement] . "     ";

					if ($this->progress >= strlen($text) + self::CHAR_CUTOFF) {
						$this->setMode(self::MODE_SPACER);
						$this->progress = 5;
					} else {
						$ntext = TextFormat::GRAY . substr($text, $this->progress, self::CHAR_CUTOFF);
						if (strlen($ntext) > strlen(trim($ntext))) {
							$diff = strlen($ntext) - strlen(trim($ntext));
							if ($diff > 5) {
								$this->mode = self::MODE_SPACER;
								$this->progress = 5;
							}
							$ntext = TextFormat::GRAY . substr($text, $this->progress - $diff, self::CHAR_CUTOFF);
						}
						$ntext = $ntext . str_repeat(" ", max(0, (self::CHAR_CUTOFF + 3) - strlen($ntext)));
						$this->lineCache[10] = $ntext;
					}
					$this->progress++;
				}
				break;

			case self::TYPE_UPTIME:
				$seconds = $network->getUptime();
				$hours = floor($seconds / 3600);
				$minutes = floor(((int) ($seconds / 60)) % 60);
				$seconds = $seconds % 60;
				if (strlen((string) $hours) == 1) $hours = "0" . $hours;
				if (strlen((string) $minutes) == 1) $minutes = "0" . $minutes;
				if (strlen((string) $seconds) == 1) $seconds = "0" . $seconds;
				$left = $network->getRestartTime() - time();
				$this->lineCache[3] = TextFormat::YELLOW . "Uptime: " . TextFormat::RED . $hours . TextFormat::GRAY . ":" . TextFormat::RED . $minutes . TextFormat::GRAY . ":" . TextFormat::RED . $seconds . " " . ($seconds % 3 == 0 ? TextFormat::EMOJI_HOURGLASS_EMPTY : TextFormat::EMOJI_HOURGLASS_FULL) . " " . ($left <= 60 ? ($seconds % 2 == 0 ? TextFormat::EMOJI_CAUTION : "") : "");
				$this->lineCache[4] = "         ";
				break;
			case self::TYPE_VOTE_PARTY:
				$vote = Core::getInstance()->getVote();
				$count = $vote->getVoteCount();
				$required = $vote->getRequiredVotes();
				$status = $vote->getPartyStatus();
				$timer = $vote->getPartyTimerLeft();

				$this->lineCache[6] = TextFormat::AQUA . "Vote Party: " . TextFormat::YELLOW . $count . TextFormat::GRAY . "/" . TextFormat::GREEN . $required;
				$this->lineCache[7] = match($status){
					default => TextFormat::GRAY . "Type " . TextFormat::YELLOW . "/vote",
					Vote::STATUS_COUNTDOWN => TextFormat::YELLOW . "Starting in " . TextFormat::GOLD . $timer . TextFormat::GRAY . " (" . TextFormat::AQUA . "/vp" . TextFormat::GRAY . ")",
					Vote::STATUS_START => TextFormat::YELLOW . "Started! " . TextFormat::GOLD . $timer . TextFormat::GRAY . " (" . TextFormat::AQUA . "/vp" . TextFormat::GRAY . ")",
				};
				break;
			case self::TYPE_PLAYER_COUNT:
				$manager = $network->getServerManager();
				$global = $manager->getTotalPlayers();
				$gamemode = $manager->getPlayerCountByType($network->getServerType());
				$this->lineCache[2] = ($this->pch ? 
					TextFormat::YELLOW . "Total Players: " . TextFormat::AQUA . $global :
					TextFormat::YELLOW . "Players Here: " . TextFormat::AQUA . $gamemode
				);
				$this->pch = !$this->pch;
				break;
			case self::TYPE_ALL:
				$this->updateLineCache(self::TYPE_ANNOUNCEMENT);
				$this->updateLineCache(self::TYPE_UPTIME);
				$this->updateLineCache(self::TYPE_PLAYER_COUNT);
				$this->updateLineCache(self::TYPE_VOTE_PARTY);
				break;
		}
		if($this->lobby){
			unset($this->lineCache[4], $this->lineCache[5], $this->lineCache[6], $this->lineCache[7], $this->lineCache[8]);
		}
	}

	public function getScoreboards(): array {
		return $this->scoreboards;
	}

	public function addScoreboard(Player $player, bool $send = true): void {
		$sb = $this->scoreboards[$player->getXuid()] = new ScoreboardObject($player);
		if ($send) $sb->send();
	}

	public function getPlayerScoreboard(Player $player): ?ScoreboardObject {
		return $this->scoreboards[$player->getXuid()] ?? null;
	}

	public function removeScoreboard(Player $player, bool $send = false): void {
		if (($sb = $this->getPlayerScoreboard($player)) !== null) {
			unset($this->scoreboards[$player->getXuid()]);
			if ($send) $sb->remove();
		}
	}

	public function onJoin(Player $player): void {
		$this->addScoreboard($player);
	}

	public function onQuit(Player $player): void {
		$this->removeScoreboard($player);
	}
}