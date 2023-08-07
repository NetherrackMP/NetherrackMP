<?php

declare(strict_types=1);

namespace pocketmine\world\overworld\decorator;

use pocketmine\world\Decorator;
use pocketmine\world\noise\glowstone\PerlinOctaveGenerator;
use pocketmine\world\utils\MathHelper;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use function abs;
use function count;
use function deg2rad;
use function floor;

class SurfaceCaveDecorator extends Decorator{

	public function decorate(ChunkManager $world, Random $random, int $chunkX, int $chunkZ, Chunk $chunk) : void{
		if($random->nextBoundedInt(8) !== 0){
			return;
		}

		$cx = $chunkX << 4;
		$cz = $chunkZ << 4;

		$startCx = $random->nextBoundedInt(16);
		$startCz = $random->nextBoundedInt(16);
		$startY = $chunk->getHeightMap($startCx, $startCz);
		if($startY > 128){
			return;
		}

		$octaves = PerlinOctaveGenerator::fromRandomAndOctaves($random, 3, 4, 2, 4);
		$noise = $octaves->getFractalBrownianMotion($cx, $cz, 0, 0.5, 0.2);
		$angles = [];
		for($i = 0, $noiseC = count($noise); $i < $noiseC; ++$i){
			$angles[$i] = 360.0 * $noise[$i];
		}
		$sectionCount = count($angles) / 2;
		$nodes = [];
		$startBlockPos = new Vector3($cx + $startCx, $startY, $cz + $startCz);
		$currentNode = $startBlockPos->asVector3();
		$nodes[] = $currentNode->asVector3();
		$length = 5;
		for($i = 0; $i < $sectionCount; ++$i){
			$yaw = $angles[$i + $sectionCount];
			$deltaY = -abs((int) floor($noise[$i] * $length));
			$deltaX = (int) floor((float) $length * MathHelper::getInstance()->cos(deg2rad($yaw)));
			$deltaZ = (int) floor((float) $length * MathHelper::getInstance()->sin(deg2rad($yaw)));
			$currentNode = new Vector3($deltaX, $deltaY, $deltaZ);
			$nodes[] = $currentNode->floor();
		}
		foreach($nodes as $node){
			if($node->y < 4){
				continue;
			}

			$this->caveAroundRay($world, $node, $random);
		}
	}

	private function caveAroundRay(ChunkManager $world, Vector3 $block, Random $random) : void{
		$radius = $random->nextBoundedInt(2) + 2;
		$blockX = $block->x;
		$blockY = $block->y;
		$blockZ = $block->z;
		for($x = $blockX - $radius; $x <= $blockX + $radius; ++$x){
			for($y = $blockY - $radius; $y <= $blockY + $radius; ++$y){
				for($z = $blockZ - $radius; $z <= $blockZ + $radius; ++$z){
					$distanceSquared = ($blockX - $x) * ($blockX - $x) + ($blockY - $y) * ($blockY - $y) + ($blockZ - $z) * ($blockZ - $z);
					if($distanceSquared < $radius * $radius){
						$world->setBlockAt($x, $y, $z, VanillaBlocks::AIR());
					}
				}
			}
		}
	}
}
