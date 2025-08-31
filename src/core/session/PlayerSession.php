<?php

namespace core\session;

use core\AtPlayer as Player;
use core\AtPlayer;
use core\Core;
use core\network\server\ServerInstance;
use core\session\component\{
	BaseComponent,
	SaveableComponent
};
use core\staff\inventory\EnderinvInventory;
use core\staff\inventory\SeeinvInventory;
use core\user\User;
use pocketmine\Server;
use prison\PrisonSession;
use skyblock\SkyBlockSession;

class PlayerSession {

	public User $user;

	/** @var array<string, BaseComponent> */
	public array $components = [];

	public bool $loading = false;
	public array $loadCallables = [];
	public bool $loaded = false;

	public bool $saving = false;
	public ?\Closure $saveCallable = null;

	public ?SeeinvInventory $seeInv = null;
	public ?EnderinvInventory $enderInv = null;

	public function __construct(private SessionManager $sessionManager, Player|User $user) {
		$this->user = $user instanceof Player ? $user->getUser() : $user;
	}

	public function getSeeInv(): ?SeeinvInventory {
		return $this->seeInv;
	}

	public function getEnderInv(): ?EnderinvInventory {
		return $this->enderInv;
	}

	public function setupInventories() {
		$this->seeInv ??= new SeeinvInventory($this);
		$this->enderInv ??= new EnderinvInventory($this);
	}

	public function updateInventory(array $inventory, array $armor, array $cursor) {
		if (($player = $this->getPlayer()) instanceof AtPlayer && $player->isLoaded() && $player->isConnected()) {
			$player->invUpdateTick = Server::getInstance()->getTick();
			$player->getInventory()->setContents($inventory);
			$player->getArmorInventory()->setContents($armor);
			$player->getCursorInventory()->setContents($cursor);
		} elseif ($this instanceof PrisonSession || $this instanceof SkyBlockSession) {
			$data = $this->getData();
			if ($data->isLoaded() && !$data->isSaving()) {
				$data->inventory = $inventory;
				$data->armorinventory = $armor;
				$data->save(false);
			}
		}
	}

	public function updateEnderInventory(array $ender) {
		if (($player = $this->getPlayer()) instanceof AtPlayer && $player->isLoaded() && $player->isConnected()) {
			$player->enderUpdateTick = Server::getInstance()->getTick();
			$player->getEnderInventory()->setContents($ender);
		} elseif ($this instanceof PrisonSession || $this instanceof SkyBlockSession) {
			$data = $this->getData();
			if ($data->isLoaded() && !$data->isSaving()) {
				$data->enderchest_inventory = $ender;
				$data->save(false);
			}
		}
	}

	public function getSessionManager(): SessionManager {
		return $this->sessionManager;
	}

	public function getUser(): User {
		return $this->user;
	}

	public function getPlayer(): ?Player {
		return $this->getUser()->getPlayer();
	}

	public function getXuid(): int {
		return $this->getUser()->getXuid();
	}

	public function getGamertag(): string {
		return $this->getUser()->getGamertag();
	}

	public function createTables(): void {
		foreach ($this->getComponents() as $component) {
			if ($component instanceof SaveableComponent) {
				$component->createTables();
				$component->updateTables();
			}
		}
	}

	public function tick(): void {
		if ($this->isLoading()) {
			$loaded = true;
			foreach ($this->getComponents() as $component) {
				if ($component instanceof SaveableComponent && !$component->isLoaded()) {
					$loaded = false;
				}
			}
			if ($loaded) $this->finishLoadAsync();
		} elseif ($this->isSaving()) {
			$saved = true;
			foreach ($this->getComponents() as $component) {
				if ($component instanceof SaveableComponent && $component->isSaving()) {
					$saved = false;
				}
			}
			if ($saved) $this->finishSaveAsync();
		}

		$server = Core::getInstance()->getNetwork()->getServerManager()->getServerByPlayer($this->getGamertag());
		if ($server instanceof ServerInstance && $server->getIdentifier() !== Core::thisServer()->getIdentifier()) {
			$this->getSeeInv()?->closeToAll(true);
			$this->getEnderInv()?->closeToAll(true);
		}

		foreach ($this->getComponents() as $component) {
			if ($this->getPlayer() instanceof Player) $component->tick();
			if ($component instanceof SaveableComponent && $component->isLoaded()) {
				$component->checkSync(true);
			}
		}
	}

	/** @return array<string, BaseComponent> */
	public function getComponents(): array {
		return $this->components;
	}

	public function addComponent(BaseComponent $component) {
		$this->components[$component->getName()] = $component;
	}

	public function getComponent(string $name): ?BaseComponent {
		return $this->components[$name] ?? null;
	}

	public function isLoading(): bool {
		return $this->loading;
	}

	public function setLoading(bool $loading = true): void {
		$this->loading = $loading;
	}

	public function getLoadCallables(): array {
		return $this->loadCallables;
	}

	public function addLoadCallable(?callable $callable = null): void {
		$this->loadCallables[] = $callable;
	}

	public function load(?callable $callable = null): void {
		if ($callable !== null) $this->addLoadCallable($callable);
		if (!$this->isLoading()) {
			foreach ($this->getComponents() as $component) {
				if ($component instanceof SaveableComponent) {
					$component->setLoaded(false);
					$component->loadAsync();
				}
			}
			$this->setLoading();
		}
	}

	public function finishLoadAsync(): void {
		$this->setLoading(false);
		$this->setLoaded();
		if (count($callables = $this->getLoadCallables()) > 0) {
			foreach ($callables as $callable) $callable($this);
			$this->loadCallables = [];
		}
	}

	public function isLoaded(): bool {
		return $this->loaded;
	}

	public function setLoaded(bool $loaded = true): void {
		$this->loaded = $loaded;
		if ($loaded) $this->setupInventories();
	}

	public function isSaving(): bool {
		return $this->saving;
	}

	public function setSaving(bool $saving = true): void {
		$this->saving = $saving;
	}

	public function getSaveCallable(): ?callable {
		return $this->saveCallable;
	}

	public function setSaveCallable(?callable $callable = null): void {
		$this->saveCallable = $callable;
	}

	public function save(bool $async = true, ?callable $callable = null): bool {
		if (!$this->isLoaded()) return false;

		if ($async) {
			$this->setSaving();
			$this->setSaveCallable($callable);
			foreach ($this->getComponents() as $component) {
				if ($component instanceof SaveableComponent) {
					$component->saveAsync();
				}
			}
		} else {
			foreach ($this->getComponents() as $component) {
				if ($component instanceof SaveableComponent) {
					$component->save();
					if ($callable !== null) $callable($this);
				}
			}
		}
		return true;
	}

	public function finishSaveAsync(): void {
		$this->setSaving(false);
		if (($callable = $this->getSaveCallable()) !== null) {
			$callable($this);
			$this->setSaveCallable();
		}
	}
}
