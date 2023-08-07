<?php

declare(strict_types=1);

namespace pocketmine\world\overworld\populator\biome;

use pocketmine\world\object\tree\BigOakTree;
use pocketmine\world\object\tree\GenericTree;
use pocketmine\world\overworld\decorator\CactusDecorator;
use pocketmine\world\overworld\decorator\DeadBushDecorator;
use pocketmine\world\overworld\decorator\DoublePlantDecorator;
use pocketmine\world\overworld\decorator\FlowerDecorator;
use pocketmine\world\overworld\decorator\LakeDecorator;
use pocketmine\world\overworld\decorator\MushroomDecorator;
use pocketmine\world\overworld\decorator\PumpkinDecorator;
use pocketmine\world\overworld\decorator\SugarCaneDecorator;
use pocketmine\world\overworld\decorator\SurfaceCaveDecorator;
use pocketmine\world\overworld\decorator\TallGrassDecorator;
use pocketmine\world\overworld\decorator\TreeDecorator;
use pocketmine\world\overworld\decorator\types\FlowerDecoration;
use pocketmine\world\overworld\decorator\types\TreeDecoration;
use pocketmine\world\overworld\decorator\UnderwaterDecorator;
use pocketmine\world\Populator;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use function array_push;
use const PHP_INT_MIN;

class BiomePopulator implements Populator{

	/** @var TreeDecoration[] */
	protected static array $TREES;

	/** @var FlowerDecoration[] */
	protected static array $FLOWERS;

	public static function init() : void{
		static::initTrees();
		static::initFlowers();
	}

	protected static function initTrees() : void{
		self::$TREES = [
			new TreeDecoration(BigOakTree::class, 1),
			new TreeDecoration(GenericTree::class, 9)
		];
	}

	protected static function initFlowers() : void{
		self::$FLOWERS = [
			new FlowerDecoration(VanillaBlocks::DANDELION(), 2),
			new FlowerDecoration(VanillaBlocks::POPPY(), 1)
		];
	}

	protected LakeDecorator $waterLakeDecorator;

	protected LakeDecorator $lavaLakeDecorator;

	protected OrePopulator $orePopulator;

	protected UnderwaterDecorator $sandPatchDecorator;

	protected UnderwaterDecorator $clayPatchDecorator;

	protected UnderwaterDecorator $gravelPatchDecorator;

	protected DoublePlantDecorator $doublePlantDecorator;

	protected TreeDecorator $treeDecorator;

	protected FlowerDecorator $flowerDecorator;

	protected TallGrassDecorator $tallGrassDecorator;

	protected DeadBushDecorator $deadBushDecorator;

	protected MushroomDecorator $brownMushroomDecorator;

	protected MushroomDecorator $redMushroomDecorator;

	protected SugarCaneDecorator $sugarCaneDecorator;

	protected PumpkinDecorator $pumpkinDecorator;

	protected CactusDecorator $cactusDecorator;

	protected SurfaceCaveDecorator $surfaceCaveDecorator;

	/** @var Populator[] */
	private array $inGroundPopulators = [];

	/** @var Populator[] */
	private array $onGroundPopulators = [];

