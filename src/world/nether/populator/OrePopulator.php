<?php

declare(strict_types=1);

namespace pocketmine\world\nether\populator;

use pocketmine\world\object\OreType;
use pocketmine\world\overworld\populator\biome\OrePopulator as OverworldOrePopulator;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\world\World;

class OrePopulator extends OverworldOrePopulator{

	/**
	 * @noinspection MagicMethodsValidityInspection
	 * @noinspection PhpMissingParentConstructorInspection
	 */
	public function __construct(int $worldHeight = World::Y_MAX){
		$this->addOre(new OreType(VanillaBlocks::NETHER_QUARTZ_ORE(), 10, $worldHeight - (10 * ($worldHeight >> 7)), 13, BlockTypeIds::NETHERRACK), 16);
		$this->addOre(new OreType(VanillaBlocks::MAGMA(), 26, 32 + (5 * ($worldHeight >> 7)), 32, BlockTypeIds::NETHERRACK), 16);
	}
}
