<?php

declare(strict_types=1);

namespace pocketmine\world\ground;

use pocketmine\block\VanillaBlocks;

class SandyGroundGenerator extends GroundGenerator{

	public function __construct(){
		parent::__construct(VanillaBlocks::SAND(), VanillaBlocks::SAND());
	}
}
