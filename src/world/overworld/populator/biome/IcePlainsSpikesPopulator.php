<?php

declare(strict_types=1);

namespace pocketmine\world\overworld\populator\biome;

use pocketmine\world\overworld\biome\BiomeIds;
use pocketmine\world\overworld\decorator\IceDecorator;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class IcePlainsSpikesPopulator extends IcePlainsPopulator{

	protected IceDecorator $iceDecorator;

	public function __construct(){
		parent::__construct();
		$this->tallGrassDecorator->setAmount(0);
		$this->iceDecorator = new IceDecorator();
	}

	protected function populateOnGround(ChunkManager $world, Random $random, int $chunkX, int $chunkZ, Chunk $chunk) : void{
		$this->iceDecorator->populate($world, $random, $chunkX, $chunkZ, $chunk);
		parent::populateOnGround($world, $random, $chunkX, $chunkZ, $chunk);
	}

	public function getBiomes() : ?array{
		return [BiomeIds::ICE_PLAINS_SPIKES];
	}
}
