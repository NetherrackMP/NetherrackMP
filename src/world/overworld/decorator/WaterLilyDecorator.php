<?php

declare(strict_types=1);

namespace pocketmine\world\overworld\decorator;

use pocketmine\world\Decorator;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Water;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\World;

class WaterLilyDecorator extends Decorator{

	public function decorate(ChunkManager $world, Random $random, int $chunkX, int $chunkZ, Chunk $chunk) : void{
		$sourceX = ($chunkX << 4) + $random->nextBoundedInt(16);
		$sourceZ = ($chunkZ << 4) + $random->nextBoundedInt(16);
		$sourceY = $random->nextBoundedInt($chunk->getHighestBlockAt($sourceX & 0x0f, $sourceZ & 0x0f) << 1);
		while($world->getBlockAt($sourceX, $sourceY - 1, $sourceZ)->getTypeId() === BlockTypeIds::AIR && $sourceY > 0){
			--$sourceY;
		}

		for($j = 0; $j < 10; ++$j){
			$x = $sourceX + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
			$z = $sourceZ + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
			$y = $sourceY + $random->nextBoundedInt(4) - $random->nextBoundedInt(4);

			if(
				$y >= World::Y_MIN && $y < World::Y_MAX && $world->getBlockAt($x, $y, $z)->getTypeId() === BlockTypeIds::AIR &&
				$world->getBlockAt($x, $y - 1, $z) instanceof Water
			){
				$world->setBlockAt($x, $y, $z, VanillaBlocks::LILY_PAD());
			}
		}
	}
}
