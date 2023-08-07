<?php

declare(strict_types=1);

namespace pocketmine\world\object\tree;

use pocketmine\block\BlockTypeIds;
use pocketmine\block\RedMushroomBlock;
use pocketmine\block\VanillaBlocks;

class RedMushroomTree extends BrownMushroomTree{

	protected function getType(): RedMushroomBlock {
		return VanillaBlocks::RED_MUSHROOM_BLOCK();
	}
}
