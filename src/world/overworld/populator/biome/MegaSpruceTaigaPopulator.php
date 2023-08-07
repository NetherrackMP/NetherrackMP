<?php

declare(strict_types=1);

namespace pocketmine\world\overworld\populator\biome;

use pocketmine\world\object\tree\MegaSpruceTree;
use pocketmine\world\object\tree\RedwoodTree;
use pocketmine\world\object\tree\TallRedwoodTree;
use pocketmine\world\overworld\biome\BiomeIds;
use pocketmine\world\overworld\decorator\types\TreeDecoration;

class MegaSpruceTaigaPopulator extends MegaTaigaPopulator{

	/** @var TreeDecoration[] */
	protected static array $TREES;

	protected static function initTrees() : void{
		self::$TREES = [
			new TreeDecoration(RedwoodTree::class, 44),
			new TreeDecoration(TallRedwoodTree::class, 22),
			new TreeDecoration(MegaSpruceTree::class, 33)
		];
	}

	public function getBiomes() : ?array{
		return [BiomeIds::REDWOOD_TAIGA_MUTATED, BiomeIds::REDWOOD_TAIGA_HILLS_MUTATED];
	}

	protected function initPopulators() : void{
		$this->treeDecorator->setTrees(...self::$TREES);
	}
}

MegaSpruceTaigaPopulator::init();
