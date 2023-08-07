<?php

declare(strict_types=1);

namespace pocketmine\world\object;

use pocketmine\block\Flowable;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;

abstract class TerrainObject{

	/**
	 * Removes weak blocks like grass, shrub, flower or mushroom directly above the given block, if present.
	 * Does not drop an item.
	 *
	 * @return bool whether a block was removed; false if none was present
	 */
	public static function killWeakBlocksAbove(ChunkManager $world, int $x, int $y, int $z) : bool{
		$changed = false;
		for($i = 1; $i < 4; ++$i) {
			$blockAbove = $world->getBlockAt($x, $y + $i, $z);
			if($blockAbove instanceof Flowable) {
				$world->setBlockAt($x, $y + $i, $z, VanillaBlocks::AIR());
				$changed = true;
			}
		}

		return $changed;
	}

	/**
	 * Generates this feature.
	 *
	 * @param ChunkManager $world the world to generate in
	 * @param Random $random the PRNG that will choose the size and a few details of the shape
	 * @param int $sourceX the base X coordinate
	 * @param int $sourceY the base Y coordinate
	 * @param int $sourceZ the base Z coordinate
	 * @return bool if successfully generated
	 */
	abstract public function generate(ChunkManager $world, Random $random, int $sourceX, int $sourceY, int $sourceZ) : bool;
}
