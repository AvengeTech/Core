<?php

namespace core\discord\objects;

use core\discord\Structure;

class Webhook {

	const WEBHOOK_URL = "https://discordapp.com/api/webhooks/";

	public $id;
	public $secret;

	public function __construct(int $id = 0/*REDACTED*/, string $secret = "[REDACTED]") {
		$this->id = $id;
		$this->secret = $secret;
	}

	public function getId(): string {
		return $this->id;
	}

	public function getSecret(): string {
		return $this->secret;
	}

	public function getUrl(): string {
		return self::WEBHOOK_URL . $this->getId() . "/" . $this->getSecret();
	}

	public static function getWebhookByName(string $name): ?Webhook {
		$data = Structure::WEBHOOKS[strtolower($name)] ?? Structure::WEBHOOKS["other"];
		return new Webhook($data["id"], $data["secret"]);
	}
}
