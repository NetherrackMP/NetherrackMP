<?php

declare(strict_types=1);

namespace pocketmine\world\object\tree;

use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use function count;

class CocoaTree extends JungleTree{

	private const COCOA_FACES = [Facing::NORTH, Facing::EAST, Facing::SOUTH, Facing::WEST];

	// basically ages?
	private const SIZE_SMALL = 0;
	private const SIZE_MEDIUM = 1;
	private const SIZE_LARGE = 2;

	private const COCOA_SIZE = [self::SIZE_SMALL, self::SIZE_MEDIUM, self::SIZE_LARGE];

	public function generate(ChunkManager $world, Random $random, int $sourceX, int $sourceY, int $sourceZ) : bool{
		if(!parent::generate($world, $random, $sourceX, $sourceY, $sourceZ)){
			return false;
		}

		// places some vines on the trunk
		$this->addVinesOnTrunk($sourceX, $sourceY, $sourceZ, $world, $random);
		// search for air around leaves to grow hanging vines
		$this->addVinesOnLeaves($sourceX, $sourceY, $sourceZ, $world, $random);
		// and maybe place some cocoa
		$this->addCocoa($sourceX, $sourceY, $sourceZ, $random);
		return true;
	}

	protected function addVinesOnLeaves(int $baseX, int $baseY, int $baseZ, ChunkManager $world, Random $random) : void{
		for($y = $baseY - 3 + $this->height; $y <= $baseY + $this->height; ++$y){
			$ny = $y - ($baseY + $this->height);
			$radius = 2 - $ny / 2;
			for($x = $baseX - $radius; $x <= $baseX + $radius; ++$x){
				$ax = (int) $x;
				for($z = $baseZ - $radius; $z <= $baseZ + $radius; ++$z){
					$az = (int) $z;
					if($world->getBlockAt($ax, $y, $az)->getTypeId() === BlockTypeIds::JUNGLE_LEAVES){
						if($random->nextBoundedInt(4) === 0 && $world->getBlockAt($ax - 1, $y, $az)->getTypeId() === BlockTypeIds::AIR){
							$this->addHangingVine($ax - 1, $y, $az, Facing::EAST, $world);
						}
						if($random->nextBoundedInt(4) === 0 && $world->getBlockAt($ax + 1, $y, $az)->getTypeId() === BlockTypeIds::AIR){
							$this->addHangingVine($ax + 1, $y, $az, Facing::WEST, $world);
						}
						if($random->nextBoundedInt(4) === 0 && $world->getBlockAt($ax, $y, $az - 1)->getTypeId() === BlockTypeIds::AIR){
							$this->addHangingVine($ax, $y, $az - 1, Facing::SOUTH, $world);
						}
						if($random->nextBoundedInt(4) === 0 && $world->getBlockAt($ax, $y, $az + 1)->getTypeId() === BlockTypeIds::AIR){
							$this->addHangingVine($ax, $y, $az + 1, Facing::NORTH, $world);
						}
					}
				}
			}
		}
	}

	private function addVinesOnTrunk(int $trunkX, int $trunkY, int $trunkZ, ChunkManager $world, Random $random) : void{
		for($y = 1; $y < $this->height; ++$y){
			if(
				$random->nextBoundedInt(3) !== 0 &&
				$world->getBlockAt($trunkX - 1, $trunkY + $y, $trunkZ)->getTypeId() === BlockTypeIds::AIR
			){
				$this->transaction->addBlockAt($trunkX - 1, $trunkY + $y, $trunkZ, VanillaBlocks::VINES()->setFace(Facing::EAST, true));
			}
			if(
				$random->nextBoundedInt(3) !== 0 &&
				$world->getBlockAt($trunkX + 1, $trunkY + $y, $trunkZ)->getTypeId() === BlockTypeIds::AIR
			){
				$this->transaction->addBlockAt($trunkX + 1, $trunkY + $y, $trunkZ, VanillaBlocks::VINES()->setFace(Facing::WEST, true));
			}
			if(
				$random->nextBoundedInt(3) !== 0 &&
				$world->getBlockAt($trunkX, $trunkY + $y, $trunkZ - 1)->getTypeId() === BlockTypeIds::AIR
			){
				$this->transaction->addBlockAt($trunkX, $trunkY + $y, $trunkZ - 1, VanillaBlocks::VINES()->setFace(Facing::SOUTH, true));
			}
			if(
				$random->nextBoundedInt(3) !== 0 &&
				$world->getBlockAt($trunkX, $trunkY + $y, $trunkZ + 1)->getTypeId() === BlockTypeIds::AIR
			){
				$this->transaction->addBlockAt($trunkX, $trunkY + $y, $trunkZ + 1, VanillaBlocks::VINES()->setFace(Facing::NORTH, true));
			}
		}
	}

	private function addHangingVine(int $x, int $y, int $z, int $face, ChunkManager $world) : void{
		for($i = 0; $i < 5; ++$i){
			if($world->getBlockAt($x, $y - $i, $z)->getTypeId() !== BlockTypeIds::AIR){
				break;
			}
			$this->transaction->addBlockAt($x, $y - $i, $z, VanillaBlocks::VINES()->setFace($face, true));
		}
	}

	private function addCocoa(int $sourceX, int $sourceY, int $sourceZ, Random $random) : void{
		if($this->height > 5 && $random->nextBoundedInt(5) === 0){
			for($y = 0; $y < 2; ++$y){
				foreach(self::COCOA_FACES as $cocoaFace){
					if($random->nextBoundedInt(count(self::COCOA_FACES) - $y) === 0){ // higher it is, more chances there is
						$size = self::COCOA_SIZE[$random->nextBoundedInt(count(self::COCOA_SIZE))];
						$block = (new Vector3($sourceX, $sourceY + $this->height - 5 + $y, $sourceZ))->getSide($cocoaFace);
						$this->transaction->addBlockAt($block->x, $block->y, $block->z, VanillaBlocks::COCOA_POD()->setFacing($cocoaFace)->setAge($size));
					}
				}
			}
		}
	}
}
