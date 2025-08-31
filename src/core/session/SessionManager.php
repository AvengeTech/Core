<?php

namespace core\session;

use pocketmine\Server;
use pocketmine\plugin\Plugin;

use core\AtPlayer as Player;
use core\Core;
use core\network\data\DataSyncResult;
use core\session\component\{
    ComponentSyncRequest,
    SaveableComponent
};
use core\session\mysqli\data\{
	MySqlRequest,
	MySqlUtils
};
use core\user\User;
use core\utils\AsyncStuff;
use core\utils\Utils;
use poggit\libasynql\{
	libasynql,
	DataConnector,
	SqlThread,
	SqlError
};

class SessionManager {

	public int $ticks = 0;

	public array $sessions = [];

	private \mysqli $database;
	private ?DataConnector $db;

	public array $doneSaving = [];
	public array $doneLoading = [];
	public array $doneStray = [];

	public array $waitingRequests = [];

	public bool $saveOnLeave = true;
	public array $sessionSaved = [];
	public array $gameSessionSaved = [];

	/** @var ComponentSyncRequest[] */
	public array $syncRequests = [];
	/** @var \Closure[] */
	public array $syncReturns = [];

	public function __construct(Plugin $plugin, private string $sessionClass, private string $databaseName) {
		try {
			$this->database = new \mysqli(...MySqlUtils::generateCredentials($databaseName));
			$this->database->query("SET SESSION wait_timeout=2147483");
		} catch (\Exception $e) {
			$plugin->getLogger()->error("Database connection failed!");
			Server::getInstance()->shutdown();
		}

		try {
			$this->db = libasynql::create($plugin, MySqlUtils::generateCredentials($databaseName, true), [
				"mysql" => "mysql.sql"
			]);
		} catch (\Exception $e) {
			echo $e->getMessage(), PHP_EOL;
			Server::getInstance()->shutdown();
			return;
		}

		$class = new ($this->getSessionClass())($this, new User(0, "Server"));
		$class->createTables();
	}

	public function tick(): void {
		$this->ticks++;

		foreach ($this->doneLoading as $key => $data) {
			$session = $this->getSession($data->getXuid());
			if ($session instanceof PlayerSession) {
				$component = $session->getComponent($data->getComponentName());
				if ($component instanceof SaveableComponent) {
					$component->finishLoadAsync($data);
				}
			}
			unset($this->doneLoading[$key]);
		}

		foreach ($this->doneSaving as $key => $data) {
			$session = $this->getSession($data->getXuid());
			if ($session instanceof PlayerSession) {
				$component = $session->getComponent($data->getComponentName());
				if ($component instanceof SaveableComponent) {
					$component->finishSaveAsync();
				}
			}
			unset($this->doneSaving[$key]);
		}

		foreach ($this->doneStray as $key => $request) {
			$return = $this->getStrayReturn($request);
			if ($return instanceof \Closure) {
				$return($request);
			}
			unset($this->doneStray[$key]);
		}

		foreach ($this->syncRequests as $key => $request) {
			if ($request->isFinished()) {
				unset($this->syncRequests[$key]);
				[$componentName, $xuid] = explode(":", $key);
				$component = $this->getSession((int) $xuid)?->getComponent($componentName);
				if ($component instanceof SaveableComponent) {
					$component->finishSync($request);
				}
			}
		}

		foreach ($this->getSessions() as $session) $session->tick();

		if ($this->ticks % 8 == 0) {
			foreach ($this->sessionSaved as $name => $time) {
				if (time() > $time + 60) {
					unset($this->sessionSaved[$name]);
					continue;
				}
				/**$player = Server::getInstance()->getPlayerExact($name); //need to figure out a good way to do this incase the player join order is different
				if($player instanceof Player && $player->hasFullyJoined()){
					$player->setSessionSaved();
					unset($this->sessionSaved[$name]);
					continue;
				}*/
			}
		}
	}

	public function close(): void {
		$this->saveAll();
		$this->getDatabase()->close();
		if (isset($this->db)) $this->db->close();
	}

	public function getSessionClass(): string {
		return $this->sessionClass;
	}

	public function getSessions(): array {
		return $this->sessions;
	}

	public function getSession(User|Player|int $player): ?PlayerSession {
		return $this->sessions[is_int($player) ? $player : $player->getXuid()] ?? null;
	}

	public function useSession(User|Player|int $player, \Closure $closure = null, bool $ignoreLoaded = false): void {
		if (($session = $this->getSession($player)) !== null) {
			if ($session->isLoaded() || $ignoreLoaded) {
				$closure($session);
			} else {
				$session->addLoadCallable($closure);
			}
			return;
		}
		$this->loadSession($player, $closure);
	}

	/**
	 * Loads whole player session + caches
	 */
	public function loadSession(User|Player $player, callable $callable = null): void {
		$class = $this->getSessionClass();
		if (!isset($this->sessions[$player->getXuid()]))
			$this->sessions[$player->getXuid()] = new $class($this, $player);
		$this->getSession($player)->load($callable);
	}

