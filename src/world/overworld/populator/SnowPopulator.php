<?php

declare(strict_types=1);

namespace pocketmine\world\overworld\populator;

use pocketmine\world\overworld\biome\BiomeClimateManager;
use pocketmine\world\Populator;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use function in_array;

class SnowPopulator implements Populator {
	public function populate(ChunkManager $world, Random $random, int $chunkX, int $chunkZ, Chunk $chunk) : void {
		$disallowedBlocks = [
			VanillaBlocks::WATER()->getStateId(),
			VanillaBlocks::WATER()->setStill()->getStateId(),
			VanillaBlocks::SNOW()->getStateId(),
			VanillaBlocks::ICE()->getStateId(),
			VanillaBlocks::PACKED_ICE()->getStateId(),
			VanillaBlocks::DANDELION()->getStateId(),
			VanillaBlocks::POPPY()->getStateId(),
			VanillaBlocks::DOUBLE_TALLGRASS()->getStateId(),
			VanillaBlocks::LARGE_FERN()->getStateId(),
			VanillaBlocks::BROWN_MUSHROOM()->getStateId(),
			VanillaBlocks::RED_MUSHROOM()->getStateId(),
			VanillaBlocks::ROSE_BUSH()->getStateId(),
			VanillaBlocks::LARGE_FERN()->getStateId(),
			VanillaBlocks::SUGARCANE()->getStateId(),
			VanillaBlocks::TALL_GRASS()->getStateId(),
			VanillaBlocks::LAVA()->getStateId(),
			VanillaBlocks::LAVA()->setStill()->getStateId(),
		];

		$dirt = VanillaBlocks::DIRT()->getStateId();
		$grass = VanillaBlocks::GRASS()->getStateId();
		$snow = VanillaBlocks::SNOW_LAYER()->getStateId();

		$sourceX = $chunkX << 4;
		$sourceZ = $chunkZ << 4;
		for($x = 0; $x < 16; ++$x) {
			for($z = 0; $z < 16; ++$z) {
				$y = ($chunk->getHighestBlockAt($x, $z) ?? 0);
				if(BiomeClimateManager::isSnowy($chunk->getBiomeId($x, $y, $z), $sourceX + $x, $y, $sourceZ + $z)) {
					$block = $chunk->getBlockStateId($x, $y, $z);
					if(in_array($block, $disallowedBlocks, true)) {
						continue;
					}

					if($block === $dirt) {
						$chunk->setBlockStateId($x, $y, $z, $grass);
					}
					$chunk->setBlockStateId($x, $y + 1, $z, $snow);
				}
			}
		}
	}
}
