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

namespace pocketmine\event\entity;

use pocketmine\entity\Entity;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\utils\Utils;
use pocketmine\world\Position;

/**
 * @phpstan-extends EntityEvent<Entity>
 */
class EntityPortalEnterEvent extends EntityEvent implements Cancellable{
	use CancellableTrait;
	public const TYPE_NETHER = 0;
	public const TYPE_END = 1;

	public function __construct(
		Entity $entity,
		private string $targetWorldName,
		private readonly int $type
	){
		$this->entity = $entity;
	}

	public function getTargetWorldName(): string
	{
		return $this->targetWorldName;
	}

	public function setTargetWorldName(string $targetWorldName): void
	{
		$this->targetWorldName = $targetWorldName;
	}

	public function getType(): int
	{
		return $this->type;
	}
}
