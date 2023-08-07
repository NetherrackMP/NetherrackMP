<?php

declare(strict_types=1);

namespace pocketmine\world\overworld\decorator;

use pocketmine\world\Decorator;
use pocketmine\world\object\Lake;
use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class LakeDecorator extends Decorator{

	private Block $type;

	private int $rarity;

	private int $baseOffset;

	/**
	 * Creates a lake decorator.
	 */
	public function __construct(Block $type, int $rarity, int $baseOffset = 0){
		$this->type = $type;
		$this->rarity = $rarity;
		$this->baseOffset = $baseOffset;
	}

	public function decorate(ChunkManager $world, Random $random, int $chunkX, int $chunkZ, Chunk $chunk) : void{
		if($random->nextBoundedInt($this->rarity) === 0){
			$sourceX = ($chunkX << 4) + $random->nextBoundedInt(16);
			$sourceZ = ($chunkZ << 4) + $random->nextBoundedInt(16);
			$sourceY = $random->nextBoundedInt($world->getMaxY() - $this->baseOffset) + $this->baseOffset;
			if($this->type->getTypeId() === BlockTypeIds::LAVA && ($sourceY >= 64 || $random->nextBoundedInt(10) > 0)){
				return;
			}
			while($world->getBlockAt($sourceX, $sourceY, $sourceZ)->getTypeId() === BlockTypeIds::AIR && $sourceY > 5){
				--$sourceY;
			}
			if($sourceY >= 5){
				(new Lake($this->type))->generate($world, $random, $sourceX, $sourceY, $sourceZ);
			}
		}
	}
}
