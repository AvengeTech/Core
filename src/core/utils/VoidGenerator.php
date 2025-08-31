<?php

namespace core\utils;

use pocketmine\block\{
	VanillaBlocks
};
use pocketmine\world\ChunkManager;
use pocketmine\world\generator\Generator;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

class VoidGenerator extends Generator{

	/** @var ChunkManager $world */
	protected $world;

	/** @var array $options */
	private $options;


	/**
	 * @return array
	 */
	public function getSettings(): array {
		return [];
	}

	/**
	 * @return string
	 */
	public function getName(): string {
		return "void";
	}

	/**
	 * VoidGenerator constructor.
	 *
	 * @param int $seed
	 * @param string $preset
	 */
	public function __construct(int $seed, string $preset) {
		parent::__construct($seed, $preset);
	}

	/**
	 * @param ChunkManager $world
	 * @param Random	   $random
	 *
	 * @return mixed|void
	 */
	public function init(ChunkManager $world, Random $random): void {
		$this->world = $world;
		$this->random = $random;
	}

	/**
	 * @param int $chunkX
	 * @param int $chunkZ
	 */
	public function generateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void {
		$chunk = $world->getChunk($chunkX, $chunkZ);
		if ($chunkX == 16 && $chunkZ == 16) {
			$chunk->setBlockStateId(0, 64, 0, VanillaBlocks::GRASS()->getStateId());
		}
	}

	/**
	 * @param ChunkManager $world
	 * @param $chunkX
	 * @param $chunkZ
	 *
	 * @return mixed|void
	 */
	public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ): void {
	}

	/**
	 * @return Vector3
	 */
	/**public function getSpawn(): Vector3{
		return new Vector3(256, 65, 256);
	}*/
}
