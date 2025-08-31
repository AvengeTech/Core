<?php

namespace core\friends;

use core\session\component\{
	ComponentRequest,
	SaveableComponent
};
use core\session\mysqli\data\MySqlQuery;

/**
 * @deprecated
 */
class FriendsComponent extends SaveableComponent {

	public array $friends = [];

	public array $blocked = [];
	public array $blockedBy = [];

	public function getName(): string {
		return "friends";
	}

	public function getFriends(): array {
		return $this->friends;
	}

	public function addFriend(Friend $friend, bool $save = false): void {
		$this->friends[$friend->getFriend()->getGamertag()] = $friend;
	}

	public function removeFriend(Friend|string $friend): void {
		unset($this->friends[$friend instanceof Friend ? $friend->getFriend()->getGamertag() : $friend]);
	}

	public function getBlocked(): array {
		return $this->blocked;
	}

	public function addBlocked(Block $block): void {
		$this->blocked[$block->getUser()->getGamertag()] = $block;
	}

	public function getBlockedBy(): array {
		return $this->blockedBy;
	}

	public function addBlockedBy(Block $block): array {
		$this->blockedBy[$block->getUser()] = $block;
	}

	public function createTables(): void {
		$db = $this->getSession()->getSessionManager()->getDatabase();
		foreach ([
			"CREATE TABLE IF NOT EXISTS friends(xuid BIGINT(16) NOT NULL, friend BIGINT(16) NOT NULL, created INT NOT NULL, settingsVersion VARCHAR(10) NOT NULL, settings VARCHAR(5000) NOT NULL DEFAULT '{}', PRIMARY KEY(xuid, friend));",
			"CREATE TABLE IF NOT EXISTS blockList(xuid BIGINT(16) NOT NULL, blocked BIGINT(16) NOT NULL, created INT NOT NULL, PRIMARY KEY(xuid, blocked)",
		] as $query) $db->query($query);
	}

	public function loadAsync(): void {
		$request = new ComponentRequest($this->getXuid(), $this->getName(), [
			new MySqlQuery("friends", "SELECT * FROM friends WHERE xuid=?", [$this->getXuid()]),
			new MySqlQuery("blocked", "SELECT * FROM blockList WHERE xuid=?", [$this->getXuid()]),
			new MySqlQuery("blockedBy", "SELECT * FROM blockList WHERE blocked=?", [$this->getXuid()]),
		]);
		$this->newRequest($request, ComponentRequest::TYPE_LOAD);
		parent::loadAsync();
	}

	public function finishLoadAsync(?ComponentRequest $request = null): void {
		$friends = $request->getQuery("friends")->getResult()->getRows();
		$blocked = $request->getQuery("blocked")->getResult()->getRows();
		$blockedBy = $request->getQuery("blockedBy")->getResult()->getRows();

		foreach ($friends as $friend) {
		}
		foreach ($blocked as $block) {
		}
		foreach ($blockedBy as $block) {
		}

		parent::finishLoadAsync($request);
	}

	public function saveAsync(): void {
	}
}
