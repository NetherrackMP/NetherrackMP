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

namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\entity\Location;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\entity\object\Boat as BoatEntity;

class Boat extends Item
{
    private BoatType $boatType;

    public function __construct(ItemIdentifier $identifier, string $name, BoatType $boatType)
    {
        parent::__construct($identifier, $name);
        $this->boatType = $boatType;
    }

    public function getType(): BoatType
    {
        return $this->boatType;
    }

    public function setBoatType(BoatType $boatType): self
    {
        $this->boatType = $boatType;
        return $this;
    }

    public function getFuelTime(): int
    {
        return 1200;
    }

    public function getMaxStackSize(): int
    {
        return 1;
    }

    public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, array &$returnedItems): ItemUseResult
    {
        $this->pop();
        $entity = new BoatEntity(Location::fromObject($blockClicked->getSide($face)->getPosition()->add(0.5, 0.5, 0.5), $player->getWorld(), $player->getLocation()->yaw + 90), CompoundTag::create()
            ->setString(BoatEntity::TAG_WOOD_TYPE, $this->boatType->name())
        );
        $entity->spawnToAll();
        return ItemUseResult::SUCCESS();
    }
}
