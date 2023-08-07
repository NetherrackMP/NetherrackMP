<?php

declare(strict_types=1);

namespace pocketmine\world\overworld\populator;

use pocketmine\world\overworld\biome\BiomeIds;
use pocketmine\world\overworld\populator\biome\BiomePopulator;
use pocketmine\world\overworld\populator\biome\BirchForestMountainsPopulator;
use pocketmine\world\overworld\populator\biome\BirchForestPopulator;
use pocketmine\world\overworld\populator\biome\DesertMountainsPopulator;
use pocketmine\world\overworld\populator\biome\DesertPopulator;
use pocketmine\world\overworld\populator\biome\FlowerForestPopulator;
use pocketmine\world\overworld\populator\biome\ForestPopulator;
use pocketmine\world\overworld\populator\biome\IcePlainsPopulator;
use pocketmine\world\overworld\populator\biome\IcePlainsSpikesPopulator;
use pocketmine\world\overworld\populator\biome\JungleEdgePopulator;
use pocketmine\world\overworld\populator\biome\JunglePopulator;
use pocketmine\world\overworld\populator\biome\MegaSpruceTaigaPopulator;
use pocketmine\world\overworld\populator\biome\MegaTaigaPopulator;
use pocketmine\world\overworld\populator\biome\PlainsPopulator;
use pocketmine\world\overworld\populator\biome\RoofedForestPopulator;
use pocketmine\world\overworld\populator\biome\SavannaMountainsPopulator;
use pocketmine\world\overworld\populator\biome\SavannaPopulator;
use pocketmine\world\overworld\populator\biome\SunflowerPlainsPopulator;
use pocketmine\world\overworld\populator\biome\SwamplandPopulator;
use pocketmine\world\overworld\populator\biome\TaigaPopulator;
use pocketmine\world\Populator;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use ReflectionClass;
use function array_key_exists;
use function array_values;

class OverworldPopulator implements Populator{

	/** @var Populator[] */
	private array $biomePopulators = []; // key = biomeId

	/**
	 * Creates a populator with biome populators for all vanilla overworld biomes.
	 */
	public function __construct(){
		$this->registerBiomePopulator(new BiomePopulator()); // defaults applied to all biomes
		$this->registerBiomePopulator(new PlainsPopulator());
		$this->registerBiomePopulator(new SunflowerPlainsPopulator());
		$this->registerBiomePopulator(new ForestPopulator());
		$this->registerBiomePopulator(new BirchForestPopulator());
		$this->registerBiomePopulator(new BirchForestMountainsPopulator());
		$this->registerBiomePopulator(new RoofedForestPopulator());
		$this->registerBiomePopulator(new FlowerForestPopulator());
		$this->registerBiomePopulator(new DesertPopulator());
		$this->registerBiomePopulator(new DesertMountainsPopulator());
		$this->registerBiomePopulator(new JunglePopulator());
		$this->registerBiomePopulator(new JungleEdgePopulator());
		$this->registerBiomePopulator(new SwamplandPopulator());
		$this->registerBiomePopulator(new TaigaPopulator());
		$this->registerBiomePopulator(new MegaTaigaPopulator());
		$this->registerBiomePopulator(new MegaSpruceTaigaPopulator());
		$this->registerBiomePopulator(new IcePlainsPopulator());
		$this->registerBiomePopulator(new IcePlainsSpikesPopulator());
		$this->registerBiomePopulator(new SavannaPopulator());
		$this->registerBiomePopulator(new SavannaMountainsPopulator());
		/*
		$this->registerBiomePopulator(new ExtremeHillsPopulator());
		$this->registerBiomePopulator(new ExtremeHillsPlusPopulator());
		$this->registerBiomePopulator(new MesaPopulator());
		$this->registerBiomePopulator(new MesaForestPopulator());
		$this->registerBiomePopulator(new MushroomIslandPopulator());
		$this->registerBiomePopulator(new OceanPopulator());
		*/
	}

	public function populate(ChunkManager $world, Random $random, int $chunkX, int $chunkZ, Chunk $chunk) : void{
		$biome = $chunk->getBiomeId(8, 0, 8);
		if(array_key_exists($biome, $this->biomePopulators)){
			$this->biomePopulators[$biome]->populate($world, $random, $chunkX, $chunkZ, $chunk);
		}
	}

	private function registerBiomePopulator(BiomePopulator $populator) : void{
		$biomes = $populator->getBiomes();
		if($biomes === null){
			$biomes = array_values((new ReflectionClass(BiomeIds::class))->getConstants());
		}

		foreach($biomes as $biome){
			$this->biomePopulators[$biome] = $populator;
		}
	}
}
