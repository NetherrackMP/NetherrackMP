<?php

declare(strict_types=1);

namespace pocketmine\world\ground;

use pocketmine\block\utils\DirtType;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;

class DirtAndStonePatchGroundGenerator extends GroundGenerator{

	public function generateTerrainColumn(ChunkManager $world, Random $random, int $x, int $z, int $biome, float $surfaceNoise) : void{
		if($surfaceNoise > 1.75){
			$this->setTopMaterial(VanillaBlocks::STONE());
			$this->setGroundMaterial(VanillaBlocks::STONE());
		}elseif($surfaceNoise > -0.5){
			$this->setTopMaterial(VanillaBlocks::DIRT()->setDirtType(DirtType::COARSE()));
			$this->setGroundMaterial(VanillaBlocks::DIRT());
		}else{
			$this->setTopMaterial(VanillaBlocks::GRASS());
			$this->setGroundMaterial(VanillaBlocks::DIRT());
		}

		parent::generateTerrainColumn($world, $random, $x, $z, $biome, $surfaceNoise);
	}
}
