<?php

namespace core\inbox\object;

use pocketmine\nbt\{
	BigEndianNbtSerializer,
	TreeRoot
};

use core\{
	Core,
	AtPlayer as Player
};
use core\session\mysqli\data\MySqlQuery;
use core\session\stray\StrayRequest;
use core\user\User;

class MessageInstance {

	public bool $changed = false;
	public $inventory = null;

	public static int $lastId = 0;
	public static function newId(): int {
		return self::$lastId++;
	}

	public function __construct(
		public InboxInstance $inbox,

		public int $id,
		public int $time,
		public User|int $sender,

		public string $subject,
		public string $body,

		public bool $reply = true,
		public bool $opened = false,

		public array $items = []
	) {
		if (!$sender instanceof User) {
			Core::getInstance()->getUserPool()->useUser($sender, function (User $user): void {
				$this->sender = $user;
			});
		}
	}

	public function getInbox(): InboxInstance {
		return $this->inbox;
	}

	public function getId(): int {
		return $this->id;
	}

	public function getTime(): int {
		return $this->time;
	}

	public function getFormattedTime(): string {
		return date("m/d/Y", $this->getTime());
	}

	public function getSender(): User|int {
		return $this->sender;
	}

	public function getSubject(): string {
		return $this->subject;
	}

	public function getBody(): string {
		return $this->body;
	}

	public function canReply(): bool {
		return $this->reply;
	}

	public function hasOpened(): bool {
		return $this->opened;
	}

	public function setOpened(bool $opened = true): void {
		$this->opened = $opened;
		$this->setChanged();
	}

	public function getItems(): array {
		return $this->items;
	}

	public function setItems(array $items): void {
		$this->items = $items;
		$this->setChanged();
	}

	public function getItemString(): string {
		$data = [];
		$stream = new BigEndianNbtSerializer();
		foreach ($this->getItems() as $slot => $item) {
			$data[$slot] = $stream->write(new TreeRoot($item->nbtSerialize()));
		}
		return zlib_encode(serialize($data), ZLIB_ENCODING_DEFLATE, 1);
	}

	public function hasChanged(): bool {
		return $this->changed;
	}

	public function setChanged(bool $changed = true): void {
		$this->changed = $changed;
	}

	public function save(): void {
		Core::getInstance()->getSessionManager()->sendStrayRequest(new StrayRequest(
			"inbox_message_save_" . ($xuid = $this->getInbox()->getXuid()) . "_" . ($mid = $this->getId()),
			new MySqlQuery(
				"message" . $this->getId(),
				"INSERT INTO inbox_message(
					mid, time, receiver, sender, identifier,
					subject, body, reply, opened, items
				) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
				ON DUPLICATE KEY UPDATE
					time=VALUES(time),
					sender=VALUES(sender),
					subject=VALUES(subject),
					body=VALUES(body),
					reply=VALUES(reply),
					opened=VALUES(opened),
					items=VALUES(items)
				",
				[
					$mid, $this->getTime(),
					$xuid, is_int($this->getSender()) ? $this->getSender() : $this->getSender()->getXuid(),
					$this->getInbox()->getServer(),
					$this->getSubject(), $this->getBody(),
					(int) $this->canReply(), (int) $this->hasOpened(),
					$this->getItemString()
				]
			)
		), function (StrayRequest $request): void {
		});
	}

	public function delete(): void {
		Core::getInstance()->getSessionManager()->sendStrayRequest(new StrayRequest("inbox_message_delete_" . ($xuid = $this->getInbox()->getXuid()) . "_" . ($mid = $this->getId()), new MySqlQuery(
			"main",
			"DELETE FROM inbox_message WHERE mid=? AND receiver=? AND identifier=?",
			[$mid, $xuid, $this->getInbox()->getServer()]
		)), function (StrayRequest $request): void {
		});
	}

	public function getSource(): ?MessageInstance {
		$player = $this->getInbox()->getUser()->getPlayer();
		if ($player instanceof Player) {
			$inbox = $player->getSession()->getInbox()->getInboxByInbox($this->getInbox());
			return $inbox->getMessage($this->getId());
		}
		return null;
	}

	public function verify(&$source = null): bool {
		return ($source = $this->getSource()) !== null;
	}
}
