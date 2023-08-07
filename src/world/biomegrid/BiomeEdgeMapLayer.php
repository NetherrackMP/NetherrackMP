<?php

declare(strict_types=1);

namespace pocketmine\world\biomegrid;

use pocketmine\world\biomegrid\utils\BiomeEdgeEntry;
use pocketmine\world\overworld\biome\BiomeIds;
use function array_key_exists;

class BiomeEdgeMapLayer extends MapLayer{

	/** @var int[] */
	private static array $MESA_EDGES = [
		BiomeIds::MESA_PLATEAU_STONE => BiomeIds::MESA,
		BiomeIds::MESA_PLATEAU => BiomeIds::MESA
	];

	/** @var int[] */
	private static array $MEGA_TAIGA_EDGES = [
		BiomeIds::MEGA_TAIGA => BiomeIds::TAIGA
	];

	/** @var int[] */
	private static array $DESERT_EDGES = [
		BiomeIds::DESERT => BiomeIdS::EXTREME_HILLS_PLUS_TREES
	];

	/** @var int[] */
	private static array $SWAMP1_EDGES = [
		BiomeIds::SWAMPLAND => BiomeIds::PLAINS
	];

	/** @var int[] */
	private static array $SWAMP2_EDGES = [
		BiomeIds::SWAMPLAND => BiomeIds::JUNGLE_EDGE
	];

	/** @var BiomeEdgeEntry[] */
	private static array $EDGES;

	public static function init() : void{
		self::$EDGES = [
			new BiomeEdgeEntry(self::$MESA_EDGES),
			new BiomeEdgeEntry(self::$MEGA_TAIGA_EDGES),
			new BiomeEdgeEntry(self::$DESERT_EDGES, [BiomeIds::ICE_PLAINS]),
			new BiomeEdgeEntry(self::$SWAMP1_EDGES, [BiomeIds::DESERT, BiomeIds::COLD_TAIGA, BiomeIds::ICE_PLAINS]),
			new BiomeEdgeEntry(self::$SWAMP2_EDGES, [BiomeIds::JUNGLE])
		];
	}

	private MapLayer $belowLayer;

	public function __construct(int $seed, MapLayer $belowLayer){
		parent::__construct($seed);
		$this->belowLayer = $belowLayer;
	}

	public function generateValues(int $x, int $z, int $sizeX, int $sizeZ) : array{
		$gridX = $x - 1;
		$gridZ = $z - 1;
		$gridSizeX = $sizeX + 2;
		$gridSizeZ = $sizeZ + 2;
		$values = $this->belowLayer->generateValues($gridX, $gridZ, $gridSizeX, $gridSizeZ);

		$finalValues = [];
		for($i = 0; $i < $sizeZ; ++$i){
			for($j = 0; $j < $sizeX; ++$j){
				// This applies biome large edges using Von Neumann neighborhood
				$centerVal = $values[$j + 1 + ($i + 1) * $gridSizeX];
				$val = $centerVal;
				foreach(self::$EDGES as $edge){ // [$map, $entry]
					if(array_key_exists($centerVal, $edge->key)){
						$upperVal = $values[$j + 1 + $i * $gridSizeX];
						$lowerVal = $values[$j + 1 + ($i + 2) * $gridSizeX];
						$leftVal = $values[$j + ($i + 1) * $gridSizeX];
						$rightVal = $values[$j + 2 + ($i + 1) * $gridSizeX];

						if($edge->value === null && (
							!array_key_exists($upperVal, $edge->key)
							|| !array_key_exists($lowerVal, $edge->key)
							|| !array_key_exists($leftVal, $edge->key)
							|| !array_key_exists($rightVal, $edge->key)
						)){
							$val = $edge->key[$centerVal];
							break;
						}

						if($edge->value !== null && (
							array_key_exists($upperVal, $edge->value) ||
							array_key_exists($lowerVal, $edge->value) ||
							array_key_exists($leftVal, $edge->value) ||
							array_key_exists($rightVal, $edge->value)
						)){
							$val = $edge->key[$centerVal];
							break;
						}
					}
				}

				$finalValues[$j + $i * $sizeX] = $val;
			}
		}

		return $finalValues;
	}
}

BiomeEdgeMapLayer::init();
