<?php

declare(strict_types=1);

namespace pocketmine\world\nether\decorator;

use pocketmine\world\Decorator;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class GlowstoneDecorator extends Decorator{

	private const SIDES = [Facing::EAST, Facing::WEST, Facing::DOWN, Facing::UP, Facing::SOUTH, Facing::NORTH];

	private bool $variableAmount;

	public function __construct(bool $variableAmount = false){
		$this->variableAmount = $variableAmount;
	}

	public function decorate(ChunkManager $world, Random $random, int $chunkX, int $chunkZ, Chunk $chunk) : void{
		$amount = $this->variableAmount ? 1 + $random->nextBoundedInt(1 + $random->nextBoundedInt(10)) : 10;

		$height = $world->getMaxY();
		$sourceYMargin = 8 * ($height >> 7);

		for($i = 0; $i < $amount; ++$i){
			$sourceX = ($chunkX << 4) + $random->nextBoundedInt(16);
			$sourceZ = ($chunkZ << 4) + $random->nextBoundedInt(16);
			$sourceY = 4 + $random->nextBoundedInt($height - $sourceYMargin);

			$block = $world->getBlockAt($sourceX, $sourceY, $sourceZ);
			if(
				$block->getTypeId() !== BlockTypeIds::AIR ||
				$world->getBlockAt($sourceX, $sourceY + 1, $sourceZ)->getTypeId() !== BlockTypeIds::NETHERRACK
			){
				continue;
			}

			$world->setBlockAt($sourceX, $sourceY, $sourceZ, VanillaBlocks::GLOWSTONE());

			for($j = 0; $j < 1500; ++$j){
				$x = $sourceX + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
				$z = $sourceZ + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
				$y = $sourceY - $random->nextBoundedInt(12);
				$block = $world->getBlockAt($x, $y, $z);
				if($block->getTypeId() !== BlockTypeIds::AIR){
					continue;
				}

				$glowstoneBlockCount = 0;
				$vector = new Vector3($x, $y, $z);
				foreach(self::SIDES as $face){
					$pos = $vector->getSide($face);
					if($world->getBlockAt($pos->x, $pos->y, $pos->z)->getTypeId() === BlockTypeIds::GLOWSTONE){
						++$glowstoneBlockCount;
					}
				}

				if($glowstoneBlockCount === 1){
					$world->setBlockAt($x, $y, $z, VanillaBlocks::GLOWSTONE());
				}
			}
		}
	}
}
