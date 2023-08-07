<?php

declare(strict_types=1);

namespace pocketmine\world\object;

use pocketmine\world\utils\MathHelper;
use pocketmine\block\Block;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;

class OreVein extends TerrainObject{

	/**
	 * The square of the percentage of the radius that is the distance between the given block's
	 * center and the center of an orthogonal ellipsoid. A block's center is inside the ellipsoid
	 * if and only if its normalizedSquaredCoordinate values add up to less than 1.
	 *
	 * @param float $origin the center of the spheroid
	 * @param float $radius the spheroid's radius on this axis
	 * @param int $x the raw coordinate
	 * @return float the square of the normalized coordinate
	 */
	protected static function normalizedSquaredCoordinate(float $origin, float $radius, int $x) : float{
		$squaredNormalizedX = ($x + 0.5 - $origin) / $radius;
		$squaredNormalizedX *= $squaredNormalizedX;
		return $squaredNormalizedX;
	}

	private Block $type;
	private int $amount;
	private int $targetType;

	/**
	 * Creates the instance for a given ore type.
	 *
	 * @param OreType $oreType the ore type
	 */
	public function __construct(OreType $oreType){
		$this->type = $oreType->getType();
		$this->amount = $oreType->getAmount();
		$this->targetType = $oreType->getTargetType();
	}

	public function generate(ChunkManager $world, Random $random, int $sourceX, int $sourceY, int $sourceZ) : bool{
		$angle = $random->nextFloat() * M_PI;
		$dx1 = $sourceX + MathHelper::getInstance()->sin($angle) * $this->amount / 8.0;
		$dx2 = $sourceX - MathHelper::getInstance()->sin($angle) * $this->amount / 8.0;
		$dz1 = $sourceZ + MathHelper::getInstance()->cos($angle) * $this->amount / 8.0;
		$dz2 = $sourceZ - MathHelper::getInstance()->cos($angle) * $this->amount / 8.0;
		$dy1 = $sourceY + $random->nextBoundedInt(3) - 2;
		$dy2 = $sourceY + $random->nextBoundedInt(3) - 2;
		$succeeded = false;
		for($i = 0; $i < $this->amount; ++$i){
			$originX = $dx1 + ($dx2 - $dx1) * $i / $this->amount;
			$originY = $dy1 + ($dy2 - $dy1) * $i / $this->amount;
			$originZ = $dz1 + ($dz2 - $dz1) * $i / $this->amount;
			$q = $random->nextFloat() * $this->amount / 16.0;
			$radiusH = (MathHelper::getInstance()->sin($i * M_PI / $this->amount) + $q + 1) / 2.0;
			$radiusV = (MathHelper::getInstance()->sin($i * M_PI / $this->amount) + $q + 1) / 2.0;

			$minX = (int) ($originX - $radiusH);
			$maxX = (int) ($originX + $radiusH);

			$minY = (int) ($originY - $radiusV);
			$maxY = (int) ($originY + $radiusV);

			$minZ = (int) ($originZ - $radiusH);
			$maxZ = (int) ($originZ + $radiusH);

			for($x = $minX; $x <= $maxX; ++$x){
				// scale the center of x to the range [-1, 1] within the circle
				$squaredNormalizedX = self::normalizedSquaredCoordinate($originX, $radiusH, $x);
				if($squaredNormalizedX >= 1){
					continue;
				}
				for($y = $minY; $y <= $maxY; ++$y){
					$squaredNormalizedY = self::normalizedSquaredCoordinate($originY, $radiusV, $y);
					if($squaredNormalizedX + $squaredNormalizedY >= 1){
						continue;
					}
					for($z = $minZ; $z <= $maxZ; ++$z){
						$squaredNormalizedZ = self::normalizedSquaredCoordinate($originZ, $radiusH, $z);
						if($squaredNormalizedX + $squaredNormalizedY + $squaredNormalizedZ < 1 && $world->getBlockAt($x, $y, $z)->getTypeId() === $this->targetType){
							$world->setBlockAt($x, $y, $z, $this->type);
							$succeeded = true;
						}
					}
				}
			}
		}

		return $succeeded;
	}
}
