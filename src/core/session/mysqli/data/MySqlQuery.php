<?php

namespace core\session\mysqli\data;

use pmmp\thread\ThreadSafeArray;

use poggit\libasynql\SqlResult;

class MySqlQuery {

	public $id;

	public $query;
	public $parameters;

	public $executed = false;
	public $result = null;

	public function __construct(string $id, string $query, array $parameters = []) {
		$this->id = $id;

		$this->query = $query;
		$this->parameters = $parameters;
	}

	public function getId(): string {
		return $this->id;
	}

	public function getQuery(): string {
		return $this->query;
	}

	public function getParameters(): array|ThreadSafeArray {
		return $this->parameters;
	}

	public function hasExecuted(): bool {
		return $this->executed;
	}

	public function setExecuted(bool $executed = true): void {
		$this->executed = $executed;
	}

	/*
	 * Will only have a result if used for loading (select statement)
	 */
	public function getResult(): ?SqlResult {
		return $this->result;
	}

	public function hasResult(): bool {
		return $this->getResult() !== null;
	}

	public function setResult(?SqlResult $result = null): void {
		$this->result = $result;
	}
}
