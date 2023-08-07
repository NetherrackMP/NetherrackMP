<?php

declare(strict_types=1);

namespace pocketmine\world\overworld\populator\biome\utils;

use pocketmine\world\object\OreType;

final class OreTypeHolder{

	public OreType $type;

	public int $value;

	public function __construct(OreType $type, int $value){
		$this->type = $type;
		$this->value = $value;
	}
}
