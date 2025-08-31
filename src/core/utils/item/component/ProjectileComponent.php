<?php

namespace core\utils\item\component;

/**
 * From Customies
 */
final class ProjectileComponent implements ItemComponent {

	private string $projectileEntity;

	public function __construct(string $projectileEntity) {
		$this->projectileEntity = $projectileEntity;
	}

	public function getName(): string {
		return "minecraft:projectile";
	}

	public function getValue(): array {
		return [
			"projectile_entity" => $this->projectileEntity
		];
	}

	public function isProperty(): bool {
		return false;
	}
}