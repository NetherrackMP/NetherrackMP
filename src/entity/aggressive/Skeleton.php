<?php

namespace pocketmine\entity\aggressive;

use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Living;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\VanillaItems;

class Skeleton extends Living
{
	public static function getNetworkTypeId(): string
	{
		return EntityIds::SKELETON;
	}

	protected function getInitialSizeInfo(): EntitySizeInfo
	{
		return new EntitySizeInfo(1.99, 0.6, 1.62);
	}

	public function getName(): string
	{
		return 'Skeleton';
	}

	public function getDrops() : array
	{
		$drops = [
			VanillaItems::BONE()->setCount(mt_rand(0, 2)),
			VanillaItems::ARROW()->setCount(mt_rand(1, 2))
		];

		if(mt_rand(0, 199) < 5){
			switch(mt_rand(0, 2)){
				case 0:
					$drops[] = VanillaItems::BOW();
					break;
				case 1:
					$drops[] = VanillaItems::BOW()->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), 2))->setDamage(VanillaItems::BOW()->getMaxDurability() - 3);
					break;
			}
		}
		return $drops;
	}

	public function getXpDropAmount() : int
	{
		return rand(1, 5);
	}
}