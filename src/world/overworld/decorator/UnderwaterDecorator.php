<?php

declare(strict_types=1);

namespace pocketmine\world\overworld\decorator;

use pocketmine\world\Decorator;
use pocketmine\world\object\BlockPatch;
use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\Water;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class UnderwaterDecorator extends Decorator{

	private Block $type;

	private int $horizRadius;

	private int $vertRadius;

	/** @var int[] */
	private array $overridables;

	public function __construct(Block $type){
		$this->type = $type;
	}

	/**
	 * Updates the size of this decorator.
	 *
	 * @param int $horizRadius the maximum radius on the horizontal plane
	 * @param int $vertRadius the depth above and below the center
	 * @return UnderwaterDecorator this, updated
	 */
	final public function setRadii(int $horizRadius, int $vertRadius) : UnderwaterDecorator{
		$this->horizRadius = $horizRadius;
		$this->vertRadius = $vertRadius;
		return $this;
	}

	final public function setOverridableBlocks(Block ...$overridables) : UnderwaterDecorator{
		foreach($overridables as $overridable){
			$this->overridables[] = $overridable->getStateId();
		}
		return $this;
	}

	public function decorate(ChunkManager $world, Random $random, int $chunkX, int $chunkZ, Chunk $chunk) : void{
		$sourceX = ($chunkX << 4) + $random->nextBoundedInt(16);
		$sourceZ = ($chunkZ << 4) + $random->nextBoundedInt(16);
		$sourceY = $chunk->getHighestBlockAt($sourceX & 0x0f, $sourceZ & 0x0f) - 1;
		while($sourceY > 1 && $world->getBlockAt($sourceX, $sourceY - 1, $sourceZ) instanceof Water) {
			--$sourceY;
		}
		$material = $world->getBlockAt($sourceX, $sourceY, $sourceZ);
		if($material instanceof Water){
			(new BlockPatch($this->type, $this->horizRadius, $this->vertRadius, ...$this->overridables))->generate($world, $random, $sourceX, $sourceY, $sourceZ);
		}
	}
}
