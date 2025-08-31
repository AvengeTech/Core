<?php

namespace core\inbox;

use pocketmine\item\Item;
use pocketmine\nbt\BigEndianNbtSerializer;

use core\{
	Core,
	AtPlayer as Player
};
use core\inbox\object\{
	InboxInstance,
	MessageInstance
};
use core\session\component\{
	ComponentRequest,
	SaveableComponent
};
use core\session\mysqli\data\MySqlQuery;

class InboxComponent extends SaveableComponent {

	public array $inboxes = [];

	public function getName(): string {
		return "inbox";
	}

	/** @return InboxInstance[] */
	public function getInboxes(): array {
		return $this->inboxes;
	}

	public function getInbox(int $type = Inbox::TYPE_GLOBAL): ?InboxInstance {
		return $this->inboxes[$type] ?? null;
	}

	public function getInboxByInbox(InboxInstance $inbox): ?InboxInstance {
		foreach ($this->getInboxes() as $i) {
			if ($i->getServer() == $inbox->getServer()) return $i;
		}
		return null;
	}

	public function doItems(string $data): array {
		$data = unserialize(zlib_decode($data));
		$stream = new BigEndianNbtSerializer();
		foreach ($data as $slot => $buffer) {
			$data[$slot] = Item::nbtDeserialize($stream->read($buffer)->mustGetCompoundTag());
		}
		return $data;
	}

	public function getTotalNewMessages(): int {
		$messages = 0;
		foreach ($this->getInboxes() as $inbox) {
			foreach ($inbox->getMessages() as $message) {
				if (!$message->hasOpened()) $messages++;
			}
		}
		return $messages;
	}

	public function createTables(): void {
		$db = $this->getSession()->getSessionManager()->getDatabase();
		foreach ([
			"CREATE TABLE IF NOT EXISTS inbox_message(mid INT NOT NULL, time INT NOT NULL, receiver BIGINT(16) NOT NULL, sender BIGINT(16) NOT NULL, identifier VARCHAR(32) NOT NULL, subject VARCHAR(255) NOT NULL, body VARCHAR(1024) NOT NULL, reply TINYINT(1) NOT NULL DEFAULT '0', opened TINYINT(1) NOT NULL DEFAULT '0', items LONGBLOB NOT NULL, PRIMARY KEY (mid, receiver, identifier))",
		] as $query) $db->query($query);
	}

	public function loadAsync(): void {
		$queries = [];
		foreach (["core", Core::getInstance()->getNetwork()->getIdentifier()] as $type)
			$queries[] = new MySqlQuery($type, "SELECT * FROM inbox_message WHERE receiver=? AND identifier=?", [$this->getXuid(), $type]);
		$request = new ComponentRequest($this->getXuid(), $this->getName(), $queries);

		$this->newRequest($request, ComponentRequest::TYPE_LOAD);

		parent::loadAsync();
	}

	public function finishLoadAsync(?ComponentRequest $request = null): void {
		$key = 0;
		foreach ($request->getQueries() as $query) {
			$inbox = new InboxInstance($this->getUser(), $query->getId());
			foreach ($query->getResult()->getRows() as $row) {
				$inbox->addMessage(new MessageInstance(
					$inbox,
					$row["mid"],
					$row["time"],
					$row["sender"],
					$row["subject"],
					$row["body"],
					(bool) $row["reply"],
					(bool) $row["opened"],
					$this->doItems($row["items"])
				));
			}
			$this->inboxes[$key] = $inbox;
			$key++;
		}

		parent::finishLoadAsync($request);
	}

	public function saveAsync(): void {
		$player = $this->getPlayer();
		$queries = [];
		foreach ($this->getInboxes() as $type => $inbox) {
			foreach ($inbox->getMessages() as $message) {
				if ($message->hasChanged()) {
					$queries[] = new MySqlQuery(
						"message" . $message->getId(),
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
							$message->getId(), $message->getTime(),
							$this->getXuid(), $message->getSender()->getXuid(),
							$inbox->getServer(),
							$message->getSubject(), $message->getBody(),
							(int) $message->canReply(), (int) $message->hasOpened(),
							$message->getItemString()
						]
					);
					$message->setChanged(false);
				}
			}
		}

		$request = new ComponentRequest($this->getXuid(), $this->getName(), $queries);
		$this->newRequest($request, ComponentRequest::TYPE_SAVE);

		parent::saveAsync();
	}

	public function save(): bool {
		$db = $this->getSession()->getSessionManager()->getDatabase();
		$stmt = $db->prepare("
			INSERT INTO inbox_message(
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
		");
		foreach ($this->getInboxes() as $type => $inbox) {
			$rxuid = $this->getXuid();
			$sid = $inbox->getServer();
			foreach ($inbox->getMessages() as $message) {
				$mid = $message->getId();
				$time = $message->getTime();
				$sender = $message->getSender()->getXuid();
				$subject = $message->getSubject();
				$body = $message->getBody();
				$reply = (int) $message->canReply();
				$opened = (int) $message->hasOpened();
				$items = $message->getItemString();

				$stmt->bind_param("iiiisssiis", $mid, $time, $rxuid, $sender, $sid, $subject, $body, $reply, $opened, $items);
				$stmt->execute();
			}
		}
		$stmt->close();

		return parent::save();
	}

	public function getSerializedData(): array {
		$messages = [];

		foreach ($this->getInboxes() as $type => $inbox) {
			$messages[$type] ??= [];
			$rxuid = $this->getXuid();
			$sid = $inbox->getServer();
			foreach ($inbox->getMessages() as $message) {
				$mid = $message->getId();
				$time = $message->getTime();
				$sender = $message->getSender()->getXuid();
				$subject = $message->getSubject();
				$body = $message->getBody();
				$reply = (int) $message->canReply();
				$opened = (int) $message->hasOpened();
				$items = $message->getItemString();

				$messages[$sid][] = [
					"mid" => $mid,
					"time" => $time,
					"reciever" => $rxuid,
					"sender" => $sender,
					"identifier" => $sid,
					"subject" => $subject,
					"body" => $body,
					"reply" => $reply,
					"opened" => $opened,
					"items" => $items,
				];
			}
		}

		return [
			"messages" => $messages
		];
	}

	public function applySerializedData(array $data): void {
		foreach ($this->getInboxes() as $key => $inbox) {
			if (!isset($data["messages"][$inbox->getServer()])) continue;
			$inbox = new InboxInstance($this->getUser(), $inbox->getServer());
			foreach ($data["messages"][$inbox->getServer()] as $msg) {
				$inbox->addMessage(new MessageInstance(
					$inbox,
					$msg["mid"],
					$msg["time"],
					$msg["sender"],
					$msg["subject"],
					$msg["body"],
					(bool) $msg["reply"],
					(bool) $msg["opened"],
					$this->doItems($msg["items"])
				));
				$this->inboxes[$key] = $inbox;
			}
		}
	}
}
