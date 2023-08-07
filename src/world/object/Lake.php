<?php

declare(strict_types=1);

namespace pocketmine\world\object;

use pocketmine\world\overworld\biome\BiomeClimateManager;
use pocketmine\world\overworld\biome\BiomeIds;
use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\Liquid;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Wood;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use function array_key_exists;
use function intdiv;

class Lake extends TerrainObject{
	private const MAX_DIAMETER_INT = 16;
	private const MAX_HEIGHT_INT = 8;
	private const MAX_DIAMETER =  self::MAX_DIAMETER_INT / 1.0;
	private const MAX_HEIGHT = self::MAX_HEIGHT_INT / 1.0;

	/** @var int[] */
	private static array $MYCEL_BIOMES;

	public static function init() : void{
		self::$MYCEL_BIOMES = [];
		foreach([BiomeIds::MUSHROOM_ISLAND, BiomeIds::MUSHROOM_ISLAND_SHORE] as $blockId){
			self::$MYCEL_BIOMES[$blockId] = $blockId;
		}
	}

	private Block $type;

	public function __construct(Block $type){
		$this->type = $type;
	}

	public function generate(ChunkManager $world, Random $random, int $sourceX, int $sourceY, int $sourceZ) : bool{
		$heightHalf = intdiv(self::MAX_HEIGHT_INT, 2);
		$diameterHalf = intdiv(self::MAX_DIAMETER_INT, 2);

		$dirt = VanillaBlocks::DIRT()->getStateId();
		$ice = VanillaBlocks::ICE()->getStateId();
		$packedIce = VanillaBlocks::PACKED_ICE()->getStateId();
		$stillWater = VanillaBlocks::WATER()->setStill()->getStateId();

		$succeeded = false;
		$sourceY -= $heightHalf;

		$lakeMap = [];
		for($n = 0; $n < $random->nextBoundedInt(4) + 4; ++$n){
			$sizeX = $random->nextFloat() * 6.0 + 3;
			$sizeY = $random->nextFloat() * 4.0 + 2;
			$sizeZ = $random->nextFloat() * 6.0 + 3;
			$dx = $random->nextFloat() * (self::MAX_DIAMETER - $sizeX - 2) + 1 + $sizeX / 2.0;
			$dy = $random->nextFloat() * (self::MAX_HEIGHT - $sizeY - 4) + 2 + $sizeY / 2.0;
			$dz = $random->nextFloat() * (self::MAX_DIAMETER - $sizeZ - 2) + 1 + $sizeZ / 2.0;
			for($x = 1; $x < self::MAX_DIAMETER_INT - 1; ++$x){
				for($z = 1; $z < self::MAX_DIAMETER_INT - 1; ++$z){
					for($y = 1; $y < self::MAX_HEIGHT_INT - 1; ++$y){
						$nx = 2.0 * ($x - $dx) / $sizeX;
						$nx *= $nx;
						$ny = 2.0 * ($y - $dy) / $sizeY;
						$ny *= $ny;
						$nz = 2.0 * ($z - $dz) / $sizeZ;
						$nz *= $nz;
						if($nx + $ny + $nz < 1.0){
							$this->setLakeBlock($lakeMap, $x, $y, $z);
							$succeeded = true;
						}
					}
				}
			}
		}

		if(!$this->canPlace($lakeMap, $world, $sourceX, $sourceY, $sourceZ)){
			return $succeeded;
		}

		/** @var Chunk $chunk */
		$chunk = $world->getChunk($sourceX >> 4, $sourceZ >> 4);
		$biome = $chunk->getBiomeId(($sourceX + 8 + $diameterHalf) & 0x0f, $sourceY, ($sourceZ + 8 + $diameterHalf) & 0x0f);
		$mycelBiome = array_key_exists($biome, self::$MYCEL_BIOMES);

		for($x = 0; $x < self::MAX_DIAMETER_INT; ++$x){
			for($z = 0; $z < self::MAX_DIAMETER_INT; ++$z){
				for($y = 0; $y < self::MAX_HEIGHT_INT; ++$y){
					if(!$this->isLakeBlock($lakeMap, $x, $y, $z)){
						continue;
					}

					$type = $this->type;
					$block = $world->getBlockAt($sourceX + $x, $sourceY + $y, $sourceZ + $z);
					$blockAbove = $world->getBlockAt($sourceX + $x, $sourceY + $y + 1, $sourceZ + $z);
					$blockType = $block->getStateId();
					if(($blockType === $dirt && $blockAbove instanceof Wood) || $block instanceof Wood){
						continue;
					}

					if($y >= $heightHalf){
						$type = VanillaBlocks::AIR();
						if(TerrainObject::killWeakBlocksAbove($world, $sourceX + $x, $sourceY + $y, $sourceZ + $z)){
							break;
						}

						if(($blockType === $ice || $blockType === $packedIce) && $this->type->getStateId() === $stillWater){
							$type = $block;
						}
					}elseif($y === $heightHalf - 1){
						if($type->getStateId() === $stillWater && BiomeClimateManager::isCold($chunk->getBiomeId($x & 0x0f,  $y,$z & 0x0f), $sourceX + $x, $y, $sourceZ + $z)){
							$type = VanillaBlocks::ICE();
						}
					}
					$world->setBlockAt($sourceX + $x, $sourceY + $y, $sourceZ + $z, $type);
				}
			}
		}

		for($x = 0; $x < self::MAX_DIAMETER_INT; ++$x){
			for($z = 0; $z < self::MAX_DIAMETER_INT; ++$z){
				for($y = $heightHalf; $y < self::MAX_HEIGHT_INT; ++$y){
					if(!$this->isLakeBlock($lakeMap, $x, $y, $z)){
						continue;
					}

					$block = $world->getBlockAt($sourceX + $x, $sourceY + $y - 1, $sourceZ + $z);
					$blockAbove = $world->getBlockAt($sourceX + $x, $sourceY + $y, $sourceZ + $z);
					if($block->getTypeId() === BlockTypeIds::DIRT && $blockAbove->isTransparent() && $blockAbove->getLightLevel() > 0){
						$world->setBlockAt($sourceX + $x, $sourceY + $y - 1, $sourceZ + $z, $mycelBiome ? VanillaBlocks::MYCELIUM() : VanillaBlocks::GRASS());
					}
				}
			}
		}
		return $succeeded;
	}

