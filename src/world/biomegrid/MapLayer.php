<?php

declare(strict_types=1);

namespace pocketmine\world\biomegrid;

use pocketmine\world\biomegrid\utils\MapLayerPair;
use pocketmine\world\Environment;
use pocketmine\world\overworld\biome\BiomeIds;
use pocketmine\world\overworld\WorldType;
use pocketmine\utils\Random;

abstract class MapLayer{

	public static function initialize(int $seed, int $environment, string $worldType) : MapLayerPair{
		if($environment === Environment::OVERWORLD && $worldType === WorldType::FLAT){
			return new MapLayerPair(new ConstantBiomeMapLayer($seed, BiomeIds::PLAINS), null);
		}

		if($environment === Environment::NETHER){
			return new MapLayerPair(new ConstantBiomeMapLayer($seed, BiomeIds::HELL), null);
		}

		if($environment === Environment::THE_END){
			return new MapLayerPair(new ConstantBiomeMapLayer($seed, BiomeIds::SKY), null);
		}

		$zoom = 2;
		if($worldType === WorldType::LARGE_BIOMES){
			$zoom = 4;
		}

		$layer = new NoiseMapLayer($seed); // this is initial land spread layer
		$layer = new WhittakerMapLayer($seed + 1, $layer, WhittakerMapLayer::WARM_WET);
		$layer = new WhittakerMapLayer($seed + 1, $layer, WhittakerMapLayer::COLD_DRY);
		$layer = new WhittakerMapLayer($seed + 2, $layer, WhittakerMapLayer::LARGER_BIOMES);

		for($i = 0; $i < 2; ++$i){
			$layer = new ZoomMapLayer($seed + 100 + $i, $layer, ZoomMapLayer::BLURRY);
		}

		for($i = 0; $i < 2; ++$i){
			$layer = new ErosionMapLayer($seed + 3 + $i, $layer);
		}

		$layer = new DeepOceanMapLayer($seed + 4, $layer);

		$layerMountains = new BiomeVariationMapLayer($seed + 200, $layer);
		for($i = 0; $i < 2; ++$i){
			$layerMountains = new ZoomMapLayer($seed + 200 + $i, $layerMountains);
		}

		$layer = new BiomeMapLayer($seed + 5, $layer);
		for($i = 0; $i < 2; ++$i){
			$layer = new ZoomMapLayer($seed + 200 + $i, $layer);
		}

		$layer = new BiomeEdgeMapLayer($seed + 200, $layer);
		$layer = new BiomeVariationMapLayer($seed + 200, $layer, $layerMountains);
		$layer = new RarePlainsMapLayer($seed + 201, $layer);
		$layer = new ZoomMapLayer($seed + 300, $layer);
		$layer = new ErosionMapLayer($seed + 6, $layer);
		$layer = new ZoomMapLayer($seed + 400, $layer);
		$layer = new BiomeThinEdgeMapLayer($seed + 400, $layer);
		$layer = new ShoreMapLayer($seed + 7, $layer);
		for($i = 0; $i < $zoom; ++$i){
			$layer = new ZoomMapLayer($seed + 500 + $i, $layer);
		}

		$layerRiver = $layerMountains;
		$layerRiver = new ZoomMapLayer($seed + 300, $layerRiver);
		$layerRiver = new ZoomMapLayer($seed + 400, $layerRiver);
		for($i = 0; $i < $zoom; ++$i){
			$layerRiver = new ZoomMapLayer($seed + 500 + $i, $layerRiver);
		}
		$layerRiver = new RiverMapLayer($seed + 10, $layerRiver);
		$layer = new RiverMapLayer($seed + 1000, $layerRiver, $layer);

		$layerLowerRes = $layer;
		for($i = 0; $i < 2; ++$i){
			$layer = new ZoomMapLayer($seed + 2000 + $i, $layer);
		}

		$layer = new SmoothMapLayer($seed + 1001, $layer);

		return new MapLayerPair($layer, $layerLowerRes);
	}

	private Random $random;
	private int $seed;

	public function __construct(int $seed){
		$this->random = new Random();
		$this->seed = $seed;
	}

	public function setCoordsSeed(int $x, int $z) : void{
		$this->random->setSeed($this->seed);
		$this->random->setSeed($x * $this->random->nextInt() + $z * $this->random->nextInt() ^ $this->seed);
	}

	public function nextInt(int $max) : int{
		return $this->random->nextBoundedInt($max);
	}

	/**
	 * @return int[]
	 */
	abstract public function generateValues(int $x, int $z, int $sizeX, int $sizeZ) : array;
}
