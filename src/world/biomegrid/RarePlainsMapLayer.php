<?php

declare(strict_types=1);

namespace pocketmine\world\biomegrid;

use pocketmine\world\overworld\biome\BiomeIds;
use function array_key_exists;

class RarePlainsMapLayer extends MapLayer{

	/** @var int[] */
	private static array $RARE_PLAINS = [BiomeIds::PLAINS => BiomeIds::SUNFLOWER_PLAINS];

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
				$this->setCoordsSeed($x + $j, $z + $i);
				$centerValue = $values[$j + 1 + ($i + 1) * $gridSizeX];
				if($this->nextInt(57) === 0 && array_key_exists($centerValue, self::$RARE_PLAINS)){
					$centerValue = self::$RARE_PLAINS[$centerValue];
				}

				$finalValues[$j + $i * $sizeX] = $centerValue;
			}
		}

		return $finalValues;
	}
}
