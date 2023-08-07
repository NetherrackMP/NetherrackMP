<?php

declare(strict_types=1);

namespace pocketmine\world\object;

use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\Leaves;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;

class TallGrass extends TerrainObject{

	private Block $grassType;

	public function __construct(Block $grassType){
		$this->grassType = $grassType;
	}

	public function generate(ChunkManager $world, Random $random, int $sourceX, int $sourceY, int $sourceZ) : bool{
		do{
			$thisBlock = $world->getBlockAt($sourceX, $sourceY, $sourceZ);
			--$sourceY;
		}while(($thisBlock instanceof Air || $thisBlock instanceof Leaves) && $sourceY > 0);
		++$sourceY;
		$succeeded = false;
		$height = $world->getMaxY();
		for($i = 0; $i < 128; ++$i){
			$x = $sourceX + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
			$z = $sourceZ + $random->nextBoundedInt(8) - $random->nextBoundedInt(8);
			$y = $sourceY + $random->nextBoundedInt(4) - $random->nextBoundedInt(4);

			$blockType = $world->getBlockAt($x, $y, $z)->getTypeId();
			$blockTypeBelow = $world->getBlockAt($x, $y - 1, $z)->getTypeId();
			if($y < $height && $blockType === BlockTypeIds::AIR && ($blockTypeBelow === BlockTypeIds::GRASS || $blockTypeBelow === BlockTypeIds::DIRT)){
				$world->setBlockAt($x, $y, $z, $this->grassType);
				$succeeded = true;
			}
		}
		return $succeeded;
	}
}
