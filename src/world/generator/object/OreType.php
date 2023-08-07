<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
 */

declare(strict_types=1);

namespace pocketmine\world\generator\object;

use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;

class OreType
{
	public array $replaces;
	public Block $low;

	public function __construct(
		public Block $material,
		Block|array  $replaces,
		public int   $clusterCount,
		public int   $clusterSize,
		public int   $minHeight,
		public int   $maxHeight
	)
	{
		if (!is_array($replaces)) $replaces = [$replaces];
		$this->replaces = $replaces;
		$this->low = $this->material;
		$ind = array_search($this->material->getTypeId(), [
			BlockTypeIds::COAL_ORE, BlockTypeIds::GOLD_ORE, BlockTypeIds::IRON_ORE, BlockTypeIds::DIAMOND_ORE,
			BlockTypeIds::COPPER_ORE, BlockTypeIds::EMERALD_ORE, BlockTypeIds::LAPIS_LAZULI_ORE,
			BlockTypeIds::REDSTONE_ORE
		]);
		if ($ind !== false) {
			$name = [
				"COAL_ORE", "GOLD_ORE", "IRON_ORE", "DIAMOND_ORE", "COPPER_ORE", "EMERALD_ORE", "LAPIS_LAZULI_ORE",
				"REDSTONE_ORE"
			][$ind];
			$o = VanillaBlocks::__callStatic("DEEPSLATE_" . $name, []);
			if ($o instanceof Block) $this->low = $o;
		}
	}
}
