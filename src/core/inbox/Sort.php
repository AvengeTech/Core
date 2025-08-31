<?php

namespace core\inbox;

use core\{
	Core,
	AtPlayer as Player
};

class Sort {

	const TYPE_ALL = -1;
	const TYPE_GLOBAL = 0;
	const TYPE_HERE = 1;
	public $type = self::TYPE_ALL;

	const STATUS_ALL = -1;
	const STATUS_UNREAD = 0;
	const STATUS_READ = 1;
	public $status = self::STATUS_ALL;

	const SORT_NONE = -1;
	const SORT_NEWEST = 0;
	const SORT_OLDEST = 1;
	public $sort = self::SORT_NONE;

	public function getSortMessage(): string {
		$string = "Type: ";
		switch ($this->type) {
			case self::TYPE_ALL:
				$string .= "ALL";
				break;
			case self::TYPE_GLOBAL:
				$string .= "GLOBAL";
				break;
			case self::TYPE_HERE:
				$string .= "HERE";
				break;
		}

		if ($this->status !== self::STATUS_ALL) {
			$string .= " | Status: " . ($this->status == 0 ? "UNREAD" : "READ");
		}
		if ($this->sort !== self::SORT_NONE) {
			$string .= " | Sort by: " . ($this->sort == 0 ? "NEWEST" : "OLDEST");
		}

		return $string;
	}

	public function getMessages(Player $player): array {
		$session = $player->getSession()->getInbox();
		$inboxes = [];
		switch ($this->type) {
			case self::TYPE_ALL:
				foreach ($session->getInboxes() as $inbox) {
					$inboxes[$inbox->getServer()] = $inbox;
				}
				break;
			case self::TYPE_GLOBAL:
				$inboxes["core"] = $session->getInbox(0);
				break;
			case self::TYPE_HERE:
				$inboxes["here"] = $session->getInbox(1);
				break;
		}
		$ikeys = [];
		$status = $this->status;
		foreach ($inboxes as $name => $inbox) {
			$ikeys[$name] = [];
			foreach ($inbox->getMessages() as $message) {
				switch ($status) {
					case self::STATUS_ALL:
						$ikeys[$name][$message->getId()] = $message;
						break;
					case self::STATUS_UNREAD:
						if (!$message->hasOpened()) $ikeys[$name][$message->getId()] = $message;
						break;
					case self::STATUS_READ:
						if ($message->hasOpened()) $ikeys[$name][$message->getId()] = $message;
						break;
				}
			}
		}

		$rmes = [];

		foreach ($ikeys as $name => $messages) {
			switch ($this->sort) {
				case self::SORT_NONE:
					foreach ($messages as $msg) {
						$rmes[$msg->getId()] = $msg;
					}
					break;
				case self::SORT_NEWEST:
					usort($messages, function ($a, $b) {
						return $a->getTime() > $b->getTime() ? 1 : -1;
					});
					foreach ($messages as $msg) {
						$rmes[$msg->getId()] = $msg;
					}
					break;
				case self::SORT_OLDEST:
					usort($messages, function ($a, $b) {
						return $a->getTime() < $b->getTime() ? 1 : -1;
					});
					foreach ($messages as $msg) {
						$rmes[$msg->getId()] = $msg;
					}
					break;
			}
		}

		return $rmes;
	}
}
