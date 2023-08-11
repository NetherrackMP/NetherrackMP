<?php

namespace pocketmine\item;

use pocketmine\entity\Living;

class Elytra extends Armor
{
	private int $damageTicks = 0;

	protected function onBroken(): void
	{
	}

	public function onTickWorn(Living $entity): bool
	{
		if (++$this->damageTicks >= 20) {
			$this->applyDamage(1);
			return true;
		}
		return false;
	}
}
