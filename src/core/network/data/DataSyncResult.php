<?php

namespace core\network\data;

class DataSyncResult {

	public function __construct(
		public array $rows
	) {
	}

	public function getRows(): array {
		$validRows = true;
		foreach ($this->rows as $key => $row) {
			if (!is_array($row)) {
				$validRows = false;
				break;
			}
		}
		if (!$validRows) $this->rows = [$this->rows];
		return $this->rows;
	}
}
