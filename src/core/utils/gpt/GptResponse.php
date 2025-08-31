<?php

namespace core\utils\gpt;

class GptResponse {

	public array $choices = [];

	public function __construct(
		public string $id,
		public string $object,
		public int $created,
		public string $model,
		public array $usage,
		array $choices = [],
	) {
		foreach ($choices as $key => $choice) {
			if (!$choice instanceof Choice) {
				$this->choices[] = Choice::fromArray($choice);
			} else {
				$this->choices[] = $choice;
			}
		}
	}

	public static function fromJson(string $json): GptResponse {
		$decoded = json_decode($json, true);
		return new GptResponse($decoded["id"] ?? 0, $decoded["object"] ?? "", $decoded["created"] ?? 0, $decoded["model"] ?? "", $decoded["usage"] ?? [], $decoded["choices"] ?? [new Choice("error", 0, Message::create(($decoded["error"] ?? [])["message"] ?? "error"))]);
	}

	public function getId(): string {
		return $this->id;
	}

	public function getObject(): string {
		return $this->object;
	}

	public function getCreated(): int {
		return $this->created;
	}

	public function getModelString(): string {
		return $this->model;
	}

	public function getUsage(): array {
		return $this->usage;
	}

	public function getChoices(): array {
		return $this->choices;
	}
}
