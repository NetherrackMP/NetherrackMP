<?php

declare(strict_types=1);

namespace pocketmine\world\nether;

use pocketmine\world\Environment;
use pocketmine\world\nether\populator\NetherPopulator;
use pocketmine\world\noise\glowstone\PerlinOctaveGenerator;
use pocketmine\world\overworld\WorldType;
use pocketmine\world\utils\MathHelper;
use pocketmine\world\utils\NetherWorldOctaves;
use pocketmine\world\utils\preset\SimpleGeneratorPreset;
use pocketmine\world\VanillaBiomeGrid;
use pocketmine\world\VanillaGenerator;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\World;
use function intdiv;

/**
 * @phpstan-extends VanillaGenerator<NetherWorldOctaves<PerlinOctaveGenerator, PerlinOctaveGenerator, PerlinOctaveGenerator, PerlinOctaveGenerator, PerlinOctaveGenerator, PerlinOctaveGenerator>>
 */
class NetherGenerator extends VanillaGenerator{

	protected const COORDINATE_SCALE = 684.412;
	protected const HEIGHT_SCALE = 2053.236;
	protected const HEIGHT_NOISE_SCALE_X = 100.0;
	protected const HEIGHT_NOISE_SCALE_Z = 100.0;
	protected const DETAIL_NOISE_SCALE_X = 80.0;
	protected const DETAIL_NOISE_SCALE_Y = 60.0;
	protected const DETAIL_NOISE_SCALE_Z = 80.0;
	protected const SURFACE_SCALE = 0.0625;

	/**
	 * @param int $i 0-4
	 * @param int $j 0-4
	 * @param int $k 0-32
	 */
	private static function densityHash(int $i, int $j, int $k) : int{
		return ($k << 6) | ($j << 3) | $i;
	}

	protected int $bedrockRoughness = 5;

	public function __construct(int $seed, string $presetString){
		$preset = SimpleGeneratorPreset::parse($presetString);
		parent::__construct(
			$seed,
			$preset->exists("environment") ? Environment::fromString($preset->getString("environment")) : Environment::NETHER,
			$preset->exists("worldtype") ? WorldType::fromString($preset->getString("worldtype")) : null,
			$preset
		);
		$this->addPopulators(new NetherPopulator($this->getMaxY())); // This isn't faithful to original code. Was $world->getWorldHeight()
	}

	public function getBedrockRoughness() : int{
		return $this->bedrockRoughness;
	}

	public function setBedrockRoughness(int $bedrockRoughness) : void{
		$this->bedrockRoughness = $bedrockRoughness;
	}

	public function getMaxY() : int{
		return 128;
	}

	protected function generateChunkData(ChunkManager $world, int $chunkX, int $chunkZ, VanillaBiomeGrid $biomes) : void{
		$this->generateRawTerrain($world, $chunkX, $chunkZ);
		$cx = $chunkX << 4;
		$cz = $chunkZ << 4;

		/** @var NetherWorldOctaves $octaves */
		$octaves = $this->getWorldOctaves();

		$surfaceNoise = $octaves->surface->getFractalBrownianMotion($cx, $cz, 0, 0.5, 2.0);
		$soulSandNoise = $octaves->soulSand->getFractalBrownianMotion($cx, $cz, 0, 0.5, 2.0);
		$graveNoise = $octaves->gravel->getFractalBrownianMotion($cx, 0, $cz, 0.5, 2.0);

		/** @var Chunk $chunk */
		$chunk = $world->getChunk($chunkX, $chunkZ);

		for($x = 0; $x < 16; ++$x){
			for($z = 0; $z < 16; ++$z){
				$biome = $biomes->getBiome($x, $z);
				for($y = 0; $y < World::Y_MAX; ++$y)
					$chunk->setBiomeId($x, $y, $z, $biome);

				$this->generateTerrainColumn($world, $cx + $x, $cz + $z, $surfaceNoise[$x | $z << 4], $soulSandNoise[$x | $z << 4], $graveNoise[$x | $z << 4]);
			}
		}
	}

