<?php

declare(strict_types=1);

namespace pocketmine\world\overworld\populator\biome;

use pocketmine\world\object\tree\RedwoodTree;
use pocketmine\world\overworld\biome\BiomeIds;
use pocketmine\world\overworld\decorator\types\TreeDecoration;

class IcePlainsPopulator extends BiomePopulator{

	/** @var TreeDecoration[] */
	protected static array $TREES;

	protected static function initTrees() : void{
		self::$TREES = [
			new TreeDecoration(RedwoodTree::class, 1)
		];
	}

	public function getBiomes() : ?array{
		return [BiomeIds::ICE_PLAINS, BiomeIds::ICE_MOUNTAINS];
	}

	protected function initPopulators() : void{
		$this->treeDecorator->setAmount(1);
		$this->treeDecorator->setTrees(...self::$TREES);
		$this->flowerDecorator->setAmount(0);
	}
}

IcePlainsPopulator::init();
