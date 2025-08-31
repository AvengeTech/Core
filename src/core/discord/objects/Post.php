<?php

namespace core\discord\objects;

use pocketmine\Server;

use core\Core;
use core\discord\task\SendPostTask;

class Post {

	public $message = "test";
	public $username = "AvengeTech";
	public $avatar = "";

	public $tts = false;

	public $file = "";
	public $embeds = [];

	public $canMention = true;

	public $webhook;

	public function __construct(string $message, string $username = "AvengeTech", string $avatar = "", bool $tts = false, string $file = "", array $embeds = [], bool $canMention = true, ?Webhook $webhook = null) {
		$this->message = $message;
		$this->username = $username;
		$this->avatar = $avatar;

		$this->tts = $tts;

		$this->file = $file;
		$this->embeds = $embeds;

		$this->canMention = $canMention;

		if ($webhook == null) {
			$this->webhook = new Webhook();
		} else {
			$this->webhook = $webhook;
		}
	}

	public function getMessage(): string {
		return $this->message;
	}

	public function getUsername(): string {
		return $this->username;
	}

	public function getAvatar(): string {
		return $this->avatar;
	}

	public function isTextToSpeech(): bool {
		return $this->tts;
	}

	public function getFile(): string {
		return $this->file;
	}

	public function getEmbeds(): array {
		return $this->embeds;
	}

	public function canMention(): bool {
		return $this->canMention;
	}

	public function getWebhook(): Webhook {
		return $this->webhook;
	}

	public function setWebhook(Webhook $webhook): void {
		$this->webhook = $webhook;
	}

	public function toArray(): array {
		$embeds = [];
		foreach ($this->getEmbeds() as $embed) $embeds[] = $embed->toArray();

		return [
			"content" => $this->getMessage(),
			"username" => $this->getUsername(),
			"avatar_url" => $this->getAvatar(),
			"tts" => $this->isTextToSpeech(),
			"file" => $this->getFile(),

			"embeds" => $embeds,

			//"allowed_mentions" => $this->canMention()
		];
	}

	public function send(): void {
		$task = new SendPostTask($this);
		try {
			$pool = Core::getInstance()?->getAsyncPool() ?? Server::getInstance()->getAsyncPool();
			$pool->submitTask($task);
		} catch (\RuntimeException $e) {
			echo "couldn't send post", PHP_EOL;
		}
	}
}