	protected function createWorldOctaves() : NetherWorldOctaves{
		$seed = new Random($this->random->getSeed());

		$height = PerlinOctaveGenerator::fromRandomAndOctaves($seed, 16, 5, 1, 5);
		$height->setXScale(static::HEIGHT_NOISE_SCALE_X);
		$height->setZScale(static::HEIGHT_NOISE_SCALE_Z);

		$roughness = PerlinOctaveGenerator::fromRandomAndOctaves($seed, 16, 5, 17, 5);
		$roughness->setXScale(static::COORDINATE_SCALE);
		$roughness->setYScale(static::HEIGHT_SCALE);
		$roughness->setZScale(static::COORDINATE_SCALE);

		$roughness2 = PerlinOctaveGenerator::fromRandomAndOctaves($seed, 16, 5, 17, 5);
		$roughness2->setXScale(static::COORDINATE_SCALE);
		$roughness2->setYScale(static::HEIGHT_SCALE);
		$roughness2->setZScale(static::COORDINATE_SCALE);

		$detail = PerlinOctaveGenerator::fromRandomAndOctaves($seed, 8, 5, 17, 5);
		$detail->setXScale(static::COORDINATE_SCALE / static::DETAIL_NOISE_SCALE_X);
		$detail->setYScale(static::HEIGHT_SCALE / static::DETAIL_NOISE_SCALE_Y);
		$detail->setZScale(static::COORDINATE_SCALE / static::DETAIL_NOISE_SCALE_Z);

		$surface = PerlinOctaveGenerator::fromRandomAndOctaves($seed, 4, 16, 16, 1);
		$surface->setScale(static::SURFACE_SCALE);

		$soulsand = PerlinOctaveGenerator::fromRandomAndOctaves($seed, 4, 16, 16, 1);
		$soulsand->setXScale(static::SURFACE_SCALE / 2.0);
		$soulsand->setYScale(static::SURFACE_SCALE / 2.0);

		$gravel = PerlinOctaveGenerator::fromRandomAndOctaves($seed, 4, 16, 1, 16);
		$gravel->setXScale(static::SURFACE_SCALE / 2.0);
		$gravel->setZScale(static::SURFACE_SCALE / 2.0);

		return new NetherWorldOctaves($height, $roughness, $roughness2, $detail, $surface, $soulsand, $gravel);
	}

	private function generateRawTerrain(ChunkManager $world, int $chunkX, int $chunkZ) : void{
		$density = $this->generateTerrainDensity($chunkX << 2, $chunkZ << 2);

		$netherRack = VanillaBlocks::NETHERRACK()->getStateId();
		$stillLava = VanillaBlocks::LAVA()->getStillForm()->getStateId();

		/** @var Chunk $chunk */
		$chunk = $world->getChunk($chunkX, $chunkZ);

		for ($i = 0; $i < 5 - 1; ++$i) {
			for ($j = 0; $j < 5 - 1; ++$j) {
				for ($k = 0; $k < 17 - 1; ++$k) {
					$d1 = $density[self::densityHash($i, $j, $k)];
					$d2 = $density[self::densityHash($i + 1, $j, $k)];
					$d3 = $density[self::densityHash($i, $j + 1, $k)];
					$d4 = $density[self::densityHash($i + 1, $j + 1, $k)];
					$d5 = ($density[self::densityHash($i, $j, $k + 1)] - $d1) / 8;
					$d6 = ($density[self::densityHash($i + 1, $j, $k + 1)] - $d2) / 8;
					$d7 = ($density[self::densityHash($i, $j + 1, $k + 1)] - $d3) / 8;
					$d8 = ($density[self::densityHash($i + 1, $j + 1, $k + 1)] - $d4) / 8;

					for ($l = 0; $l < 8; ++$l) {
						$d9 = $d1;
						$d10 = $d3;

						$yPos = $l + ($k << 3);
						$yBlockPos = $yPos & 0xf;
						$subChunk = $chunk->getSubChunk($yPos >> 4);

						for ($m = 0; $m < 4; ++$m) {
							$dens = $d9;
							for ($n = 0; $n < 4; ++$n) {
								// any density higher than 0 is ground, any density lower or equal
								// to 0 is air (or lava if under the lava level).
								if ($dens > 0) {
									$subChunk->setBlockStateId($m + ($i << 2), $yBlockPos, $n + ($j << 2), $netherRack);
								} elseif ($l + ($k << 3) < 32) {
									$subChunk->setBlockStateId($m + ($i << 2), $yBlockPos, $n + ($j << 2), $stillLava);
								}
								// interpolation along z
								$dens += ($d10 - $d9) / 4;
							}
							// interpolation along x
							$d9 += ($d2 - $d1) / 4;
							// interpolate along z
							$d10 += ($d4 - $d3) / 4;
						}
						// interpolation along y
						$d1 += $d5;
						$d3 += $d7;
						$d2 += $d6;
						$d4 += $d8;
					}
				}
			}
		}
	}

