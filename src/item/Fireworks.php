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
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\entity\object\FireworkRocket;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\player\Player;

class Fireworks extends Item
{
	public const TYPE_SMALL_SPHERE = 0;
	public const TYPE_HUGE_SPHERE = 1;
	public const TYPE_STAR = 2;
	public const TYPE_CREEPER_HEAD = 3;
	public const TYPE_BURST = 4;

	//color = chr(dye metadata)
	public const COLOR_BLACK = "\x00";
	public const COLOR_RED = "\x01";
	public const COLOR_DARK_GREEN = "\x02";
	public const COLOR_BROWN = "\x03";
	public const COLOR_BLUE = "\x04";
	public const COLOR_DARK_PURPLE = "\x05";
	public const COLOR_DARK_AQUA = "\x06";
	public const COLOR_GRAY = "\x07";
	public const COLOR_DARK_GRAY = "\x08";
	public const COLOR_PINK = "\x09";
	public const COLOR_GREEN = "\x0a";
	public const COLOR_YELLOW = "\x0b";
	public const COLOR_LIGHT_AQUA = "\x0c";
	public const COLOR_DARK_PINK = "\x0d";
	public const COLOR_GOLD = "\x0e";
	public const COLOR_WHITE = "\x0f";

	public function getFlightDuration(): int
	{
		return $this->getExplosionsTag()->getByte("Flight", 1);
	}

	public function getRandomizedFlightDuration(): int
	{
		return ($this->getFlightDuration() + 1) * 10 + mt_rand(0, 5) + mt_rand(0, 6);
	}

	public function setFlightDuration(int $duration): void
	{
		$this->getExplosionsTag()->setByte("Flight", $duration);
	}

	public function addExplosion(int $type, string $color, string $fade = "", bool $flicker = false, bool $trail = false): void
	{
		$tag = $this->getExplosionsTag();
		$explosions = $tag->getListTag("Explosions");
		if ($explosions === null) {
			$tag->setTag("Explosions", $explosions = new ListTag());
		}

		$explosions->push(CompoundTag::create()
			->setByte("FireworkType", $type)
			->setByteArray("FireworkColor", $color)
			->setByteArray("FireworkFade", $fade)
			->setByte("FireworkFlicker", $flicker ? 1 : 0)
			->setByte("FireworkTrail", $trail ? 1 : 0)
		);
	}

	protected function getExplosionsTag(): CompoundTag
	{
		$tag = $this->getNamedTag()->getCompoundTag("Fireworks");
		if ($tag === null) {
			$this->getNamedTag()->setTag("Fireworks", $tag = CompoundTag::create());
		}
		return $tag;
	}

	public function onClickAir(Player $player, Vector3 $directionVector, array &$returnedItems): ItemUseResult
	{
		if (!$player->isGliding()) return ItemUseResult::FAIL();
		$this->pop();
		$location = $player->getLocation();
		$entity = new FireworkRocket($location, $this);
		$entity->getNetworkProperties()->setLong(EntityMetadataProperties::MINECART_HAS_DISPLAY, $player->getId());
		$entity->setOwningEntity($player);
		$entity->spawnToAll();
		return ItemUseResult::SUCCESS();
	}

	public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, array &$returnedItems): ItemUseResult
	{
		$entity = new FireworkRocket(Location::fromObject($blockReplace->getPosition()->add(0.5, 0, 0.5), $player->getWorld(), lcg_value() * 360, 90), $this);
		$this->pop();
		$entity->spawnToAll();
		return ItemUseResult::SUCCESS();
	}
}