	/**
	 * Creates a populator for lakes; dungeons; caves; ores; sand, gravel and clay patches; desert
	 * wells; and vegetation.
	 */
	public function __construct(){
		$this->waterLakeDecorator = new LakeDecorator(VanillaBlocks::WATER()->getStillForm(), 4);
		$this->lavaLakeDecorator = new LakeDecorator(VanillaBlocks::LAVA()->getStillForm(), 8, 8);
		$this->orePopulator = new OrePopulator();
		$this->sandPatchDecorator = new UnderwaterDecorator(VanillaBlocks::SAND());
		$this->clayPatchDecorator = new UnderwaterDecorator(VanillaBlocks::CLAY());
		$this->gravelPatchDecorator = new UnderwaterDecorator(VanillaBlocks::GRAVEL());
		$this->doublePlantDecorator = new DoublePlantDecorator();
		$this->treeDecorator = new TreeDecorator();
		$this->flowerDecorator = new FlowerDecorator();
		$this->tallGrassDecorator = new TallGrassDecorator();
		$this->deadBushDecorator = new DeadBushDecorator();
		$this->brownMushroomDecorator = new MushroomDecorator(VanillaBlocks::BROWN_MUSHROOM());
		$this->redMushroomDecorator = new MushroomDecorator(VanillaBlocks::RED_MUSHROOM());
		$this->sugarCaneDecorator = new SugarCaneDecorator();
		$this->pumpkinDecorator = new PumpkinDecorator();
		$this->cactusDecorator = new CactusDecorator();
		$this->surfaceCaveDecorator = new SurfaceCaveDecorator();

		array_push($this->inGroundPopulators,
			$this->waterLakeDecorator,
			$this->lavaLakeDecorator,
			$this->surfaceCaveDecorator,
			$this->orePopulator,
			$this->sandPatchDecorator,
			$this->clayPatchDecorator,
			$this->gravelPatchDecorator
		);

		array_push($this->onGroundPopulators,
			$this->doublePlantDecorator,
			$this->treeDecorator,
			$this->flowerDecorator,
			$this->tallGrassDecorator,
			$this->deadBushDecorator,
			$this->brownMushroomDecorator,
			$this->redMushroomDecorator,
			$this->sugarCaneDecorator,
			$this->pumpkinDecorator,
			$this->cactusDecorator
		);

		$this->initPopulators();
	}

	protected function initPopulators() : void{
		$this->waterLakeDecorator->setAmount(1);
		$this->lavaLakeDecorator->setAmount(1);
		$this->surfaceCaveDecorator->setAmount(1);
		$this->sandPatchDecorator->setAmount(3);
		$this->sandPatchDecorator->setRadii(7, 2);
		$this->sandPatchDecorator->setOverridableBlocks(VanillaBlocks::DIRT(), VanillaBlocks::GRASS());
		$this->clayPatchDecorator->setAmount(1);
		$this->clayPatchDecorator->setRadii(4, 1);
		$this->clayPatchDecorator->setOverridableBlocks(VanillaBlocks::DIRT());
		$this->gravelPatchDecorator->setAmount(1);
		$this->gravelPatchDecorator->setRadii(6, 2);
		$this->gravelPatchDecorator->setOverridableBlocks(VanillaBlocks::DIRT(), VanillaBlocks::GRASS());

		$this->doublePlantDecorator->setAmount(0);
		$this->treeDecorator->setAmount(PHP_INT_MIN);
		$this->treeDecorator->setTrees(...self::$TREES);
		$this->flowerDecorator->setAmount(2);
		$this->flowerDecorator->setFlowers(...self::$FLOWERS);
		$this->tallGrassDecorator->setAmount(1);
		$this->deadBushDecorator->setAmount(0);
		$this->brownMushroomDecorator->setAmount(1);
		$this->brownMushroomDecorator->setDensity(0.25);
		$this->redMushroomDecorator->setAmount(1);
		$this->redMushroomDecorator->setDensity(0.125);
		$this->sugarCaneDecorator->setAmount(10);
		$this->cactusDecorator->setAmount(0);
	}

	/**
	 * Returns an array of biome ids or null if this populator targets all
	 * biomes.
	 *
	 * @return int[]|null
	 */
	public function getBiomes() : ?array{
		return null;
	}

	public function populate(ChunkManager $world, Random $random, int $chunkX, int $chunkZ, Chunk $chunk) : void{
		$this->populateInGround($world, $random, $chunkX, $chunkZ, $chunk);
		$this->populateOnGround($world, $random, $chunkX, $chunkZ, $chunk);
	}

	protected function populateInGround(ChunkManager $world, Random $random, int $chunkX, int $chunkZ, Chunk $chunk) : void{
		foreach($this->inGroundPopulators as $populator){
			$populator->populate($world, $random, $chunkX, $chunkZ, $chunk);
		}
	}

	protected function populateOnGround(ChunkManager $world, Random $random, int $chunkX, int $chunkZ, Chunk $chunk) : void{
		foreach($this->onGroundPopulators as $populator){
			$populator->populate($world, $random, $chunkX, $chunkZ, $chunk);
		}
	}
}

BiomePopulator::init();
