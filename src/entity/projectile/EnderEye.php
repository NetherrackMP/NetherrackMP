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

namespace pocketmine\entity\projectile;

use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class EnderEye extends Entity
{
	private int $stopTimer = 0;

	public static function getNetworkTypeId(): string
	{
		return EntityIds::EYE_OF_ENDER_SIGNAL;
	}

	protected function entityBaseTick(int $tickDiff = 1): bool
	{
		if (!$this->closed && !$this->isFlaggedForDespawn()) {
			$this->motion->x *= 0.9;
			$this->motion->y *= 0.9;
			$this->motion->z *= 0.9;
			if ($this->motion->length() < 0.01 && ++$this->stopTimer > 20) {
				if (mt_rand(1, 100) < 80)
					$this->getLocation()->getWorld()->dropItem($this->getLocation(), VanillaItems::ENDER_EYE());
				$this->flagForDespawn();
			}
		}
		return parent::entityBaseTick($tickDiff);
	}

	protected function getInitialSizeInfo(): EntitySizeInfo
	{
		return new EntitySizeInfo(0.25, 0.25);
	}

	protected function getInitialDragMultiplier(): float
	{
		return 0;
	}

	protected function getInitialGravity(): float
	{
		return 0;
	}
}
