<?php

declare(strict_types=1);

namespace pocketmine\world\object\tree;

use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\Leaves;
use pocketmine\block\RedMushroomBlock;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\block\utils\MushroomBlockType;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;

class BrownMushroomTree extends GenericTree{

	protected MushroomBlockType $type;

	/**
	 * Initializes this mushroom with a random height, preparing it to attempt to generate.
	 */
	public function __construct(Random $random, BlockTransaction $transaction){
		parent::__construct($random, $transaction);
		$this->setHeight($random->nextBoundedInt(3) + 4);
	}

	public function canPlaceOn(Block $soil) : bool{
		$id = $soil->getTypeId();
		return $id === BlockTypeIds::GRASS || $id === BlockTypeIds::DIRT || $id === BlockTypeIds::MYCELIUM;
	}

	protected function getType() : RedMushroomBlock{
		return VanillaBlocks::BROWN_MUSHROOM_BLOCK();
	}

	public function canPlace(int $baseX, int $baseY, int $baseZ, ChunkManager $world) : bool{
		$worldHeight = $world->getMaxY();
		for($y = $baseY; $y <= $baseY + 1 + $this->height; ++$y){
			// Space requirement is 7x7 blocks, so brown mushroom's cap
			// can be directly touching a mushroom next to it.
			// Since red mushrooms fits in 5x5 blocks it will never
			// touch another huge mushroom.
			$radius = 3;
			if($y <= $baseY + 3){
				$radius = 0; // radius is 0 below 4 blocks tall (only the stem to take in account)
			}

			// check for block collision on horizontal slices
			for($x = $baseX - $radius; $x <= $baseX + $radius; ++$x){
				for($z = $baseZ - $radius; $z <= $baseZ + $radius; ++$z){
					if($y < 0 || $y >= $worldHeight){ // height out of range
						return false;
					}
					// skip source block check
					if($y !== $baseY || $x !== $baseX || $z !== $baseZ){
						// we can overlap leaves around
						if(!$this->canBeOverridden($world->getBlockAt($x, $y, $z))){
							return false;
						}
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

		$registry = RuntimeBlockStateRegistry::getInstance();

		// generate the stem
		// generate the stem
		$stem = VanillaBlocks::MUSHROOM_STEM();
		for($y = 0; $y < $this->height; ++$y){
			$this->transaction->addBlockAt($sourceX, $sourceY + $y, $sourceZ, $stem);
		}

		$typeId = $this->getType()->getTypeId();
		// get the mushroom's cap Y start
		$capY = $sourceY + $this->height; // for brown mushroom it starts on top directly
		if($typeId === BlockTypeIds::RED_MUSHROOM_BLOCK){
			$capY = $sourceY + $this->height - 3; // for red mushroom, cap's thickness is 4 blocks
		}

		// generate mushroom's cap
		for($y = $capY; $y <= $sourceY + $this->height; ++$y){ // from bottom to top of mushroom
			$radius = match(true){
				$y < $sourceY + $this->height => 2, // radius for red mushroom cap is 2
				$typeId === BlockTypeIds::BROWN_MUSHROOM_BLOCK => 3, // radius always 3 for a brown mushroom
				default => 1 // radius for the top of red mushroom
			};

			// loop over horizontal slice
			for($x = $sourceX - $radius; $x <= $sourceX + $radius; ++$x){
				for($z = $sourceZ - $radius; $z <= $sourceZ + $radius; ++$z){
					// cap's borders/corners treatment
					$data = match(true){
						$x === $sourceX - $radius => match(true){
							$z === $sourceZ - $radius => MushroomBlockType::CAP_NORTHWEST(),
							$z === $sourceZ + $radius => MushroomBlockType::CAP_SOUTHWEST(),
							default => MushroomBlockType::CAP_WEST()
						},
						$x === $sourceX + $radius => match(true){
							$z === $sourceZ - $radius => MushroomBlockType::CAP_NORTHEAST(),
							$z === $sourceZ + $radius => MushroomBlockType::CAP_SOUTHEAST(),
							default => MushroomBlockType::CAP_EAST()
						},
						default => match(true){
							$z === $sourceZ - $radius => MushroomBlockType::CAP_NORTH(),
							$z === $sourceZ + $radius => MushroomBlockType::CAP_SOUTH(),
							default => MushroomBlockType::CAP_MIDDLE()
						}
					};

					// corners shrink treatment
					// if it's a brown mushroom we need it always
					// it's a red mushroom, it's only applied below the top
					if($typeId === BlockTypeIds::BROWN_MUSHROOM_BLOCK || $y < $sourceY + $this->height){

						// excludes the real corners of the cap structure
						if(($x === $sourceX - $radius || $x === $sourceX + $radius)
							&& ($z === $sourceZ - $radius || $z === $sourceZ + $radius)){
							continue;
						}

						// mushroom's cap corners treatment
						if($x === $sourceX - ($radius - 1) && $z === $sourceZ - $radius){
							$data = MushroomBlockType::CAP_NORTHWEST();
						}elseif($x === $sourceX - $radius && $z === $sourceZ - ($radius - 1)){
							$data = MushroomBlockType::CAP_NORTHWEST();
						}elseif($x === $sourceX + $radius - 1 && $z === $sourceZ - $radius){
							$data = MushroomBlockType::CAP_NORTHEAST();
						}elseif($x === $sourceX + $radius && $z === $sourceZ - ($radius - 1)){
							$data = MushroomBlockType::CAP_NORTHEAST();
						}elseif($x === $sourceX - ($radius - 1) && $z === $sourceZ + $radius){
							$data = MushroomBlockType::CAP_SOUTHWEST();
						}elseif($x === $sourceX - $radius && $z === $sourceZ + $radius - 1){
							$data = MushroomBlockType::CAP_SOUTHWEST();
						}elseif($x === $sourceX + $radius - 1 && $z === $sourceZ + $radius){
							$data = MushroomBlockType::CAP_SOUTHEAST();
						}elseif($x === $sourceX + $radius && $z === $sourceZ + $radius - 1){
							$data = MushroomBlockType::CAP_SOUTHEAST();
						}
					}

					// a $data of CAP_MIDDLE below the top layer means air
					if($data !== MushroomBlockType::CAP_MIDDLE() || $y >= $sourceY + $this->height){
						$this->transaction->addBlockAt($x, $y, $z, $this->getType()->setMushroomBlockType($data));
					}
				}
			}
		}

		return true;
	}

	protected function canBeOverridden(Block $block): bool {
		return $block instanceof Air || $block instanceof Leaves;
	}
}
