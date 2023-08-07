<?php

declare(strict_types=1);

namespace pocketmine\world\object\tree;

use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;
use pocketmine\world\World;
use function abs;
use function array_key_exists;

class RedwoodTree extends GenericTree{

	protected int $maxRadius;
	protected int $leavesHeight;

	public function __construct(Random $random, BlockTransaction $transaction){
		parent::__construct($random, $transaction);
		$this->setHeight($random->nextBoundedInt(4) + 6);
		$this->setLeavesHeight($random->nextBoundedInt(2) + 1);
		$this->setMaxRadius($random->nextBoundedInt(2) + 2);
		$this->setType(VanillaBlocks::SPRUCE_LOG(), VanillaBlocks::SPRUCE_LEAVES());
	}

	final protected function setMaxRadius(int $maxRadius) : void{
		$this->maxRadius = $maxRadius;
	}

	final protected function setLeavesHeight(int $leavesHeight) : void{
		$this->leavesHeight = $leavesHeight;
	}

	public function canPlace(int $baseX, int $baseY, int $baseZ, ChunkManager $world) : bool{
		for($y = $baseY; $y <= $baseY + 1 + $this->height; ++$y){
			if($y - $baseY < $this->leavesHeight){
				$radius = 0; // radius is 0 for trunk below leaves
			}else{
				$radius = $this->maxRadius;
			}
			// check for block collision on horizontal slices
			for($x = $baseX - $radius; $x <= $baseX + $radius; ++$x){
				for($z = $baseZ - $radius; $z <= $baseZ + $radius; ++$z){
					if($y >= World::Y_MIN && $y < World::Y_MAX){
						// we can overlap some blocks around
						if(!$this->canBeOverridden($world->getBlockAt($x, $y, $z))){
							return false;
						}
					}else{ // $this->height out of range
						return false;
					}
				}
			}
		}
		return true;
	}

	public function generate(ChunkManager $world, Random $random, int $sourceX, int $sourceY, int $sourceZ) : bool{
		if($this->cannotGenerateAt($sourceX, $sourceY, $sourceZ, $world)){
			return false;
		}

		// generate the leaves
		$radius = $random->nextBoundedInt(2);
		$peakRadius = 1;
		$minRadius = 0;
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
			if($radius >= $peakRadius){
				$radius = $minRadius;
				$minRadius = 1; // after the peak $radius is reached once, the min $radius increases
				++$peakRadius;  // the peak $radius increases each time it's reached
				if($peakRadius > $this->maxRadius){
					$peakRadius = $this->maxRadius;
				}
			}else{
				++$radius;
			}
		}

		// generate the trunk
		for($y = 0; $y < $this->height - $random->nextBoundedInt(3); $y++){
			if($this->canBeOverridden($world->getBlockAt($sourceX, $sourceY + $y, $sourceZ))){
				$this->transaction->addBlockAt($sourceX, $sourceY + $y, $sourceZ, $this->logType);
			}
		}

		$this->transaction->addBlockAt($sourceX, $sourceY - 1, $sourceZ, VanillaBlocks::DIRT());
		return true;
	}
}
