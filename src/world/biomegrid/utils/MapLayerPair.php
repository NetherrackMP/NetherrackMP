<?php

declare(strict_types=1);

namespace pocketmine\world\biomegrid\utils;

use pocketmine\world\biomegrid\MapLayer;

final class MapLayerPair{

	public MapLayer $highResolution;
	public ?MapLayer $lowResolution;

	public function __construct(MapLayer $highResolution, ?MapLayer $lowResolution){
		$this->highResolution = $highResolution;
		$this->lowResolution = $lowResolution;
	}
}
