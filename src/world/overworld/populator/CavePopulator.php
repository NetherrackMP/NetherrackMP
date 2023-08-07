<?php

namespace pocketmine\world\overworld\populator;

use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\Liquid;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Math;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\Populator;
use pocketmine\world\utils\CaveRandom;

class CavePopulator implements Populator
{

	// The density of vanilla caves. Higher = more caves, closer together.
	// Default: 14 (value used in vanilla)
	const CAVE_DENSITY = 15;

	// The maximum y-coordinate at which vanilla caves can generate.
	// Default: 128
	const CAVE_MAX_Y = 40;

	// The minimum y-coordinate at which vanilla caves can generate.
	// Default: 8
	const CAVE_MIN_Y = 8;

	// Default cave range
	const CAVE_RANGE = 12;

	// Lava (or water in water regions) spawns at and below this y-coordinate.
	// Default: 10
	const CAVE_LIQUID_ALTITUDE = 10;

	private CaveRandom $random;

	/**
	 * @param ChunkManager $world
	 * @param Random $random
	 * @param int $chunkX
	 * @param int $chunkZ
	 * @param Chunk $chunk
	 * @return void
	 */
	public function populate(ChunkManager $world, Random $random, int $chunkX, int $chunkZ, Chunk $chunk): void
	{
		$this->random = new CaveRandom($random->getSeed());
		$allCondition = [];

		for ($x = 0; $x < 16; $x++) {
			for ($z = 0; $z < 16; $z++) {
				$allCondition[$x][$z] = true;
			}
		}
		$j = $this->random->nextLong();
		$k = $this->random->nextLong();
		$chunk = $world->getChunk($chunkX, $chunkZ);

		for ($currentChunkX = $chunkX - self::CAVE_RANGE; $currentChunkX <= $chunkX + self::CAVE_RANGE; $currentChunkX++) {
			for ($currentChunkZ = $chunkZ - self::CAVE_RANGE; $currentChunkZ <= $chunkZ + self::CAVE_RANGE; $currentChunkZ++) {
				$rx = (int)($currentChunkX * $j);
				$rz = (int)($currentChunkZ * $k);
				$this->random->setSeed((int)floor($rx ^ $rz ^ $random->getSeed()));
				$this->recursiveGenerate($currentChunkX, $currentChunkZ, $chunkX, $chunkZ, $chunk, true, $allCondition);
			}
		}
	}

	/**
	 * @param int $chunkX
	 * @param int $chunkZ
	 * @param int $refChunkX
	 * @param int $refChunkZ
	 * @param Chunk $chunk
	 * @param bool $addRooms
	 * @param array $carvingMask
	 * @return void
	 */
	protected function recursiveGenerate(int $chunkX, int $chunkZ, int $refChunkX, int $refChunkZ, Chunk $chunk, bool $addRooms = true, array $carvingMask = []): void
	{
		$numAttempts = $this->random->nextBoundedInt($this->random->nextBoundedInt($this->random->nextBoundedInt(15) + 1) + 1);

		if ($this->random->nextBoundedInt(100) > self::CAVE_DENSITY) {
			$numAttempts = 0;
		}
		for ($i = 0; $i < $numAttempts; ++$i) {
			$caveStartX = $chunkX * 16 + $this->random->nextBoundedInt(16);
			$caveStartY = $this->random->nextBoundedInt(self::CAVE_MAX_Y - self::CAVE_MIN_Y) + self::CAVE_MIN_Y;
			$caveStartZ = $chunkZ * 16 + $this->random->nextBoundedInt(16);
			$numAddTunnelCalls = 1;

			if ($addRooms && $this->random->nextBoundedInt(4) == 0) {
				$this->addRoom($this->random->nextLong(), $chunk, $refChunkX, $refChunkZ, $caveStartX, $caveStartY, $caveStartZ, $carvingMask);
				$numAddTunnelCalls += $this->random->nextBoundedInt(4);
			}
			for ($j = 0; $j < $numAddTunnelCalls; ++$j) {
				$yaw = $this->random->nextFloat() * ((float)M_PI * 2);
				$pitch = ($this->random->nextFloat() - 0.5) * 2.0 / 8.0;
				$width = $this->random->nextFloat() * 2.0 + $this->random->nextFloat();

				if ($addRooms && $this->random->nextBoundedInt(10) == 0) {
					$width *= $this->random->nextFloat() * $this->random->nextFloat() * 3.0 + 1.0;
				}
				$this->addTunnel($this->random->nextLong(), $chunk, $refChunkX, $refChunkZ, $caveStartX, $caveStartY, $caveStartZ, $width, $yaw, $pitch, 0, 0, 1.0, $carvingMask);
			}
		}
	}

