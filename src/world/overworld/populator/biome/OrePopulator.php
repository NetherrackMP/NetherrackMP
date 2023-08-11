<?php

declare(strict_types=1);

namespace pocketmine\world\overworld\populator\biome;

use pocketmine\world\object\OreType;
use pocketmine\world\object\OreVein;
use pocketmine\world\overworld\populator\biome\utils\OreTypeHolder;
use pocketmine\world\Populator;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\World;

class OrePopulator implements Populator{

	/** @var OreTypeHolder[] */
	private array $ores = [];

	/**
	 * Creates a populator for dirt, gravel, andesite, diorite, granite; and coal, iron, gold,
	 * redstone, diamond and lapis lazuli ores.
	 */
	public function __construct(){
		$this->addOre(new OreType(VanillaBlocks::DIRT(), 0, 256, 32), 10);
		$this->addOre(new OreType(VanillaBlocks::GRAVEL(), World::Y_MIN, 256, 32), 8);
		$this->addOre(new OreType(VanillaBlocks::GRANITE(), 0, 80, 32), 10);
		$this->addOre(new OreType(VanillaBlocks::TUFF(), World::Y_MIN, 0, 32), 16);
		$this->addOre(new OreType(VanillaBlocks::DIORITE(), 0, 80, 32), 10);
		$this->addOre(new OreType(VanillaBlocks::ANDESITE(), 0, 80, 32), 10);
		$this->addOre(new OreType(VanillaBlocks::COAL_ORE(), World::Y_MIN, 128, 16), 20);
		$this->addOre(new OreType(VanillaBlocks::IRON_ORE(), World::Y_MIN, 64, 8), 20);
		$this->addOre(new OreType(VanillaBlocks::GOLD_ORE(), World::Y_MIN, 32, 8), 2);
		$this->addOre(new OreType(VanillaBlocks::REDSTONE_ORE(), World::Y_MIN, 16, 7), 8);
		$this->addOre(new OreType(VanillaBlocks::DIAMOND_ORE(), World::Y_MIN, 16, 7), 1);
		$this->addOre(new OreType(VanillaBlocks::LAPIS_LAZULI_ORE(), 16, 16, 6), 1);
	}

	protected function addOre(OreType $type, int $value) : void{
		$this->ores[] = new OreTypeHolder($type, $value);
	}

	public function populate(ChunkManager $world, Random $random, int $chunkX, int $chunkZ, Chunk $chunk) : void{
		$cx = $chunkX << 4;
		$cz = $chunkZ << 4;

		foreach($this->ores as $oreTypeHolder){
			for($n = 0; $n < $oreTypeHolder->value; ++$n){
				$sourceX = $cx + $random->nextBoundedInt(16);
				$sourceZ = $cz + $random->nextBoundedInt(16);
				$sourceY = $oreTypeHolder->type->getRandomHeight($random);
				(new OreVein($oreTypeHolder->type))->generate($world, $random, $sourceX, $sourceY, $sourceZ);
			}
		}
	}
}
