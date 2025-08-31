<?php

namespace core\utils;

class Version{

	public function __construct(
		public int $major,
		public int $minor,
		public int $min
	) {
	}

	public function getMajor(): int {
		return $this->major;
	}

	public function getMinor(): int {
		return $this->minor;
	}

	public function getMin(): int {
		return $this->min;
	}

	public function equals(Version|string $version): bool {
		if (!$version instanceof Version) $version = Version::fromString($version);
		return
			$this->getMajor() === $version->getMajor() &&
			$this->getMinor() === $version->getMinor() &&
			$this->getMin() === $version->getMin();
	}

	public function newerThan(Version|string $version): bool {
		if (!$version instanceof Version) $version = Version::fromString($version);
		return
			$this->getMajor() > $version->getMajor() ||
			($this->getMajor() == $version->getMajor() &&
				$this->getMinor() > $version->getMinor()
			) || ($this->getMajor() == $version->getMajor() &&
				$this->getMinor() == $version->getMinor() &&
				$this->getMin() > $version->getMin()
			);
	}

	public static function fromString(string $version): ?Version {
		$version = explode(".", $version);
		if (count($version) == 3) return new Version(...$version);
		return null;
	}

	public function toString(): string {
		return $this->getMajor() . "." . $this->getMinor() . "." . $this->getMin();
	}

	public function __toString(): string {
		return $this->toString();
	}
}
