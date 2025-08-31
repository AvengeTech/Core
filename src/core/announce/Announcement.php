<?php

namespace core\announce;

use pocketmine\utils\TextFormat;
use pocketmine\scheduler\Task;
use pocketmine\{
	player\Player,
	Server
};

use core\{
	Core,
	AtPlayer
};
use core\chat\Chat;
use core\ui\windows\CustomForm;
use core\ui\elements\customForm\Label;

class Announcement {

	const COLOR_PLACEHOLDER = "&";

	public $title = "";
	public $subs = [];

	public function __construct(string $title, array $subs) {
		$this->title = TextFormat::colorize($title, self::COLOR_PLACEHOLDER);
		$this->subs = $subs;
	}

	public function getTitle(): string {
		return $this->title;
	}

	public function setTitle(string $title): void {
		$this->title = TextFormat::colorize($title, self::COLOR_PLACEHOLDER);
	}

	public function getSubs(): array {
		return $this->subs;
	}

	public function getForm(): CustomForm {
		$form = new class($this->getTitle()) extends CustomForm {
			public function close(Player $player) {
				if (($closure = Core::getInstance()->getAnnounce()->getAfterAnnouncementClosure()) !== null) {
					$closure($player);
				}
			}
			public function handle($response, Player $player) {
				if (($closure = Core::getInstance()->getAnnounce()->getAfterAnnouncementClosure()) !== null) {
					$closure($player);
				}
			}
		};
		foreach ($this->getSubs() as $sub) {
			if ($sub->canDisplay()) {
				$text = Chat::convertWithEmojis(TextFormat::colorize($sub->getTitle(), self::COLOR_PLACEHOLDER)) . TextFormat::RESET . "\n";
				foreach ($sub->getBullets() as $bullet) {
					$text .= TextFormat::GRAY . "- " . Chat::convertWithEmojis(TextFormat::colorize($bullet, self::COLOR_PLACEHOLDER)) . TextFormat::RESET . "\n";
				}
				$text = trim($text);
				$form->addElement(new Label($text));
				$form->addElement(new Label("------------------------"));
			}
		}
		return $form;
	}

	public function send(Player $player): void {
		$task = new class($this, $player) extends Task {

			public $announcement;
			public $player = "";

			public function __construct(Announcement $announcement, Player $player) {
				$this->announcement = $announcement;
				$this->player = $player->getName();
			}

			public function onRun(): void {
				/** @var AtPlayer $player */
				$player = Server::getInstance()->getPlayerExact($this->player);
				if ($player === null) {
					return;
				}
				$player->showModal($this->announcement->getForm());
			}
		};
		Core::getInstance()->getScheduler()->scheduleDelayedTask($task, 15);
	}
}
