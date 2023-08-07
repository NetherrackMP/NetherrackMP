<?php

declare(strict_types=1);

namespace pocketmine\world\overworld\populator\biome;

use pocketmine\world\overworld\biome\BiomeIds;

class DesertMountainsPopulator extends DesertPopulator{

	protected function initPopulators() : void{
		$this->waterLakeDecorator->setAmount(1);
	}

	public function getBiomes() : ?array{
		return [BiomeIds::DESERT_MUTATED];
	}
}
