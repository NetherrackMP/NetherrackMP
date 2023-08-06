<?php

namespace pocketmine\entity\mob;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Living;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class Donkey extends Living
{

	protected function getInitialSizeInfo(): EntitySizeInfo
	{
		return new EntitySizeInfo(1.5625, 1.25);
	}

	public static function getNetworkTypeId(): string
	{
		return EntityIds::DONKEY;
	}

	public function getName(): string
	{
		return 'Donkey';
	}
}