	/**
	 * @param int $seed
	 * @param Chunk $chunk
	 * @param int $refChunkX
	 * @param int $refChunkZ
	 * @param float $caveStartX
	 * @param float $caveStartY
	 * @param float $caveStartZ
	 * @param array $carvingMask
	 * @return void
	 */
	private function addRoom(int $seed, Chunk $chunk, int $refChunkX, int $refChunkZ, float $caveStartX, float $caveStartY, float $caveStartZ, array $carvingMask = []): void
	{
		$this->addTunnel($seed, $chunk, $refChunkX, $refChunkZ, $caveStartX, $caveStartY, $caveStartZ, 1.0 + $this->random->nextFloat() * 6.0, 0.0, 0.0, -1, -1, 0.5, $carvingMask);
	}

	/**
	 * @param int $seed
	 * @param Chunk $chunk
	 * @param int $refChunkX
	 * @param int $refChunkZ
	 * @param float $caveStartX
	 * @param float $caveStartY
	 * @param float $caveStartZ
	 * @param float $width
	 * @param float $yaw
	 * @param float $pitch
	 * @param int $startCounter
	 * @param int $endCounter
	 * @param float $heightModifier
	 * @param array $carvingMask
	 * @return void
	 */
	private function addTunnel(int $seed, Chunk $chunk, int $refChunkX, int $refChunkZ, float $caveStartX, float $caveStartY, float $caveStartZ, float $width, float $yaw, float $pitch, int $startCounter, int $endCounter, float $heightModifier, array $carvingMask = []): void
	{
		$random = new CaveRandom($seed);
		$originBlockX = ($refChunkX * 16 + 8);
		$originBlockZ = ($refChunkZ * 16 + 8);
		$yawModifier = 0.0;
		$pitchModifier = 0.0;

		if ($endCounter <= 0) {
			$i = self::CAVE_RANGE * 16 - 16;
			$endCounter = $i - $random->nextBoundedInt((int)($i / 4));
		}
		$comesFromRoom = false;

		if ($startCounter == -1) {
			$startCounter = $endCounter / 2;
			$comesFromRoom = true;
		}
		$randomCounterValue = $random->nextBoundedInt((int)($endCounter / 2)) + $endCounter / 4;

		while ($startCounter < $endCounter) {
			$xzOffset = 1.5 + sin((float)$startCounter * M_PI / (float)$endCounter) * $width;
			$yOffset = $xzOffset * $heightModifier;
			$pitchXZ = cos($pitch);
			$pitchY = sin($pitch);
			$caveStartX += cos($yaw) * $pitchXZ;
			$caveStartY += $pitchY;
			$caveStartZ += sin($yaw) * $pitchXZ;
			$flag = $random->nextBoundedInt(6) == 0;

			if ($flag) {
				$pitch = $pitch * 0.92;
			} else {
				$pitch = $pitch * 0.7;
			}
			$pitch = $pitch + $pitchModifier * 0.1;
			$yaw += $yawModifier * 0.1;
			$pitchModifier = $pitchModifier * 0.9;
			$yawModifier = $yawModifier * 0.75;
			$pitchModifier = $pitchModifier + ($random->nextFloat() - $random->nextFloat()) * $random->nextFloat() * 2.0;
			$yawModifier = $yawModifier + ($random->nextFloat() - $random->nextFloat()) * $random->nextFloat() * 4.0;

			if ((!$comesFromRoom) && ($startCounter === $randomCounterValue) && ($width > 1.0) && ($endCounter > 0)) {
				$this->addTunnel($random->nextLong(), $chunk, $refChunkX, $refChunkZ, $caveStartX, $caveStartY, $caveStartZ, $random->nextFloat() * 0.5 + 0.5, $yaw - ((float)M_PI / 2), $pitch / 3.0, $startCounter, $endCounter, 1.0, $carvingMask);
				$this->addTunnel($random->nextLong(), $chunk, $refChunkX, $refChunkZ, $caveStartX, $caveStartY, $caveStartZ, $random->nextFloat() * 0.5 + 0.5, $yaw + ((float)M_PI / 2), $pitch / 3.0, $startCounter, $endCounter, 1.0, $carvingMask);
				return;
			}
			if ($comesFromRoom || $random->nextBoundedInt(4) != 0) {
				$caveStartXOffsetFromCenter = $caveStartX - $originBlockX;
				$caveStartZOffsetFromCenter = $caveStartZ - $originBlockZ;
				$distanceToEnd = $endCounter - $startCounter;
				$d7 = $width + 2.0 + 16.0;

				if ($caveStartXOffsetFromCenter * $caveStartXOffsetFromCenter + $caveStartZOffsetFromCenter * $caveStartZOffsetFromCenter - $distanceToEnd * $distanceToEnd > $d7 * $d7) {
					return;
				}
				if ($caveStartX >= $originBlockX - 16.0 - $xzOffset * 2.0 && $caveStartZ >= $originBlockZ - 16.0 - $xzOffset * 2.0 && $caveStartX <= $originBlockX + 16.0 + $xzOffset * 2.0 && $caveStartZ <= $originBlockZ + 16.0 + $xzOffset * 2.0) {
					$minX = Math::floorFloat($caveStartX - $xzOffset) - $refChunkX * 16 - 1;
					$minY = Math::floorFloat($caveStartY - $yOffset) - 1;
					$minZ = Math::floorFloat($caveStartZ - $xzOffset) - $refChunkZ * 16 - 1;
					$maxX = Math::floorFloat($caveStartX + $xzOffset) - $refChunkX * 16 + 1;
					$maxY = Math::floorFloat($caveStartY + $yOffset) + 1;
					$maxZ = Math::floorFloat($caveStartZ + $xzOffset) - $refChunkZ * 16 + 1;

					if ($minX < 0) $minX = 0;
					if ($maxX > 16) $maxX = 16;
					if ($minY < 1) $minY = 1;
					if ($maxY > 248) $maxY = 248;
					if ($minZ < 0) $minZ = 0;
					if ($maxZ > 16) $maxZ = 16;

					for ($currX = $minX; $currX < $maxX; ++$currX) {
						$xAxisDist = ((double)($currX + $refChunkX * 16) + 0.5 - $caveStartX) / $xzOffset;

						for ($currZ = $minZ; $currZ < $maxZ; ++$currZ) {
							$zAxisDist = ((double)($currZ + $refChunkZ * 16) + 0.5 - $caveStartZ) / $xzOffset;

							if (!$carvingMask[$currX][$currZ]) continue;
							if ($xAxisDist * $xAxisDist + $zAxisDist * $zAxisDist < 1.0) {
								for ($currY = $maxY; $currY > $minY; --$currY) {
									$yAxisDist = ((double)($currY - 1) + 0.5 - $caveStartY) / $yOffset;

									if ($yAxisDist > -0.7 && $xAxisDist * $xAxisDist + $yAxisDist * $yAxisDist + $zAxisDist * $zAxisDist < 1.0) {
										$this->digBlock($chunk, $currX, $currY, $currZ, self::CAVE_LIQUID_ALTITUDE);
									}
								}
							}
						}
					}
					if ($comesFromRoom) {
						break;
					}
				}
			}
			$startCounter++;
		}
	}

