<?php

declare(strict_types=1);

namespace pocketmine\world\utils;

use pocketmine\world\noise\bukkit\OctaveGenerator;

/**
 * @phpstan-template T of OctaveGenerator
 * @phpstan-template U of OctaveGenerator
 * @phpstan-template V of OctaveGenerator
 * @phpstan-template W of OctaveGenerator
 * @phpstan-template X of OctaveGenerator
 * @phpstan-template Y of OctaveGenerator
 *
 * @phpstan-extends WorldOctaves<T, U, V, W>
 */
class NetherWorldOctaves extends WorldOctaves{

	/** @phpstan-var X */
	public OctaveGenerator $soulSand;

	/** @phpstan-var Y */
	public OctaveGenerator $gravel;

	/**
	 * @phpstan-param T $height
	 * @phpstan-param U $roughness
	 * @phpstan-param U $roughness2
	 * @phpstan-param V $detail
	 * @phpstan-param W $surface
	 * @phpstan-param X $soulSand
	 * @phpstan-param Y $gravel
	 */
	public function __construct(
		OctaveGenerator $height,
		OctaveGenerator $roughness,
		OctaveGenerator $roughness2,
		OctaveGenerator $detail,
		OctaveGenerator $surface,
		OctaveGenerator $soulSand,
		OctaveGenerator $gravel
	){
		parent::__construct($height, $roughness, $roughness2, $detail, $surface);
		$this->soulSand = $soulSand;
		$this->gravel = $gravel;
	}
}
