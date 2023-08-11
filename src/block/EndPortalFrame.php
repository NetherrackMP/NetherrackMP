<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
 */

declare(strict_types=1);

namespace pocketmine\block;

use pocketmine\block\utils\FacesOppositePlacingPlayerTrait;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\player\Player;
use pocketmine\world\Position;

class EndPortalFrame extends Opaque
{
	use FacesOppositePlacingPlayerTrait;

	protected bool $eye = false;

	private const SIDES = [Facing::NORTH, Facing::EAST, Facing::SOUTH, Facing::WEST];

	public static function isCompletedPortal(Position $pos): bool
	{
		for ($i = 0; $i < 4; ++$i) {
			for ($j = -1; $j <= 1; ++$j) {
				$block = $pos->getSide(self::SIDES[$i], 2)->getSide(self::SIDES[($i + 1) % 4], $j);
				if (!$block instanceof EndPortalFrame || !$block->hasEye()) {
					return false;
				}
			}
		}

		return true;
	}

	public static function tryCreatingPortal(Position $pos): void
	{
		for ($i = 0; $i < 4; ++$i) {
			for ($j = -1; $j <= 1; ++$j) {
				$center = $pos->getSide(self::SIDES[$i], 2)->getSide(self::SIDES[($i + 1) % 4], $j);
				if (self::isCompletedPortal($center)) {
					self::createPortal($center);
				}
			}
		}
	}

	public static function createPortal(Position $pos): void
	{
		$world = $pos->getWorld();
		for ($i = -1; $i <= 1; ++$i) {
			for ($j = -1; $j <= 1; ++$j) {
				$world->setBlockAt($pos->x + $i, $pos->y, $pos->z + $j, VanillaBlocks::END_PORTAL(), false);
			}
		}
	}

	public static function tryDestroyingPortal(Position $pos): void
	{
		for ($i = 0; $i < 4; ++$i) {
			for ($j = -1; $j <= 1; ++$j) {
				$center = $pos->getSide(self::SIDES[$i], 2)->getSide(self::SIDES[($i + 1) % 4], $j);
				if (!self::isCompletedPortal($center)) {
					self::destroyPortal($center);
				}
			}
		}
	}

	public static function destroyPortal(Position $pos): void
	{
		$world = $pos->getWorld();
		$type_id = VanillaBlocks::END_PORTAL()->getTypeId();
		for ($i = -1; $i <= 1; ++$i) {
			for ($j = -1; $j <= 1; ++$j) {
				if ($world->getBlockAt($pos->x + $i, $pos->y, $pos->z + $j)->getTypeId() === $type_id) {
					$world->setBlockAt($pos->x + $i, $pos->y, $pos->z + $j, VanillaBlocks::AIR(), false);
				}
			}
		}
	}

	protected function describeBlockOnlyState(RuntimeDataDescriber $w): void
	{
		$w->horizontalFacing($this->facing);
		$w->bool($this->eye);
	}

	public function hasEye(): bool
	{
		return $this->eye;
	}

	/** @return $this */
	public function setEye(bool $eye): self
	{
		$this->eye = $eye;
		return $this;
	}

	public function getLightLevel(): int
	{
		return 1;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes(): array
	{
		return [AxisAlignedBB::one()->trim(Facing::UP, 3 / 16)];
	}

	public function onBreak(Item $item, ?Player $player = null, array &$returnedItems = []): bool
	{
		$res = parent::onBreak($item, $player, $returnedItems);
		self::tryDestroyingPortal($this->position);
		return $res;
	}
}
