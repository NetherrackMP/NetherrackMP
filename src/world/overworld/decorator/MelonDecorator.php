<?php

declare(strict_types=1);

namespace pocketmine\world\overworld\decorator;

use pocketmine\world\Decorator;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\World;

class MelonDecorator extends Decorator{

	public function decorate(ChunkManager $world, Random $random, int $chunkX, int $chunkZ, Chunk $chunk) : void{
		$sourceX = ($chunkX << 4) + $random->nextBoundedInt(16);
		$sourceZ = ($chunkZ << 4) + $random->nextBoundedInt(16);
		$seaLevel = 64;
		$sourceY = $random->nextBoundedInt($seaLevel << 1);

		for($i = 0; $i < 64; ++$i){
			$x = $sourceX + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
			$z = $sourceZ + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
			$y = $sourceY + $random->nextBoundedInt(4) - $random->nextBoundedInt(4);

			if(
				$world->getBlockAt($x, $y, $z)->getTypeId() === BlockTypeIds::AIR &&
				$world->getBlockAt($x, $y - 1, $z)->getTypeId() === BlockTypeIds::GRASS
			){
				$world->setBlockAt($x, $y, $z, VanillaBlocks::MELON());
			}
		}
	}
}
