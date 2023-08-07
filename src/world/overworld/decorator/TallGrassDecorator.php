<?php

declare(strict_types=1);

namespace pocketmine\world\overworld\decorator;

use pocketmine\world\Decorator;
use pocketmine\world\object\TallGrass;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use function abs;

class TallGrassDecorator extends Decorator{

	private float $fernDensity = 0.0;

	final public function setFernDensity(float $fernDensity) : void{
		$this->fernDensity = $fernDensity;
	}

	public function decorate(ChunkManager $world, Random $random, int $chunkX, int $chunkZ, Chunk $chunk) : void{
		$x = $random->nextBoundedInt(16);
		$z = $random->nextBoundedInt(16);
		$topBlock = $chunk->getHighestBlockAt($x, $z);
		if($topBlock <= 0){
			// Nothing to do if this column is empty
			return;
		}

		$sourceY = $random->nextBoundedInt(abs($topBlock << 1));

		// the grass species can change on each decoration pass
		$species = VanillaBlocks::TALL_GRASS();
		if($this->fernDensity > 0 && $random->nextFloat() < $this->fernDensity){
			$species = VanillaBlocks::FERN();
		}
		(new TallGrass($species))->generate($world, $random, ($chunkX << 4) + $x, $sourceY, ($chunkZ << 4) + $z);
	}
}
