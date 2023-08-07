<?php

declare(strict_types=1);

namespace pocketmine\world\object\tree;

use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Water;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\World;
use function abs;
use function array_key_exists;

class SwampTree extends CocoaTree{

	public function __construct(Random $random, BlockTransaction $transaction){
		parent::__construct($random, $transaction);
		$this->setHeight($random->nextBoundedInt(4) + 5);
		$this->setType(VanillaBlocks::OAK_LOG(), VanillaBlocks::OAK_LEAVES());
	}

	public function canPlaceOn(Block $soil) : bool{
		$id = $soil->getTypeId();
		return $id === BlockTypeIds::GRASS || $id === BlockTypeIds::DIRT;
	}

	public function canPlace(int $baseX, int $baseY, int $baseZ, ChunkManager $world) : bool{
		for($y = $baseY; $y <= $baseY + 1 + $this->height; ++$y){
			if($y < 0 || $y >= World::Y_MAX){ // height out of range
				return false;
			}

			// Space requirement
			$radius = 1; // default radius if above first block
			if($y === $baseY){
				$radius = 0; // radius at source block y is 0 (only trunk)
			}elseif($y >= $baseY + 1 + $this->height - 2){
				$radius = 3; // max radius starting at leaves bottom
			}
			// check for block collision on horizontal slices
			for($x = $baseX - $radius; $x <= $baseX + $radius; ++$x){
				for($z = $baseZ - $radius; $z <= $baseZ + $radius; ++$z){
					// we can overlap some blocks around
					$type = $world->getBlockAt($x, $y, $z);
					if($this->canBeOverridden($type)){
						continue;
					}

					if($type instanceof Water){
						if($y > $baseY){
							return false;
						}
					}else{
						return false;
					}
				}
			}
		}
		return true;
	}

	public function generate(ChunkManager $world, Random $random, int $sourceX, int $sourceY, int $sourceZ) : bool{
		/** @var Chunk $chunk */
		$chunk = $world->getChunk($sourceX >> 4, $sourceZ >> 4);
		$chunkBlockX = $sourceX & 0x0f;
		$chunkBlockZ = $sourceZ & 0x0f;
		$registry = RuntimeBlockStateRegistry::getInstance();
		while($registry->fromStateId($chunk->getBlockStateId($chunkBlockX, $sourceY, $chunkBlockZ)) instanceof Water){
			--$sourceY;
		}

		++$sourceY;
		if($this->cannotGenerateAt($sourceX, $sourceY, $sourceZ, $world)){
			return false;
		}

		// generate the leaves
		for($y = $sourceY + $this->height - 3; $y <= $sourceY + $this->height; ++$y){
			$n = $y - ($sourceY + $this->height);
			$radius = (int) (2 - $n / 2);
			for($x = $sourceX - $radius; $x <= $sourceX + $radius; ++$x){
				for($z = $sourceZ - $radius; $z <= $sourceZ + $radius; ++$z){
					if(
						abs($x - $sourceX) !== $radius ||
						abs($z - $sourceZ) !== $radius ||
						($random->nextBoolean() && $n !== 0)
					){
						$this->replaceIfAirOrLeaves($x, $y, $z, $this->leavesType, $world);
					}
				}
			}
		}

		$worldHeight = $world->getMaxY();
		// generate the trunk
		for($y = 0; $y < $this->height; ++$y){
			if($sourceY + $y < $worldHeight){
				$material = $registry->fromStateId($chunk->getBlockStateId($chunkBlockX, $sourceY + $y, $chunkBlockZ))->getTypeId();
				if(
					$material === BlockTypeIds::AIR ||
					$material === BlockTypeIds::OAK_LEAVES ||
					$material === BlockTypeIds::WATER
				){
					$this->transaction->addBlockAt($sourceX, $sourceY + $y, $sourceZ, $this->logType);
				}
			}
		}

		// add some vines on the leaves
		$this->addVinesOnLeaves($sourceX, $sourceY, $sourceZ, $world, $random);

		$this->transaction->addBlockAt($sourceX, $sourceY - 1, $sourceZ, VanillaBlocks::DIRT());
		return true;
	}
}
