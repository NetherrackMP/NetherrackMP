<?php

declare(strict_types=1);

namespace pocketmine\world\overworld\populator\biome;

use pocketmine\world\object\tree\AcaciaTree;
use pocketmine\world\object\tree\GenericTree;
use pocketmine\world\overworld\biome\BiomeIds;
use pocketmine\world\overworld\decorator\types\DoublePlantDecoration;
use pocketmine\world\overworld\decorator\types\TreeDecoration;
use pocketmine\block\VanillaBlocks;

class SavannaPopulator extends BiomePopulator{

	/** @var DoublePlantDecoration[] */
	protected static array $DOUBLE_PLANTS;

	/** @var TreeDecoration[] */
	protected static array $TREES;

	public static function init() : void{
		parent::init();
		self::$DOUBLE_PLANTS = [
			new DoublePlantDecoration(VanillaBlocks::DOUBLE_TALLGRASS(), 1)
		];
	}

	protected static function initTrees() : void{
		self::$TREES = [
			new TreeDecoration(AcaciaTree::class, 4),
			new TreeDecoration(GenericTree::class, 4)
		];
	}

	protected function initPopulators() : void{
		$this->doublePlantDecorator->setAmount(7);
		$this->doublePlantDecorator->setDoublePlants(...self::$DOUBLE_PLANTS);
		$this->treeDecorator->setAmount(1);
		$this->treeDecorator->setTrees(...self::$TREES);
		$this->flowerDecorator->setAmount(4);
		$this->tallGrassDecorator->setAmount(20);
	}

	public function getBiomes() : ?array{
		return [BiomeIds::SAVANNA, BiomeIds::SAVANNA_PLATEAU];
	}
}
SavannaPopulator::init();
