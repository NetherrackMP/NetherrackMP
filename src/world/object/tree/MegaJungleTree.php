<?php

declare(strict_types=1);

namespace pocketmine\world\object\tree;

use pocketmine\world\utils\MathHelper;
use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Facing;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;
use pocketmine\world\World;

class MegaJungleTree extends GenericTree{

	/**
	 * Initializes this tree with a random height, preparing it to attempt to generate.
	 */
	public function __construct(Random $random, BlockTransaction $transaction){
		parent::__construct($random, $transaction);
		$this->setHeight($random->nextBoundedInt(20) + $random->nextBoundedInt(3) + 10);
		$this->setType(VanillaBlocks::JUNGLE_LOG(), VanillaBlocks::JUNGLE_LEAVES());
	}

	public function canPlaceOn(Block $soil) : bool{
		$id = $soil->getTypeId();
		return $id === BlockTypeIds::GRASS || $id === BlockTypeIds::DIRT;
	}

	public function canPlace(int $baseX, int $baseY, int $baseZ, ChunkManager $world) : bool{
		for($y = $baseY; $y <= $baseY + 1 + $this->height; ++$y){
			// Space requirement
			$radius = 2; // default radius if above first block
			if($y === $baseY){
				$radius = 1; // radius at source block y is 1 (only trunk)
			}
			// check for block collision on horizontal slices
			for($x = $baseX - $radius; $x <= $baseX + $radius; ++$x){
				for($z = $baseZ - $radius; $z <= $baseZ + $radius; ++$z){
					if($y >= World::Y_MIN && $y < World::Y_MAX){
						// we can overlap some blocks around
						if(!$this->canBeOverridden($world->getBlockAt($x, $y, $z))){
							return false;
						}
					}else{ // height out of range
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

		// generates the canopy leaves
		for($y = -2; $y <= 0; ++$y){
			$this->generateLeaves($sourceX, $sourceY + $this->height + $y, $sourceZ, 3 - $y, false, $world);
		}

		// generates the branches
		$branchHeight = $this->height - 2 - $random->nextBoundedInt(4);
		while($branchHeight > $this->height / 2){ // branching start at least at middle height
			/** @noinspection PhpUnusedLocalVariableInspection */
			$x = 0;
			/** @noinspection PhpUnusedLocalVariableInspection */
			$z = 0;
			// generates a branch
			$d = $random->nextFloat() * M_PI * 2.0; // random direction
			for($i = 0; $i < 5; ++$i){
				// branches are always longer when facing south or east (positive X or positive Z)
				$x = (int) (MathHelper::getInstance()->cos($d) * $i + 1.5);
				$z = (int) (MathHelper::getInstance()->sin($d) * $i + 1.5);
				$this->transaction->addBlockAt($sourceX + $x, (int) ($sourceY + $branchHeight - 3 + $i / 2), $sourceZ + $z, $this->logType);
			}
			// generates leaves for this branch
			for($y = $branchHeight - ($random->nextBoundedInt(2) + 1); $y <= $branchHeight; ++$y){
				$this->generateLeaves($sourceX + $x, $sourceY + $y, $sourceZ + $z, 1 - ($y - $branchHeight), true, $world);
			}
			$branchHeight -= $random->nextBoundedInt(4) + 2;
		}

		// generates the trunk
		$this->generateTrunk($world, $sourceX, $sourceY, $sourceZ);

		// add some vines on the trunk
		$this->addVinesOnTrunk($world, $sourceX, $sourceY, $sourceZ, $random);

		// blocks below trunk are always dirt
		$this->generateDirtBelowTrunk($sourceX, $sourceY, $sourceZ);
		return true;
	}

	protected function generateLeaves(int $sourceX, int $sourceY, int $sourceZ, int $radius, bool $odd, ChunkManager $world) : void{
		$n = 1;
		if($odd){
			$n = 0;
		}
		for($x = $sourceX - $radius; $x <= $sourceX + $radius + $n; ++$x){
			$radiusX = $x - $sourceX;
			for($z = $sourceZ - $radius; $z <= $sourceZ + $radius + $n; ++$z){
				$radiusZ = $z - $sourceZ;

				$sqX = $radiusX * $radiusX;
				$sqZ = $radiusZ * $radiusZ;
				$sqR = $radius * $radius;
				$sqXb = ($radiusX - $n) * ($radiusX - $n);
				$sqZb = ($radiusZ - $n) * ($radiusZ - $n);

				if($sqX + $sqZ <= $sqR || $sqXb + $sqZb <= $sqR || $sqX + $sqZb <= $sqR || $sqXb + $sqZ <= $sqR){
					$this->replaceIfAirOrLeaves($x, $sourceY, $z, $this->leavesType, $world);
				}
			}
		}
	}

	protected function generateTrunk(ChunkManager $world, int $blockX, int $blockY, int $blockZ) : void{
		// SELF, SOUTH, EAST, SOUTH EAST
		for($y = 0; $y < $this->height + -1; ++$y){
			$type = $world->getBlockAt($blockX, $blockY + $y, $blockZ)->getTypeId();
			if($type === BlockTypeIds::AIR || $type === BlockTypeIds::JUNGLE_LEAVES){
				$this->transaction->addBlockAt($blockX, $blockY + $y, $blockZ, $this->logType);
			}
			$type = $world->getBlockAt($blockX, $blockY + $y, $blockZ + 1)->getTypeId();
			if($type === BlockTypeIds::AIR || $type === BlockTypeIds::JUNGLE_LEAVES){
				$this->transaction->addBlockAt($blockX, $blockY + $y, $blockZ + 1, $this->logType);
			}
			$type = $world->getBlockAt($blockX + 1, $blockY + $y, $blockZ)->getTypeId();
			if($type === BlockTypeIds::AIR || $type === BlockTypeIds::JUNGLE_LEAVES){
				$this->transaction->addBlockAt($blockX + 1, $blockY + $y, $blockZ, $this->logType);
			}
			$type = $world->getBlockAt($blockX + 1, $blockY + $y, $blockZ + 1)->getTypeId();
			if($type === BlockTypeIds::AIR || $type === BlockTypeIds::JUNGLE_LEAVES){
				$this->transaction->addBlockAt($blockX + 1, $blockY + $y, $blockZ + 1, $this->logType);
			}
		}
	}

	protected function generateDirtBelowTrunk(int $blockX, int $blockY, int $blockZ) : void{
		// SELF, SOUTH, EAST, SOUTH EAST
		$dirt = VanillaBlocks::DIRT();
		$this->transaction->addBlockAt($blockX, $blockY + -1, $blockZ, $dirt);
		$this->transaction->addBlockAt($blockX, $blockY + -1, $blockZ + 1, $dirt);
		$this->transaction->addBlockAt($blockX + 1, $blockY + -1, $blockZ, $dirt);
		$this->transaction->addBlockAt($blockX + 1, $blockY + -1, $blockZ + 1, $dirt);
	}

	private function addVinesOnTrunk(ChunkManager $world, int $blockX, int $blockY, int $blockZ, Random $random) : void{
		for($y = 1; $y < $this->height; ++$y){
			$this->maybePlaceVine($world, $blockX + -1, $blockY + $y, $blockZ, Facing::EAST, $random);
			$this->maybePlaceVine($world, $blockX, $blockY + $y, $blockZ + -1, Facing::SOUTH, $random);
			$this->maybePlaceVine($world, $blockX + 2, $blockY + $y, $blockZ, Facing::WEST, $random);
			$this->maybePlaceVine($world, $blockX + 1, $blockY + $y, $blockZ + -1, Facing::SOUTH, $random);
			$this->maybePlaceVine($world, $blockX + 2, $blockY + $y, $blockZ + 1, Facing::WEST, $random);
			$this->maybePlaceVine($world, $blockX + 1, $blockY + $y, $blockZ + 2, Facing::NORTH, $random);
			$this->maybePlaceVine($world, $blockX + -1, $blockY + $y, $blockZ + 1, Facing::EAST, $random);
			$this->maybePlaceVine($world, $blockX, $blockY + $y, $blockZ + 2, Facing::NORTH, $random);
		}
	}

	private function maybePlaceVine(ChunkManager $world, int $absoluteX, int $absoluteY, int $absoluteZ, int $faceDirection, Random $random) : void{
		if(
			$random->nextBoundedInt(3) !== 0 &&
			$world->getBlockAt($absoluteX, $absoluteY, $absoluteZ)->getTypeId() === BlockTypeIds::AIR
		){
			$this->transaction->addBlockAt($absoluteX, $absoluteY, $absoluteZ, VanillaBlocks::VINES()->setFace($faceDirection, true));
		}
	}

}
