<?php

namespace core\utils;

use pmmp\thread\ThreadSafeArray;

use pocketmine\snooze\SleeperNotifier;
use pocketmine\thread\log\ThreadSafeLoggerAttachment;

use core\discord\objects\{
	Post,
	Embed,
	Field,
	Footer,
	Webhook
};
use core\Core;
use core\discord\Structure;
use core\utils\TextFormat;

class DiscordErrorConsoleAttachment extends ThreadSafeLoggerAttachment {

	/** @var ThreadSafeArray */
	protected $buffer;

	/** @var SleeperNotifier */
	private $notifier;

	public function __construct() {
		$this->buffer = new ThreadSafeArray();
	}

	public function log($level, $message): void {
		$this->buffer[] = $message;
		//$this->notifier->wakeupSleeper();
		if (($level == "critical" || $level == "error") && !stristr($message, "]: #")) {
			$id = "unknown";
			if (($core = Core::getInstance()) !== null) {
				$id = $core->getNetwork()->getIdentifier();
				$network = $core->getNetwork();
				if ($network !== null) {
					$id = $core->getNetwork()->getIdentifier();
				}
			}
			$message = explode("--- Stack trace ---", $message);
			$trace = "";
			$len = 0;
			if (isset($message[1])) {
				$nt = explode("\n", $message[1]);
				foreach ($nt as $tr) {
					$trace .= $tr . "\n";
					$len += strlen($tr);
					if ($len >= 500) break;
				}
			}
			$post = new Post("", "Error Log - " . $id, "[REDACTED]", false, "", [
				new Embed("", "rich", "A" . ($level == "error" ? "n " : " ") . $level . " alert was sent on " . $id . "!", "", "ffb106", new Footer("Oh no! Miguel wtf did u do!"), "", "[REDACTED]", null, [
					new Field("Error", TextFormat::clean($message[0]), true),
					new Field("Trace", TextFormat::clean($trace), true)
				])
			]);
			$wh = Webhook::getWebhookByName("errors-" . Core::thisServer()->getType() . (Core::thisServer()->isTestServer() ? "-test" : ""));
			if ($wh->id === Structure::WEBHOOKS["other"]["id"]) $wh = Webhook::getWebhookByName("errors-other");
			$post->setWebhook($wh);
			$post->send();
		}
	}

	/**
	 * @return string|null
	 */
	public function getLine(): ?string {
		return $this->buffer->shift();
	}
}
