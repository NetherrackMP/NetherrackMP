<?php

declare(strict_types=1);

namespace pocketmine\world\overworld\populator\biome;

use pocketmine\world\noise\bukkit\OctaveGenerator;
use pocketmine\world\noise\glowstone\SimplexOctaveGenerator;
use pocketmine\world\object\Flower;
use pocketmine\world\overworld\biome\BiomeIds;
use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use function count;
use function min;

class FlowerForestPopulator extends ForestPopulator{

	/** @var Block[] */
	protected static array $FLOWERS = [];

	protected static function initFlowers() : void{
		self::$FLOWERS = [
			VanillaBlocks::POPPY(),
			VanillaBlocks::POPPY(),
			VanillaBlocks::DANDELION(),
			VanillaBlocks::ALLIUM(),
			VanillaBlocks::AZURE_BLUET(),
			VanillaBlocks::RED_TULIP(),
			VanillaBlocks::ORANGE_TULIP(),
			VanillaBlocks::WHITE_TULIP(),
			VanillaBlocks::PINK_TULIP(),
			VanillaBlocks::OXEYE_DAISY()
		];
	}

	private OctaveGenerator $noiseGen;

	protected function initPopulators() : void{
		parent::initPopulators();
		$this->treeDecorator->setAmount(6);
		$this->flowerDecorator->setAmount(0);
		$this->doublePlantLoweringAmount = 1;
		$this->noiseGen = SimplexOctaveGenerator::fromRandomAndOctaves(new Random(2345), 1, 0, 0, 0);
		$this->noiseGen->setScale(1 / 48.0);
	}

	public function getBiomes() : ?array{
		return [BiomeIds::FLOWER_FOREST];
	}

	public function populateOnGround(ChunkManager $world, Random $random, int $chunkX, int $chunkZ, Chunk $chunk) : void{
		parent::populateOnGround($world, $random, $chunkX, $chunkZ, $chunk);

		$sourceX = $chunkX << 4;
		$sourceZ = $chunkZ << 4;

		for($i = 0; $i < 100; ++$i){
			$x = $random->nextBoundedInt(16);
			$z = $random->nextBoundedInt(16);
			$y = $random->nextBoundedInt($chunk->getHighestBlockAt($x, $z) + 32);
			$noise = ($this->noiseGen->noise($x, $z, 0.5, 0, 2.0, false) + 1.0) / 2.0;
			$noise = $noise < 0 ? 0 : (min($noise, 0.9999));
			$flower = self::$FLOWERS[(int) ($noise * count(self::$FLOWERS))];
			(new Flower($flower))->generate($world, $random, $sourceX + $x, $y, $sourceZ + $z);
		}
	}
}

FlowerForestPopulator::init();
