<?php

namespace core\session\mysqli\data;

class MySqlUtils {

	const CREDENTIALS = "[REDACTED]";

	public static function generateCredentials(string $databaseName, bool $lib = false): array {
		$creds = array_merge(file(self::CREDENTIALS), [$databaseName]);
		foreach ($creds as $key => $cred) $creds[$key] = trim(str_replace("\n", "", $cred));
		if ($lib) {
			$creds = [
				"type" => "mysql",
				"mysql" => [
					"host" => $creds[0],
					"username" => $creds[1],
					"password" => $creds[2],
					"schema" => $creds[3]
				],
				"worker-limit" => 2
			];
		}
		return $creds;
	}
}
