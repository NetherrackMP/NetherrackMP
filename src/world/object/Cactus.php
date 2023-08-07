<?php

declare(strict_types=1);

namespace pocketmine\world\object;

use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;

class Cactus extends TerrainObject{

	private const FACES = [Facing::NORTH, Facing::EAST, Facing::SOUTH, Facing::WEST];

	/**
	 * Generates or extends a cactus, if there is space.
	 */
	public function generate(ChunkManager $world, Random $random, int $sourceX, int $sourceY, int $sourceZ) : bool{
		if($world->getBlockAt($sourceX, $sourceY, $sourceZ)->getTypeId() === BlockTypeIds::AIR){
			$height = $random->nextBoundedInt($random->nextBoundedInt(3) + 1) + 1;
			for($n = $sourceY; $n < $sourceY + $height; ++$n){
				$vec = new Vector3($sourceX, $n, $sourceZ);
				$typeBelow = $world->getBlockAt($sourceX, $n - 1, $sourceZ)->getTypeId();
				if(($typeBelow === BlockTypeIds::SAND || $typeBelow === BlockTypeIds::CACTUS) && $world->getBlockAt($sourceX, $n + 1, $sourceZ)->getTypeId() === BlockTypeIds::AIR){
					foreach(self::FACES as $face){
						$face = $vec->getSide($face);
						if($world->getBlockAt($face->x, $face->y, $face->z)->isSolid()){
							return $n > $sourceY;
						}
					}

					$world->setBlockAt($sourceX, $n, $sourceZ, VanillaBlocks::CACTUS());
				}
			}
		}
		return true;
	}
}
