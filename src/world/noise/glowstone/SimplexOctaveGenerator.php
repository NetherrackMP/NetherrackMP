<?php

declare(strict_types=1);

namespace pocketmine\world\noise\glowstone;

use pocketmine\utils\Random;
use function array_fill;

class SimplexOctaveGenerator extends PerlinOctaveGenerator{

	/**
	 * @return SimplexNoise[]
	 */
	protected static function createOctaves(Random $rand, int $octaves) : array{
		$result = [];

		for($i = 0; $i < $octaves; ++$i){
			$result[$i] = new SimplexNoise($rand);
		}

		return $result;
	}

	/**
	 * @return SimplexOctaveGenerator
	 */
	public static function fromRandomAndOctaves(Random $random, int $octaves, int $sizeX, int $sizeY, int $sizeZ) : self{
		return new SimplexOctaveGenerator(self::createOctaves($random, $octaves), $sizeX, $sizeY, $sizeZ);
	}

	public function getFractalBrownianMotion(float $x, float $y, float $z, float $lacunarity, float $persistence) : array{
		$this->noise = array_fill(0, $this->sizeX * $this->sizeY * $this->sizeZ, 0.0);

		$freq = 1.0;
		$amp = 1.0;

		// fBm
		/** @var SimplexNoise $octave */
		foreach($this->octaves as $octave){
			$this->noise = $octave->getNoise($this->noise, $x, $y, $z, $this->sizeX, $this->sizeY, $this->sizeZ, $this->xScale * $freq, $this->yScale * $freq, $this->zScale * $freq, 0.55 / $amp);
			$freq *= $lacunarity;
			$amp *= $persistence;
		}

		return $this->noise;
	}
}
