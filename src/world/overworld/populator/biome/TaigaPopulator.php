<?php

declare(strict_types=1);

namespace pocketmine\world\overworld\populator\biome;

use pocketmine\world\object\tree\RedwoodTree;
use pocketmine\world\object\tree\TallRedwoodTree;
use pocketmine\world\overworld\biome\BiomeIds;
use pocketmine\world\overworld\decorator\MushroomDecorator;
use pocketmine\world\overworld\decorator\types\DoublePlantDecoration;
use pocketmine\world\overworld\decorator\types\TreeDecoration;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;

class TaigaPopulator extends BiomePopulator{

	/** @var DoublePlantDecoration[] */
	protected static array $DOUBLE_PLANTS;

	/** @var TreeDecoration[] */
	protected static array $TREES;

	public static function init() : void{
		parent::init();
		self::$DOUBLE_PLANTS = [
			new DoublePlantDecoration(VanillaBlocks::LARGE_FERN(), 1)
		];
	}

	protected static function initTrees() : void{
		self::$TREES = [
			new TreeDecoration(RedwoodTree::class, 2),
			new TreeDecoration(TallRedwoodTree::class, 1)
		];
	}

	protected MushroomDecorator $taigaBrownMushroomDecorator;
	protected MushroomDecorator $taigaRedMushroomDecorator;

	public function __construct(){
		$this->taigaBrownMushroomDecorator = new MushroomDecorator(VanillaBlocks::BROWN_MUSHROOM());
		$this->taigaRedMushroomDecorator = new MushroomDecorator(VanillaBlocks::RED_MUSHROOM());
		parent::__construct();
	}

	protected function initPopulators() : void{
		$this->doublePlantDecorator->setAmount(7);
		$this->doublePlantDecorator->setDoublePlants(...self::$DOUBLE_PLANTS);
		$this->treeDecorator->setAmount(10);
		$this->treeDecorator->setTrees(...self::$TREES);
		$this->tallGrassDecorator->setFernDensity(0.8);
		$this->deadBushDecorator->setAmount(1);
		$this->taigaBrownMushroomDecorator->setAmount(1);
		$this->taigaBrownMushroomDecorator->setUseFixedHeightRange();
		$this->taigaBrownMushroomDecorator->setDensity(0.25);
		$this->taigaRedMushroomDecorator->setAmount(1);
		$this->taigaRedMushroomDecorator->setDensity(0.125);
	}

	public function getBiomes() : ?array{
		return [BiomeIds::TAIGA, BiomeIds::TAIGA_HILLS, BiomeIds::TAIGA_MUTATED, BiomeIds::COLD_TAIGA, BiomeIds::COLD_TAIGA_HILLS, BiomeIds::COLD_TAIGA_MUTATED];
	}

	protected function populateOnGround(ChunkManager $world, Random $random, int $chunkX, int $chunkZ, Chunk $chunk) : void{
		parent::populateOnGround($world, $random, $chunkX, $chunkZ, $chunk);
		$this->taigaBrownMushroomDecorator->populate($world, $random, $chunkX, $chunkZ, $chunk);
		$this->taigaRedMushroomDecorator->populate($world, $random, $chunkX, $chunkZ, $chunk);
	}
}
TaigaPopulator::init();
