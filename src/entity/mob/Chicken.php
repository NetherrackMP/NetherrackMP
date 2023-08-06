<?php

namespace pocketmine\entity\mob;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Living;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class Chicken extends Living
{

	protected function getInitialSizeInfo(): EntitySizeInfo
	{
		return new EntitySizeInfo(0.7, 0.4);
	}

	public static function getNetworkTypeId(): string
	{
		return EntityIds::CHICKEN;
	}

	public function getName(): string
	{
		return 'Chicken';
	}

	public function getDrops(): array
	{
		$items = [
			VanillaItems::RAW_CHICKEN()->setCount(mt_rand(1, 3)),
			VanillaItems::FEATHER()->setCount(mt_rand(0, 2))
		];

		return $items;
	}

	public function getXpDropAmount(): int
	{
		return mt_rand(1, 3);
	}
}