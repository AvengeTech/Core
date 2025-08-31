<?php

namespace core\inbox\object;

use pocketmine\item\Item;
use pocketmine\nbt\BigEndianNbtSerializer;

use core\Core;
use core\user\User;

class InboxInstance {

	public array $messages = [];

	public function __construct(public User $user, public string $server = "core") {
		if ($server == "here") $this->server = (($ser = Core::thisServer())->isSubServer() ? $ser->getParentServer()->getIdentifier() : $ser->getIdentifier());
	}

	public function doItems(string $data): array {
		$data = unserialize(zlib_decode($data));
		$stream = new BigEndianNbtSerializer();
		foreach ($data as $slot => $buffer) {
			$data[$slot] = Item::nbtDeserialize($stream->read($buffer)->mustGetCompoundTag());
		}
		return $data;
	}

	public function getUser(): User {
		return $this->user;
	}

	public function getXuid(): int {
		return $this->getUser()->getXuid();
	}

	public function getServer(): string {
		return $this->server;
	}

	public function getMessages(): array {
		return $this->messages;
	}

	public function getMessage(int $id): ?MessageInstance {
		return $this->messages[$id] ?? null;
	}

	public function addMessage(MessageInstance $message, bool $save = false): void {
		$this->messages[$message->getId()] = $message;
		if ($save) $message->save();
	}

	public function deleteMessage(int $id, bool $del = false): void {
		$msg = $this->messages[$id] ?? null;
		unset($this->messages[$id]);
		if ($del && $msg != null)
			$msg->delete();
	}
}
