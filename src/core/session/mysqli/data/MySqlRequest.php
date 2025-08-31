<?php

namespace core\session\mysqli\data;

use pmmp\thread\ThreadSafeArray;

class MySqlRequest {

	const TYPE_LOAD = 0;
	const TYPE_SAVE = 1;
	const TYPE_STRAY = 2;

	public $id;

	public $queries = [];

	public function __construct(string $id, MySqlQuery|array $queries) {
		$this->id = $id;
		$this->queries = is_array($queries) ? $queries : [$queries];
	}

	public function getId(): string {
		return $this->id;
	}

	public function getQueries(): array|ThreadSafeArray {
		return $this->queries;
	}

	public function addQuery(MySqlQuery $query): void {
		$this->queries[$query->getId()] = $query;
	}

	public function getQuery(string $id = "main"): ?MySqlQuery {
		foreach ($this->queries as $query) {
			if ($query->getId() == $id) return $query;
		}
		return null;
	}
}
