<?php

declare(strict_types=1);

namespace pocketmine\world\overworld\decorator;

use pocketmine\world\Decorator;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\Leaves;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class DeadBushDecorator extends Decorator{

	private const SOIL_TYPES = [BlockTypeIds::SAND, BlockTypeIds::DIRT, BlockTypeIds::HARDENED_CLAY, BlockTypeIds::STAINED_CLAY];

	public function decorate(ChunkManager $world, Random $random, int $chunkX, int $chunkZ, Chunk $chunk) : void{
		$sourceX = ($chunkX << 4) + $random->nextBoundedInt(16);
		$sourceZ = ($chunkZ << 4) + $random->nextBoundedInt(16);
		$sourceY = $random->nextBoundedInt($chunk->getHighestBlockAt($sourceX & 0x0f, $sourceZ & 0x0f) << 1);
		while($sourceY > 0
			&& ($world->getBlockAt($sourceX, $sourceY, $sourceZ)->getTypeId() === BlockTypeIds::AIR
				|| $world->getBlockAt($sourceX, $sourceY, $sourceZ) instanceof Leaves)){
			--$sourceY;
		}

		for($i = 0; $i < 4; ++$i){
			$x = $sourceX + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
			$z = $sourceZ + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
			$y = $sourceY + $random->nextBoundedInt(4) - $random->nextBoundedInt(4);

			if($world->getBlockAt($x, $y, $z)->getTypeId() === BlockTypeIds::AIR){
				$blockBelow = $world->getBlockAt($x, $y - 1, $z)->getTypeId();
				foreach(self::SOIL_TYPES as $soil){
					if($soil === $blockBelow){
						$world->setBlockAt($x, $y, $z, VanillaBlocks::DEAD_BUSH());
						break;
					}
				}
			}
		}
	}
}
