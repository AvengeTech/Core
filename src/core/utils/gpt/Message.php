<?php

namespace core\utils\gpt;

class Message {

	public function __construct(
		public string $content,
		public string $role
	) {
	}

	public static function create(string $content, string $role = GptRequest::ROLE_USER): Message {
		return new Message($content, $role);
	}

	public function getContent(): string {
		return $this->content;
	}

	public function getRole(): string {
		return $this->role;
	}
}
