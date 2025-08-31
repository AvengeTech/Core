<?php

namespace core\session\component;

use core\Core;
use core\network\data\DataSyncResult;
use core\network\protocol\DataSyncPacket;
use core\network\data\DataSyncQuery;

abstract class SaveableComponent extends BaseComponent {

	private bool $loading = false;
	private bool $loaded = false;

	private array $changeVerify = [];
	private bool $changed = false;
	private bool $saving = false;
	private bool $syncing = false;

	private float $lastUpdateTime = -1;
	private float $lastSyncTime = -1;
	/** @var DataSyncQuery[] */
	private array $syncQueries = [];
	/** @var ComponentSyncRequest[] */
	private array $activeRequests = [];

	abstract public function createTables(): void;

	public function updateTables(): void {
	}

	public function isLoading(): bool {
		return $this->loading;
	}

	public function setLoading(bool $loading = true): void {
		$this->loading = $loading;
	}

	public function isSyncing(): bool {
		return $this->syncing;
	}

	public function setSyncing(bool $syncing = true): void {
		$this->syncing = $syncing;
	}

	/*
	 * Override and send ComponentRequest to thread
	 */
	public function loadAsync(): void {
		$this->setLoading();
		$this->setLoaded(false);
		//echo "loading " . $this->getName() . " component...", PHP_EOL;
	}

	public function requestSync(bool $push = false): void {
		$this->setSyncing();
	}

	/**
	 * Override this in extensions
	 */
	public function finishLoadAsync(?ComponentRequest $request = null): void {
		$this->setLoading(false);
		$this->setLoaded(true);
		//echo $this->getName() . " component loaded!", PHP_EOL;
	}

	public function checkSync(bool $autosync = false): bool {
		if ($this->isSyncing()) {
			return false;
		}
		if ($this->getLastSyncTime() < microtime(true) - 2) {
			if ($autosync) {
				$this->requestSync();
				return true;
			} else return false;
		}
		return true;
	}

	public function finishSync(?ComponentSyncRequest $request = null): void {
		$this->setLoading(false);
		$this->setLoaded(true);
		$this->setSyncing(false);
		$this->setLastSyncTime(microtime(true));
		$this->setLastUpdateTime(microtime(true));
	}

	public function isLoaded(): bool {
		return $this->loaded;
	}

	public function setLoaded(bool $loaded = true): void {
		$this->loaded = $loaded;
	}

	public function hasChanged(): bool {
		return $this->changed;
	}

	public function setChanged(bool $changed = true): void {
		$this->changed = $changed;
		if ($changed) {
			$this->setLastUpdateTime(microtime(true));
			$this->requestSync(true);
		}
	}

	/**
	 * Returns whether data has changed since async save
	 * (Must override in each component where necessary)
	 */
	public function verifyChange(): bool {
		return false;
	}

	public function getChangeVerify(): array {
		return $this->changeVerify;
	}

	public function setChangeVerify(array $data = []): void {
		$this->changeVerify = $data;
	}

	public function isSaving(): bool {
		return $this->saving;
	}

	public function setSaving(bool $saving = true): void {
		$this->saving = $saving;
	}

	public function getLastSyncTime(): float {
		return $this->lastSyncTime;
	}

	public function setLastSyncTime(float $time): void {
		$this->lastSyncTime = $time;
	}

	public function getLastUpdateTime(): float {
		return $this->lastUpdateTime;
	}

	public function setLastUpdateTime(float $time): void {
		$this->lastUpdateTime = $time;
	}

	/**
	 * Override and send ComponentRequest to thread
	 */
	public function saveAsync(): void {
		$this->setSaving();
	}

	public function finishSaveAsync(): void {
		$this->setSaving(false);
		$this->setChanged($this->verifyChange());
	}

	/**
	 * Main thread saving
	 */
	public function save(): bool {
		$this->setChanged(false);
		return true;
	}

	protected function newRequest(ComponentRequest|ComponentSyncRequest $request, int $type = ComponentRequest::TYPE_STRAY): void {
		$this->getSession()->getSessionManager()->newRequest($request, $type);
	}

	abstract public function getSerializedData(): array;

	abstract public function applySerializedData(array $data): void;
}