	/**
	 * @param Chunk $chunk
	 * @param int $currX
	 * @param int $currY
	 * @param int $currZ
	 * @param int $caveLiquidAltitude
	 * @return void
	 */
	private function digBlock(Chunk $chunk, int $currX, int $currY, int $currZ, int $caveLiquidAltitude): void
	{
		$block = RuntimeBlockStateRegistry::getInstance()->fromStateId($chunk->getBlockStateId($currX, $currY, $currZ));
		$blockAbove = RuntimeBlockStateRegistry::getInstance()->fromStateId($chunk->getBlockStateId($currX, $currY + 1, $currZ));

		if (self::canReplaceBlock($block, $blockAbove)) {
			$hasWaterAround = false;
			$waterState = VanillaBlocks::WATER()->getStateId();
			$waterStillState = VanillaBlocks::WATER()->getStillForm()->getStateId();
			foreach ([[0, 0, 1], [0, 0, -1], [1, 0, 0], [-1, 0, 0], [0, 1, 0]] as $a) {
				$block = $chunk->getBlockStateId($currX + $a[0], $currY + $a[1], $currZ + $a[2]);
				if ($block == $waterState || $block == $waterStillState) {
					$hasWaterAround = true;
					break;
				}
			}
			if ($currY - 1 < $caveLiquidAltitude) {
				$chunk->setBlockStateId($currX, $currY, $currZ, $hasWaterAround ? VanillaBlocks::OBSIDIAN()->getStateId() : VanillaBlocks::LAVA()->getStateId());
			} else {
				$chunk->setBlockStateId($currX, $currY, $currZ, $hasWaterAround ? $waterStillState : VanillaBlocks::AIR()->getStateId());
			}
		}
	}

