<?php

declare(strict_types=1);

namespace pocketmine\world\object\tree;

use pocketmine\world\utils\MathHelper;
use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;
use function abs;
use function array_key_exists;
use function hypot;
use function max;
use function min;
use function pow;
use function sqrt;

class BigOakTree extends GenericTree{
	private const LEAF_DENSITY = 1.0;

	private int $maxLeafDistance = 5;
	private int $trunkHeight = 0;

	public function __construct(Random $random, BlockTransaction $transaction){
		parent::__construct($random, $transaction);
		$this->setHeight($random->nextBoundedInt(12) + 5);
	}

	final public function setMaxLeafDistance(int $distance) : void{
		$this->maxLeafDistance = $distance;
	}

	public function canPlace(int $baseX, int $baseY, int $baseZ, ChunkManager $world) : bool{
		$from = new Vector3($baseX, $baseY, $baseZ);
		$to = new Vector3($baseX, $baseY + $this->height - 1, $baseZ);
		$blocks = $this->countAvailableBlocks($from, $to, $world);
		if($blocks === -1){
			return true;
		}
		if($blocks > 5){
			$this->height = $blocks;
			return true;
		}
		return false;
	}

	public function generate(ChunkManager $world, Random $random, int $sourceX, int $sourceY, int $sourceZ) : bool{
		if(!$this->canPlaceOn($world->getBlockAt($sourceX, $sourceY - 1, $sourceZ)) || !$this->canPlace($sourceX, $sourceY, $sourceZ, $world)){
			return false;
		}

		$this->trunkHeight = (int) ($this->height * 0.618);
		if($this->trunkHeight >= $this->height){
			$this->trunkHeight = $this->height - 1;
		}

		$leafNodes = $this->generateLeafNodes($sourceX, $sourceY, $sourceZ, $world, $random);

		// generate the leaves
		foreach($leafNodes as $node){
			for($y = 0; $y < $this->maxLeafDistance; ++$y){
				$size = $y > 0 && $y < $this->maxLeafDistance - 1.0 ? 3.0 : 2.0;
				$sizeSquared = pow($size, 2);
				$nodeDistance = (int) (0.618 + $size);
				for($x = -$nodeDistance; $x <= $nodeDistance; ++$x){
					$xSquared = pow(abs($x) + 0.5, 2);
					for($z = -$nodeDistance; $z <= $nodeDistance; ++$z){
						$zSquared = pow(abs($z) + 0.5, 2);
						if(
							($xSquared + $zSquared <= $sizeSquared) &&
							$this->canBeOverridden($world->getBlockAt($node->x + $x, $node->y + $y, $node->z + $z))
						){
							$this->transaction->addBlockAt($node->x + $x, $node->y + $y, $node->z + $z, $this->leavesType);
						}
					}
				}
			}
		}

		// generate the trunk
		for($y = 0; $y < $this->trunkHeight; ++$y){
			$this->transaction->addBlockAt($sourceX, $sourceY + $y, $sourceZ, $this->logType);
		}

		$registry = RuntimeBlockStateRegistry::getInstance();

		// generate the branches
		foreach($leafNodes as $node){
			if((float) ($node->branchY - $sourceY) >= $this->height * 0.2){
				$base = new Vector3($sourceX, $node->branchY, $sourceZ);
				$leafNode = new Vector3($node->x, $node->y, $node->z);
				$branch = $leafNode->subtractVector($base);
				$maxDistance = max(abs($branch->getFloorY()), abs($branch->getFloorX()), abs($branch->getFloorZ()));
				if($maxDistance > 0){
					$dx = (float) $branch->x / $maxDistance;
					$dy = (float) $branch->y / $maxDistance;
					$dz = (float) $branch->z / $maxDistance;
					for($i = 0; $i <= $maxDistance; ++$i){
						$branch = $base->add(0.5 + $i * $dx, 0.5 + $i * $dy, 0.5 + $i * $dz);
						$x = abs($branch->getFloorX() - $base->getFloorX());
						$z = abs($branch->getFloorZ() - $base->getFloorZ());
						$max = max($x, $z);
						$direction = $max > 0 ? ($max === $x ? 4 : 8) : 0; // EAST / SOUTH
						$this->transaction->addBlockAt($branch->getFloorX(), $branch->getFloorY(), $branch->getFloorZ(), $this->logType);
					}
				}
			}
		}

		return true;
	}

	private function countAvailableBlocks(Vector3 $from, Vector3 $to, ChunkManager $world) : int{
		$n = 0;
		$target = $to->subtractVector($from);
		$maxDistance = max(abs($target->getFloorY()), abs($target->getFloorX()), abs($target->getFloorZ()));
		if($maxDistance > 0){
			$dx = (float) $target->x / $maxDistance;
			$dy = (float) $target->y / $maxDistance;
			$dz = (float) $target->z / $maxDistance;
			$maxY = $world->getMaxY();
			$minY = $world->getMinY();
			for($i = 0; $i <= $maxDistance; ++$i, ++$n){
				$target = $from->add(0.5 + $i * $dx, 0.5 + $i * $dy, 0.5 + $i * $dz);
				$targetFloorY = $target->getFloorY();
				if(
					$targetFloorY < $minY ||
					$targetFloorY > $maxY ||
					!$this->canBeOverridden($world->getBlockAt($target->getFloorX(), $target->getFloorY(), $target->getFloorZ()))
				){
					return $n;
				}
			}
		}
		return -1;
	}

	/**
	 * @return BigOakTreeLeafNode[]
	 */
	private function generateLeafNodes(int $blockX, int $blockY, int $blockZ, ChunkManager $world, Random $random) : array{
		$leafNodes = [];
		$y = $blockY + $this->height - $this->maxLeafDistance;
		$trunkTopY = $blockY + $this->trunkHeight;
		$leafNodes[] = new BigOakTreeLeafNode($blockX, $y, $blockZ, $trunkTopY);

		$nodeCount = (int) (1.382 + pow((static::LEAF_DENSITY * ($this->height / 13.0)), 2.0));
		$nodeCount = max($nodeCount, 1);

		for($l = --$y - $blockY; $l >= 0; --$l, --$y) {
			$h = $this->height / 2.0;
			$v = $h - $l;
			$f = (($l < ($this->height * 0.3)) ? -1.0 : (
				$v === $h ? $h * 0.5 : (
					$h <= abs($v) ? 0.0 : (sqrt($h * $h - $v * $v) * 0.5)
				)
			));
			if($f >= 0.0) {
				for($i = 0; $i < $nodeCount; ++$i) {
					$d1 = $f * ($random->nextFloat() + 0.328);
					$d2 = $random->nextFloat() * M_PI * 2.0;
					$x = (int)($d1 * MathHelper::getInstance()->sin($d2) + $blockX + 0.5);
					$z = (int)($d1 * MathHelper::getInstance()->cos($d2) + $blockZ + 0.5);
					if($this->countAvailableBlocks(new Vector3($x, $y, $z), new Vector3($x, $y + $this->maxLeafDistance, $z), $world) === -1) {
						$offX = $blockX - $x;
						$offZ = $blockZ - $z;
						$distance = 0.381 * hypot($offX, $offZ);
						$branchBaseY = min($trunkTopY, (int)($y - $distance));
						if($this->countAvailableBlocks(new Vector3($x, $branchBaseY, $z), new Vector3($x, $y, $z), $world) === -1) {
							$leafNodes[] = new BigOakTreeLeafNode($x, $y, $z, $branchBaseY);
						}
					}
				}
			}
		}
		return $leafNodes;
	}
}
