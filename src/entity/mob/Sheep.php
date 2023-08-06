<?php

namespace pocketmine\entity\mob;

use pocketmine\block\VanillaBlocks;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Living;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
class Sheep extends Living
{

	protected function getInitialSizeInfo(): EntitySizeInfo
	{
		return new EntitySizeInfo(1.25, 0.625);
	}

	public static function getNetworkTypeId(): string
	{
		return EntityIds::SHEEP;
	}

	public function getName(): string
	{
		return 'Sheep';
	}

	public function getDrops(): array
	{
		$this->
		$items = [
			VanillaBlocks::WOOL()->asItem()->setCount(mt_rand(1, 2)),
			VanillaItems::RAW_MUTTON()->setCount(mt_rand(1, 3))
		];
		return $items;
	}

	public function getXpDropAmount(): int
	{
		return mt_rand(1, 4);
	}
}