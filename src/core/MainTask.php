<?php

namespace core;

use pocketmine\scheduler\Task;

use core\Core;
use core\utils\TextFormat;

use core\network\Structure as NetworkStructure;
use core\utils\Utils;

class MainTask extends Task {

	public int $runs = 0;

	public function __construct(public Core $plugin) {
	}

	public function onRun(): void {
		$this->runs++;

		if ($this->runs == 100) {
			$this->plugin->getVote()->getVoteSite(1)->getWinners(1, true, function (array $winners): void {
			});
		}

		$this->plugin->getScoreboards()->tick();
		$this->plugin->getTutorials()->tick();
		$this->plugin->getNetwork()->tick();

		if ($this->runs % 5 == 0) {
			$this->plugin->getSessionManager()?->tick();
			$this->plugin->getUserPool()?->tick();
			$this->plugin->getGptQueue()?->tick();
		}
		$this->plugin->getAsyncPool()?->collectTasks();

		if ($this->runs % 10 == 0) {
			foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
				/** @var AtPlayer $player */
				if ($player->hasPlayerInfoLoaded() && ($closure = $player->whenInfoLoaded()) !== null) {
					$closure($player);
					$player->setWhenInfoLoaded();
				}
				if ($player->hasSessionSaved() && ($closure = $player->whenSessionSaved()) !== null) {
					$closure($player);
					$player->setWhenSessionSaved();
				}
				if ($player->hasGameSessionSaved() && ($closure = $player->whenGameSessionSaved()) !== null) {
					$closure($player);
					$player->setWhenGameSessionSaved();
				}
				if ($player->hasPreLoadAction()) {
					foreach ($player->getPreLoadActions() as $action) {
						$action->process(true);
					}
					$player->preLoadActions = [];
				}
				if ($player->hasLoadAction() && $player->isLoaded()) {
					foreach ($player->getLoadActions() as $action) {
						$action->process();
					}
					$player->loadActions = [];
				}
			}
		}

		($discord = $this->plugin->getDiscord())->getChatQueue()->tick();
		if ($this->runs % 20 == 0) {
			if ($this->runs % (20 * 60) == 0) {
				$this->plugin->getVote()->updateTops();
			}
			$this->plugin->getVote()->tick();

			if ($this->runs % (20 * 60 * 10) == 0) {
				$this->plugin->getVote()->getVoteSite(1)->getWinners(1, true, function (array $winners): void {
				});
			}
			if ($this->runs % (20 * 60 * 5) == 0) {
				$this->plugin->getVote()->tickWinners();
			}

			$this->plugin->getEntities()->tick();
		}
		if ($this->runs % 100 == 0) {
			$discord->getCommandManager()->tick();
		}

		foreach (NetworkStructure::DOWNTIMES as $downtime) {
			if (time() >= $downtime["start"] && time() <= $downtime["end"]) {
				$dtmsg = TextFormat::RED . "The Network is currently down for maintenance and updates." . PHP_EOL .
					TextFormat::GRAY . "Remaining downtime scheduled: {time}" . PHP_EOL .
					TextFormat::YELLOW . "Check our Discord for more information: " . TextFormat::AQUA . "avengetech.net/discord";

				foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
					/** @var AtPlayer $player */
					if (!$player->isStaff()) {
						$player->kick(
							str_replace(
								"{time}",
								TextFormat::YELLOW . Utils::getRemainingTimeSimplified($downtime["end"]) . TextFormat::GRAY,
								$downtime["message"] . PHP_EOL . PHP_EOL . $dtmsg
							)
						);
					}
				}
				break;
			}
			if ($downtime["start"] - time() <= 10 && (($downtime["start"] - time()) % 2 == 0 || $downtime["start"] - time() <= 5) && $downtime["start"] > time() && $this->runs % 20 == 0) {
				foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
					$player->sendMessage(
						TextFormat::RED . "The Network will shutdown for maintenance in " .
							TextFormat::YELLOW . Utils::getRemainingTimeSimplified($downtime["start"])
					);
				}
			}
		}
	}

	public function getPlugin() {
		return $this->plugin;
	}
}