	/**
	 * @return float[]
	 */
	private function generateTerrainDensity(int $x, int $z) : array{
		$octaves = $this->getWorldOctaves();
		$roughnessNoise = $octaves->roughness->getFractalBrownianMotion($x, 0, $z, 0.5, 2.0);
		$roughnessNoise2 = $octaves->roughness2->getFractalBrownianMotion($x, 0, $z, 0.5, 2.0);
		$detailNoise = $octaves->detail->getFractalBrownianMotion($x, 0, $z, 0.5, 2.0);

		$kMax = $octaves->detail->getSizeY();

		static $nv = null;
		if($nv === null){
			$nv = [];
			for($i = 0; $i < $kMax; ++$i){
				$nv[$i] = MathHelper::getInstance()->cos($i * M_PI * 6.0 / $kMax) * 2.0;
				$nh = $i > intdiv($kMax, 2) ? $kMax - 1 - $i : $i;
				if($nh < 4.0){
					$nh = 4.0 - $nh;
					$nv[$i] -= $nh * $nh * $nh * 10.0;
				}
			}
		}

		$index = 0;

		$density = [];

		for($i = 0; $i < 5; ++$i){
			for($j = 0; $j < 5; ++$j){
				for($k = 0; $k < $kMax; ++$k){
					$noiseR = $roughnessNoise[$index] / 512.0;
					$noiseR2 = $roughnessNoise2[$index] / 512.0;
					$noiseD = ($detailNoise[$index] / 10.0 + 1.0) / 2.0;
					$nh = $nv[$k];
					// linear interpolation
					$dens = $noiseD < 0 ? $noiseR : ($noiseD > 1 ? $noiseR2 : $noiseR + ($noiseR2 - $noiseR) * $noiseD);
					$dens -= $nh;
					++$index;
					$kCap = $kMax - 4;
					if($k > $kCap){
						$lowering = ($k - $kCap) / 3.0;
						$dens = $dens * (1.0 - $lowering) + $lowering * -10.0;
					}
					$density[self::densityHash($i, $j, $k)] = $dens;
				}
			}
		}

		return $density;
	}

	public function generateTerrainColumn(ChunkManager $world, int $x, int $z, float $surfaceNoise, float $soulSandNoise, float $graveNoise) : void{
		$soulSand = $soulSandNoise + $this->random->nextFloat() * 0.2 > 0;
		$gravel = $graveNoise + $this->random->nextFloat() * 0.2 > 0;

		$surfaceHeight = (int) ($surfaceNoise / 3.0 + 3.0 + $this->random->nextFloat() * 0.25);
		$deep = -1;
		$worldHeight = $this->getMaxY();
		$worldHeightM1 = $worldHeight - 1;

		$blockBedrock = VanillaBlocks::BEDROCK()->getStateId();
		$blockAir = VanillaBlocks::AIR()->getStateId();
		$blockNetherRack = VanillaBlocks::NETHERRACK()->getStateId();
		$blockGravel = VanillaBlocks::GRAVEL()->getStateId();
		$blockSoulSand = VanillaBlocks::SOUL_SAND()->getStateId();

		$topMat = $blockNetherRack;
		$groundMat = $blockNetherRack;

		/** @var Chunk $chunk */
		$chunk = $world->getChunk($x >> 4, $z >> 4);
		$chunkBlockX = $x & 0x0f;
		$chunkBlockZ = $z & 0x0f;

		for($y = $worldHeightM1; $y >= 0; --$y){
			if($y <= $this->random->nextBoundedInt($this->bedrockRoughness) || $y >= $worldHeightM1 - $this->random->nextBoundedInt($this->bedrockRoughness)){
				$chunk->setBlockStateId($chunkBlockX, $y, $chunkBlockZ, $blockBedrock);
				continue;
			}
			$mat = $chunk->getBlockStateId($chunkBlockX, $y, $chunkBlockZ);
			if($mat === $blockAir){
				$deep = -1;
			}elseif($mat === $blockNetherRack){
				if($deep === -1){
					if($surfaceHeight <= 0){
						$topMat = $blockAir;
						$groundMat = $blockNetherRack;
					}elseif($y >= 60 && $y <= 65){
						$topMat = $blockNetherRack;
						$groundMat = $blockNetherRack;
						if($gravel){
							$topMat = $blockGravel;
						}
						if($soulSand){
							$topMat = $blockSoulSand;
							$groundMat = $blockSoulSand;
						}
					}

					$deep = $surfaceHeight;
					if($y >= 63){
						$chunk->setBlockStateId($chunkBlockX, $y, $chunkBlockZ, $topMat);
					}else{
						$chunk->setBlockStateId($chunkBlockX, $y, $chunkBlockZ, $groundMat);
					}
				}elseif($deep > 0){
					--$deep;
					$chunk->setBlockStateId($chunkBlockX, $y, $chunkBlockZ, $groundMat);
				}
			}
		}
	}
}