	/**
	 * @param Block $block
	 * @param Block $blockAbove
	 * @return bool
	 */
	public static function canReplaceBlock(Block $block, Block $blockAbove): bool
	{
		if (in_array($block->getTypeId(), [BlockTypeIds::OAK_LEAVES, BlockTypeIds::ACACIA_LEAVES, BlockTypeIds::DARK_OAK_LEAVES, BlockTypeIds::BIRCH_LEAVES, BlockTypeIds::AZALEA_LEAVES, BlockTypeIds::FLOWERING_AZALEA_LEAVES, BlockTypeIds::JUNGLE_LEAVES, BlockTypeIds::MANGROVE_LEAVES, BlockTypeIds::SPRUCE_LEAVES, BlockTypeIds::OAK_LOG, BlockTypeIds::DARK_OAK_LOG, BlockTypeIds::ACACIA_LOG, BlockTypeIds::BIRCH_LOG, BlockTypeIds::JUNGLE_LOG, BlockTypeIds::MANGROVE_LOG, BlockTypeIds::SPRUCE_LOG])) {
			return false;
		}
		if (in_array($blockAbove->getTypeId(), [BlockTypeIds::OAK_LOG, BlockTypeIds::DARK_OAK_LOG, BlockTypeIds::ACACIA_LOG, BlockTypeIds::BIRCH_LOG, BlockTypeIds::JUNGLE_LOG, BlockTypeIds::MANGROVE_LOG, BlockTypeIds::SPRUCE_LOG])) {
			return false;
		}
		if (in_array($block->getTypeId(), [
			BlockTypeIds::STONE,
			BlockTypeIds::DIRT,
			BlockTypeIds::GRASS,
			BlockTypeIds::HARDENED_CLAY,
			BlockTypeIds::STAINED_CLAY,
			BlockTypeIds::SANDSTONE,
			BlockTypeIds::RED_SANDSTONE,
			BlockTypeIds::MYCELIUM,
			BlockTypeIds::SNOW_LAYER])) {
			return true;
		}
		return ($block->getTypeId() === BlockTypeIds::SAND || $block->getTypeId() === BlockTypeIds::GRAVEL) && !($blockAbove instanceof Liquid);
	}
}
