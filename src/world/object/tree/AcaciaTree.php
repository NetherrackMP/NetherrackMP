<?php

declare(strict_types=1);

namespace pocketmine\world\object\tree;

use pocketmine\world\utils\MathHelper;
use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;
use pocketmine\world\generator\object\TreeType;
use function abs;

class AcaciaTree extends GenericTree{

	public function __construct(Random $random, BlockTransaction $transaction){
		parent::__construct($random, $transaction);
		$this->setHeight($random->nextBoundedInt(3) + $random->nextBoundedInt(3) + 5);
		$this->setType(VanillaBlocks::ACACIA_LOG(), VanillaBlocks::ACACIA_LEAVES());
	}

	public function canPlaceOn(Block $soil) : bool{
		$type = $soil->getTypeId();
		return $type === BlockTypeIds::GRASS || $type === BlockTypeIds::DIRT;
	}

	public function generate(ChunkManager $world, Random $random, int $sourceX, int $sourceY, int $sourceZ) : bool{
		if($this->cannotGenerateAt($sourceX, $sourceY, $sourceZ, $world)){
			return false;
		}

		$d = ($random->nextFloat() * M_PI * 2.0); // random direction
		$dx = (int) (MathHelper::getInstance()->cos($d) + 1.5) - 1;
		$dz = (int) (MathHelper::getInstance()->sin($d) + 1.5) - 1;
		if(abs($dx) > 0 && abs($dz) > 0){ // reduce possible directions to NESW
			if($random->nextBoolean()){
				$dx = 0;
			}else{
				$dz = 0;
			}
		}
		$twistHeight = $this->height - 1 - $random->nextBoundedInt(4);
		$twistCount = $random->nextBoundedInt(3) + 1;
		$centerX = $sourceX;
		$centerZ = $sourceZ;
		$trunkTopY = 0;
		// generates the trunk
		for($y = 0; $y < $this->height; ++$y){

			// trunk twists
			if($twistCount > 0 && $y >= $twistHeight){
				$centerX += $dx;
				$centerZ += $dz;
				--$twistCount;
			}

			$material = $world->getBlockAt($centerX, $sourceY + $y, $centerZ)->getTypeId();
			if($material === BlockTypeIds::AIR || $material === BlockTypeIds::ACACIA_LEAVES){
				$trunkTopY = $sourceY + $y;
				$this->transaction->addBlockAt($centerX, $sourceY + $y, $centerZ, $this->logType);
			}
		}

		// generates leaves
		for($x = -3; $x <= 3; ++$x){
			$absX = abs($x);
			for($z = -3; $z <= 3; ++$z){
				$absZ = abs($z);
				if($absX < 3 || $absZ < 3){
					$this->setLeaves($centerX + $x, $trunkTopY, $centerZ + $z, $world);
				}
				if($absX < 2 && $absZ < 2){
					$this->setLeaves($centerX + $x, $trunkTopY + 1, $centerZ + $z, $world);
				}
				if(($absX === 2 && $absZ === 0) || ($absX === 0 && $absZ === 2)){
					$this->setLeaves($centerX + $x, $trunkTopY + 1, $centerZ + $z, $world);
				}
			}
		}

		// try to choose a different direction for second branching and canopy
		$d = $random->nextFloat() * M_PI * 2.0;
		$dxB = (int) (MathHelper::getInstance()->cos($d) + 1.5) - 1;
		$dzB = (int) (MathHelper::getInstance()->sin($d) + 1.5) - 1;
		if(abs($dxB) > 0 && abs($dzB) > 0){
			if($random->nextBoolean()){
				$dxB = 0;
			}else{
				$dzB = 0;
			}
		}
		if($dx !== $dxB || $dz !== $dzB){
			$centerX = $sourceX;
			$centerZ = $sourceZ;
			$branchHeight = $twistHeight - 1 - $random->nextBoundedInt(2);
			$twistCount = $random->nextBoundedInt(3) + 1;
			$trunkTopY = 0;

			// generates the trunk
			for($y = $branchHeight + 1; $y < $this->height; ++$y){
				if($twistCount > 0){
					$centerX += $dxB;
					$centerZ += $dzB;
					$material = $world->getBlockAt($centerX, $sourceY + $y, $centerZ)->getTypeId();
					if($material === BlockTypeIds::AIR || $material === BlockTypeIds::ACACIA_LEAVES){
						$trunkTopY = $sourceY + $y;
						$this->transaction->addBlockAt($centerX, $sourceY + $y, $centerZ, $this->logType);
					}
					--$twistCount;
				}
			}

			// generates the leaves
			if($trunkTopY > 0){
				for($x = -2; $x <= 2; ++$x){
					for($z = -2; $z <= 2; ++$z){
						if(abs($x) < 2 || abs($z) < 2){
							$this->setLeaves($centerX + $x, $trunkTopY, $centerZ + $z, $world);
						}
					}
				}
				for($x = -1; $x <= 1; ++$x){
					for($z = -1; $z <= 1; ++$z){
						$this->setLeaves($centerX + $x, $trunkTopY + 1, $centerZ + $z, $world);
					}
				}
			}
		}

		$this->transaction->addBlockAt($sourceX, $sourceY - 1, $sourceZ, VanillaBlocks::DIRT());

		return true;
	}

	private function setLeaves(int $x, int $y, int $z, ChunkManager $world) : void{
		if($world->getBlockAt($x, $y, $z)->getTypeId() === BlockTypeIds::AIR){
			$this->transaction->addBlockAt($x, $y, $z, $this->leavesType);
		}
	}
}
