<?php

declare(strict_types=1);

namespace pocketmine\world\object\tree;

use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;
use function abs;

class TallRedwoodTree extends RedwoodTree{

	public function __construct(Random $random, BlockTransaction $transaction){
		parent::__construct($random, $transaction);

		$this->setHeight($random->nextBoundedInt(5) + 7);
		$this->setLeavesHeight($this->height - $random->nextBoundedInt(2) - 3);
		$this->setMaxRadius($random->nextBoundedInt($this->height - $this->leavesHeight + 1) + 1);
	}

	public function generate(ChunkManager $world, Random $random, int $sourceX, int $sourceY, int $sourceZ) : bool{
		if($this->cannotGenerateAt($sourceX, $sourceY, $sourceZ, $world)){
			return false;
		}

		// generate the leaves
		$radius = 0;
		for($y = $sourceY + $this->height; $y >= $sourceY + $this->leavesHeight; --$y){
			// leaves are built from top to bottom
			for($x = $sourceX - $radius; $x <= $sourceX + $radius; ++$x){
				for($z = $sourceZ - $radius; $z <= $sourceZ + $radius; ++$z){
					if(
						(
							abs($x - $sourceX) !== $radius ||
							abs($z - $sourceZ) !== $radius ||
							$radius <= 0
						) &&
						$world->getBlockAt($x, $y, $z)->getTypeId() === BlockTypeIds::AIR
					){
						$this->transaction->addBlockAt($x, $y, $z, $this->leavesType);
					}
				}
			}
			if($radius >= 1 && $y === $sourceY + $this->leavesHeight + 1){
				--$radius;
			}elseif($radius < $this->maxRadius){
				++$radius;
			}
		}

		// generate the trunk
		for($y = 0; $y < $this->height - 1; ++$y){
			$this->replaceIfAirOrLeaves($sourceX, $sourceY + $y, $sourceZ, $this->logType, $world);
		}

		$this->transaction->addBlockAt($sourceX, $sourceY - 1, $sourceZ, VanillaBlocks::DIRT());
		return true;
	}
}
