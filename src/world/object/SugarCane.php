<?php

declare(strict_types=1);

namespace pocketmine\world\object;

use pocketmine\block\BlockTypeIds;
use pocketmine\block\Dirt;
use pocketmine\block\utils\DirtType;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Water;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;

class SugarCane extends TerrainObject{

	private const FACES = [Facing::NORTH, Facing::EAST, Facing::SOUTH, Facing::WEST];

	public function generate(ChunkManager $world, Random $random, int $sourceX, int $sourceY, int $sourceZ) : bool{
		if($world->getBlockAt($sourceX, $sourceY, $sourceZ)->getTypeId() !== BlockTypeIds::AIR){
			return false;
		}

		$vec = new Vector3($sourceX, $sourceY - 1, $sourceZ);
		$adjacentWater = false;
		foreach(self::FACES as $face){
			// needs directly adjacent water block
			$blockTypeV = $vec->getSide($face);
			$block = $world->getBlockAt($blockTypeV->x, $blockTypeV->y, $blockTypeV->z);
			if($block instanceof Water){
				$adjacentWater = true;
				break;
			}
		}
		if(!$adjacentWater){
			return false;
		}
		for($n = 0; $n <= $random->nextBoundedInt($random->nextBoundedInt(3) + 1) + 1; ++$n){
			$block = $world->getBlockAt($sourceX, $sourceY + $n - 1, $sourceZ);
			$blockId = $block->getTypeId();
			if($blockId === BlockTypeIds::SUGARCANE
				|| $blockId === BlockTypeIds::GRASS
				|| $blockId === BlockTypeIds::SAND
				|| ($block instanceof Dirt && !$block->getDirtType()->equals(DirtType::COARSE()))
			){
				$caneBlock = $world->getBlockAt($sourceX, $sourceY + $n, $sourceZ);
				if($caneBlock->getTypeId() !== BlockTypeIds::AIR && $world->getBlockAt($sourceX, $sourceY + $n + 1, $sourceZ)->getTypeId() !== BlockTypeIds::AIR){
					return $n > 0;
				}

				$world->setBlockAt($sourceX, $sourceY + $n, $sourceZ, VanillaBlocks::SUGARCANE());
			}
		}
		return true;
	}
}
