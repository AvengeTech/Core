<?php

namespace core\utils\gpt;

use pocketmine\Server;

use core\utils\gpt\task\GptSendRequestTask;

class GptQueue {

	public static int $requestId = 0;

	public array $requests = [];

	public function __construct(
		public Model $model
	) {
	}

	public function tick(): void {
		foreach ($this->getRequests() as $id => $request) {
			if ($request->hasResponse()) {
				$closure = $request->getResponseClosure();
				$closure($request);
				unset($this->requests[$id]);
			}
		}
	}

	public static function newRequestId(): int {
		return self::$requestId++;
	}

	public function getModel(): Model {
		return $this->model;
	}

	public function getRequests(): array {
		return $this->requests;
	}

	public function getRequest(int $id): ?GptRequest {
		return $this->requests[$id] ?? null;
	}

	public function quickRequest(string $message, \Closure $closure, ?Conversation $conversation = null, string $role = GptRequest::ROLE_USER): void {
		$msg = Message::create($message, $role);
		if ($conversation === null) {
			$msgs = [$msg];
		} else {
			$conversation->addMessage($msg);
			$msgs = $conversation->getMessages();
		}
		$this->addRequest(
			GptRequest::create($msgs, $closure, $conversation)
		);
	}

	public function addRequest(GptRequest $request): void {
		$this->requests[$id = self::newRequestId()] = $request;
		$this->processRequest($id, $request);
	}

	public function processRequest(int $id, GptRequest $request, ?Model $model = null): void {
		$task = new GptSendRequestTask($model ?? $this->getModel(), $id, $request->toJson($this->getModel()));
		Server::getInstance()->getAsyncPool()->submitTask($task);
	}

	public function processResponse(int $id, string $response): void {
		$this->requests[$id]?->setResponse(GptResponse::fromJson($response));
	}
}