	public function saveSession(User|Player $player, bool $async, callable $callable = null): void {
		$session = $this->getSession($player);
		if ($session instanceof PlayerSession) {
			$session->save($async, $callable);
		}
	}

	public function removeSession(User|Player|int $id): void {
		unset($this->sessions[(is_int($id) ? $id : $id->getXuid())]);
	}

	public function saveAll(bool $async = false, ?callable $callable = null): void {
		foreach ($this->getSessions() as $session) {
			if ($session->getPlayer() instanceof Player) $session->save($async, $callable);
		}
	}

	public function getDb(): ?DataConnector {
		return $this->db;
	}

	public function getDatabase(): \mysqli {
		return $this->database;
	}

	public function sendStrayRequest(MySqlRequest $request, \Closure $return): void {
		$this->newRequest($request, MySqlRequest::TYPE_STRAY, $return);
	}

	public function getStrayReturn(MySqlRequest $request): ?\Closure {
		return $this->waitingRequests[$request->getId()] ?? null;
	}

	public function getWaitingRequests(): array {
		return $this->waitingRequests;
	}

	public function getDatabaseName(): string {
		return $this->databaseName;
	}

	public function newRequest(MySqlRequest|ComponentSyncRequest $request, int $type = MySqlRequest::TYPE_STRAY, ?\Closure $return = null): void {
		if ($request instanceof MySqlRequest) {
			$rq = $request->getQueries();

			$queries = [];
			$args = [];
			$modes = [];
			foreach ($rq as $query) {
				$queries[] = $q = $query->getQuery();
				$args[] = (array) $query->getParameters();
				$modes[] = stristr($q, "select") ? SqlThread::MODE_SELECT : (stristr($q, "insert") ? SqlThread::MODE_INSERT : (
					(stristr($q, "update") || stristr($q, "delete")) ? SqlThread::MODE_CHANGE : SqlThread::MODE_GENERIC
				)
				);
			}
			switch ($type) {
				case MySqlRequest::TYPE_LOAD:
					$this->getDb()->executeImplRaw($queries, $args, $modes, function (array $results) use ($request): void {
						foreach ($request->getQueries() as $query) {
							$query->setResult(array_shift($results));
						}
						//var_dump($request);
						$this->doneLoading[] = $request;
					}, function (SqlError $e): void {
						echo $e->getMessage(), PHP_EOL;
					});
					break;
				case MySqlRequest::TYPE_SAVE:
					$this->getDb()->executeImplRaw($queries, $args, $modes, function (array $results) use ($request): void {
						foreach ($request->getQueries() as $query) {
							$query->setResult(array_shift($results));
						}
						//var_dump($request);
						$this->doneSaving[] = $request;
					}, function (SqlError $e): void {
						echo $e->getMessage(), PHP_EOL;
					});
					break;
				case MySqlRequest::TYPE_STRAY:
					$this->waitingRequests[$request->getId()] = $return;
					$this->getDb()->executeImplRaw($queries, $args, $modes, function (array $results) use ($request): void {
						foreach ($request->getQueries() as $query) {
							$query->setExecuted();
							$query->setResult(array_shift($results));
						}
						//var_dump($request);
						$this->doneStray[] = $request;
					}, function (SqlError $e): void {
						echo $e->getMessage(), PHP_EOL;
					});
					break;
			}
		} else {
			foreach ($request->getQueries() as $query) {
				foreach ($query->getPackets() as $packet) {
					$dat = array_merge($packet->getPacketData(), ["rid" => $request->getId(), "qid" => $query->getId(), "schema" => $this->getDatabaseName()]);
					$packet->setPacketData($dat);
				}
			}
			$this->syncRequests[$request->getId()] = $request;
			if (!is_null($return)) $this->syncReturns[$request->getId()] = $return;
			foreach ($request->getQueries() as $query) $query->send();
		}
	}

	public function processSyncReturn(string $id, string $qid, array $data): void {
		$rt = new DataSyncResult($data);
		if (isset($this->syncReturns[$id])) {
			$callable = $this->syncReturns[$id];
			unset($this->syncReturns[$id]);
			$callable($rt);
		}
		($this->syncRequests[$id] ?? null)?->getQuery($qid)?->setResult($rt);
	}

	public function addSessionSaved(string $name): void {
		$this->sessionSaved[$name] = time();
	}

	public function shiftSessionSaved(Player $player): bool {
		if (isset($this->sessionSaved[$player->getName()])) {
			unset($this->sessionSaved[$player->getName()]);
			return true;
		}
		return false;
	}

	public function addGameSessionSaved(string $name): void {
		$this->gameSessionSaved[$name] = time();
	}

	public function shiftGameSessionSaved(Player $player): bool {
		if (isset($this->gameSessionSaved[$player->getName()])) {
			unset($this->gameSessionSaved[$player->getName()]);
			return true;
		}
		return false;
	}
}
