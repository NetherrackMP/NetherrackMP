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

use pocketmine\block\utils\SupportType;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityPortalEnterEvent;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\world\World;

class EndPortal extends Transparent
{
	public function getLightLevel(): int
	{
		return 15;
	}

	public function isSolid(): bool
	{
		return false;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes(): array
	{
		return [];
	}

	public function getSupportType(int $facing): SupportType
	{
		return SupportType::NONE();
	}

	public function getDrops(Item $item): array
	{
		return [];
	}

	public function getBreakInfo(): BlockBreakInfo
	{
		return new BlockBreakInfo(-1, BlockToolType::NONE, 0, 3_600_000);
	}

	public function onEntityInside(Entity $entity): bool
	{
		$world = $entity->getWorld();
		$ev = new EntityPortalEnterEvent($entity, $world->getFolderName() . "_end", EntityPortalEnterEvent::TYPE_END);
		if ($ev->isCancelled()) return false;
		$endWorld = $world->getServer()->getWorldManager()->getWorldByName($ev->getTargetWorldName());
		if ($endWorld instanceof World) {
			$entity->teleport($endWorld->getSpawnLocation());
			return true;
		}
		return true;
	}
}
