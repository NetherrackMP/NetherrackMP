<?php

declare(strict_types=1);

namespace pocketmine\world\overworld\populator\biome;

use pocketmine\world\object\tree\SwampTree;
use pocketmine\world\overworld\biome\BiomeIds;
use pocketmine\world\overworld\decorator\MushroomDecorator;
use pocketmine\world\overworld\decorator\types\FlowerDecoration;
use pocketmine\world\overworld\decorator\types\TreeDecoration;
use pocketmine\world\overworld\decorator\WaterLilyDecorator;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class SwamplandPopulator extends BiomePopulator{

	/** @var TreeDecoration[] */
	protected static array $TREES;

	/** @var FlowerDecoration[] */
	protected static array $FLOWERS;

	protected static function initTrees() : void{
		self::$TREES = [
			new TreeDecoration(SwampTree::class, 1)
		];
	}

	protected static function initFlowers() : void{
		self::$FLOWERS = [
			new FlowerDecoration(VanillaBlocks::BLUE_ORCHID(), 1)
		];
	}

	private MushroomDecorator $swamplandBrownMushroomDecorator;
	private MushroomDecorator $swamplandRedMushroomDecorator;
	private WaterLilyDecorator $waterlilyDecorator;

	public function __construct(){
		$this->swamplandBrownMushroomDecorator = new MushroomDecorator(VanillaBlocks::BROWN_MUSHROOM());
		$this->swamplandRedMushroomDecorator = new MushroomDecorator(VanillaBlocks::RED_MUSHROOM());
		$this->waterlilyDecorator = new WaterLilyDecorator();
		parent::__construct();
	}

	protected function initPopulators() : void{
		$this->sandPatchDecorator->setAmount(0);
		$this->gravelPatchDecorator->setAmount(0);
		$this->treeDecorator->setAmount(2);
		$this->treeDecorator->setTrees(...self::$TREES);
		$this->flowerDecorator->setAmount(1);
		$this->flowerDecorator->setFlowers(...self::$FLOWERS);
		$this->tallGrassDecorator->setAmount(5);
		$this->deadBushDecorator->setAmount(1);
		$this->sugarCaneDecorator->setAmount(20);
		$this->swamplandBrownMushroomDecorator->setAmount(8);
		$this->swamplandRedMushroomDecorator->setAmount(8);
		$this->waterlilyDecorator->setAmount(4);
	}

	public function getBiomes() : ?array{
		return [BiomeIds::SWAMPLAND, BiomeIds::SWAMPLAND_MUTATED];
	}

	protected function populateOnGround(ChunkManager $world, Random $random, int $chunkX, int $chunkZ, Chunk $chunk) : void{
		parent::populateOnGround($world, $random, $chunkX, $chunkZ, $chunk);
		$this->swamplandBrownMushroomDecorator->populate($world, $random, $chunkX, $chunkZ, $chunk);
		$this->swamplandRedMushroomDecorator->populate($world, $random, $chunkX, $chunkZ, $chunk);
		$this->waterlilyDecorator->populate($world, $random, $chunkX, $chunkZ, $chunk);
	}
}

SwamplandPopulator::init();
