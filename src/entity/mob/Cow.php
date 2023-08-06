<?php

namespace pocketmine\entity\mob;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Living;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;

class Cow extends Living
{

	protected function getInitialSizeInfo(): EntitySizeInfo
	{
		return new EntitySizeInfo(1.4, 0.9);
	}

	public static function getNetworkTypeId(): string
	{
		return EntityIds::COW;
	}

	public function getName(): string
	{
		return 'Cow';
	}

	public function getDrops(): array
	{
		$items = [
			VanillaItems::RAW_BEEF()->setCount(mt_rand(1, 3)),
			VanillaItems::LEATHER()->setCount(mt_rand(1, 3))
		];

		return $items;
	}

	public function getXpDropAmount(): int
	{
		return mt_rand(1, 4);
	}
}