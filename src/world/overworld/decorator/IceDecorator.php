<?php

declare(strict_types=1);

namespace pocketmine\world\overworld\decorator;

use pocketmine\world\Decorator;
use pocketmine\world\object\BlockPatch;
use pocketmine\world\object\IceSpike;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class IceDecorator extends Decorator{

	/** @var int[] */
	private static array $OVERRIDABLES;

	public static function init() : void{
		self::$OVERRIDABLES = [
			VanillaBlocks::DIRT()->getStateId(),
			VanillaBlocks::GRASS()->getStateId(),
			VanillaBlocks::SNOW()->getStateId(),
			VanillaBlocks::ICE()->getStateId()
		];
	}

	public function populate(ChunkManager $world, Random $random, int $chunkX, int $chunkZ, Chunk $chunk) : void{
		$sourceX = $chunkX << 4;
		$sourceZ = $chunkZ << 4;

		for($i = 0; $i < 3; ++$i){
			$x = $sourceX + $random->nextBoundedInt(16);
			$z = $sourceZ + $random->nextBoundedInt(16);
			$y = $chunk->getHighestBlockAt($x & 0x0f, $z & 0x0f) - 1;
			while($y > 2 && $world->getBlockAt($x, $y, $z)->getTypeId() === BlockTypeIds::AIR){
				--$y;
			}
			if($world->getBlockAt($x, $y, $z)->getTypeId() === BlockTypeIds::SNOW){
				(new BlockPatch(VanillaBlocks::PACKED_ICE(), 4, 1, ...self::$OVERRIDABLES))->generate($world, $random, $x, $y, $z);
			}
		}

		for($i = 0; $i < 2; ++$i){
			$x = $sourceX + $random->nextBoundedInt(16);
			$z = $sourceZ + $random->nextBoundedInt(16);
			$y = $chunk->getHighestBlockAt($x & 0x0f, $z & 0x0f);
			while($y > 2 && $world->getBlockAt($x, $y, $z)->getTypeId() === BlockTypeIds::AIR){
				--$y;
			}
			if($world->getBlockAt($x, $y, $z)->getTypeId() === BlockTypeIds::SNOW){
				(new IceSpike())->generate($world, $random, $x, $y, $z);
			}
		}
	}

	public function decorate(ChunkManager $world, Random $random, int $chunkX, int $chunkZ, Chunk $chunk) : void{
	}
}

IceDecorator::init();