	/**
	 * @param int[] $lakeMap
	 */
	private function canPlace(array $lakeMap, ChunkManager $world, int $sourceX, int $sourceY, int $sourceZ) : bool{
		$ice = VanillaBlocks::ICE()->getStateId();
		for($x = 0; $x < self::MAX_DIAMETER; ++$x){
			for($z = 0; $z < self::MAX_DIAMETER; ++$z){
				for($y = 0; $y < self::MAX_HEIGHT; ++$y){
					if($this->isLakeBlock($lakeMap, $x, $y, $z)
						|| ((($x >= (self::MAX_DIAMETER - 1)) || !$this->isLakeBlock($lakeMap, $x + 1, $y, $z))
							&& (($x <= 0) || !$this->isLakeBlock($lakeMap, $x - 1, $y, $z))
							&& (($z >= (self::MAX_DIAMETER - 1)) || !$this->isLakeBlock($lakeMap, $x, $y, $z + 1))
							&& (($z <= 0) || !$this->isLakeBlock($lakeMap, $x, $y, $z - 1))
							&& (($z >= (self::MAX_HEIGHT - 1)) || !$this->isLakeBlock($lakeMap, $x, $y + 1, $z))
							&& (($z <= 0) || !$this->isLakeBlock($lakeMap, $x, $y - 1, $z)))){
						continue;
					}
					$block = $world->getBlockAt($sourceX + $x, $sourceY + $y, $sourceZ + $z);
					if($y >= self::MAX_HEIGHT / 2 && (($block instanceof Liquid) || $block->getStateId() === $ice)){
						return false; // there's already some liquids above
					}
					if($y < self::MAX_HEIGHT / 2 && !$block->isSolid() && $block->getTypeId() !== $this->type->getTypeId()){
						return false;
						// bottom must be solid and do not overlap with another liquid type
					}
				}
			}
		}
		return true;
	}

	/**
	 * @param int[] $lakeMap
	 */
	private function isLakeBlock(array $lakeMap, int $x, int $y, int $z) : bool{
		return ($lakeMap[($x * self::MAX_DIAMETER_INT + $z) * self::MAX_HEIGHT_INT + $y] ?? 0) !== 0;
	}

	/**
	 * @param int[] $lakeMap
	 */
	private function setLakeBlock(array &$lakeMap, int $x, int $y, int $z) : void{
		$lakeMap[($x * self::MAX_DIAMETER_INT + $z) * self::MAX_HEIGHT_INT + $y] = 1;
	}
}
Lake::init();
