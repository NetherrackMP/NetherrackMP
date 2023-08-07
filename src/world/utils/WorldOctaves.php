<?php

declare(strict_types=1);

namespace pocketmine\world\utils;

use pocketmine\world\noise\bukkit\OctaveGenerator;

/**
 * @phpstan-template T of OctaveGenerator
 * @phpstan-template U of OctaveGenerator
 * @phpstan-template V of OctaveGenerator
 * @phpstan-template W of OctaveGenerator
 */
class WorldOctaves{

	/** @phpstan-var T */
	public OctaveGenerator $height;

	/** @phpstan-var U */
	public OctaveGenerator $roughness;

	/** @phpstan-var U */
	public OctaveGenerator $roughness2;

	/** @phpstan-var V */
	public OctaveGenerator $detail;

	/** @phpstan-var W */
	public OctaveGenerator $surface;

	/**
	 * @phpstan-param T $height
	 * @phpstan-param U $roughness
	 * @phpstan-param U $roughness2
	 * @phpstan-param V $detail
	 * @phpstan-param W $surface
	 */
	public function __construct(
		OctaveGenerator $height,
		OctaveGenerator $roughness,
		OctaveGenerator $roughness2,
		OctaveGenerator $detail,
		OctaveGenerator $surface
	){
		$this->height = $height;
		$this->roughness = $roughness;
		$this->roughness2 = $roughness2;
		$this->detail = $detail;
		$this->surface = $surface;
	}
}
