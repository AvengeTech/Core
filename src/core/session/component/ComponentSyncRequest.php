<?php

namespace core\session\component;

use core\network\data\DataSyncQuery;

class ComponentSyncRequest {

	/** @var DataSyncQuery[] */
	public array $queries;

	public function __construct(public int $xuid, public string $componentName, DataSyncQuery|array $queries) {
		$this->queries = is_array($queries) ? $queries : [$queries];
	}

	public function getXuid(): int {
		return $this->xuid;
	}

	public function getComponentName(): string {
		return $this->componentName;
	}

	public function getId(): string {
		return $this->componentName . ":" . $this->xuid;
	}

	public function isFinished(): bool {
		$finished = true;
		foreach ($this->queries as $query) {
			if (is_null($query->getResult())) {
				$finished = false;
				break;
			}
		}
		return $finished;
	}

	/** @return DataSyncQuery[] */
	public function getQueries(): array {
		return $this->queries;
	}

	public function getQuery(string $id = "main"): ?DataSyncQuery {
		foreach ($this->queries as $query) {
			if ($query->getId() === $id) {
				return $query;
			}
		}
		return null;
	}
}
