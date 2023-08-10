<?php

declare(strict_types=1);

namespace pocketmine\event\entity;

use pocketmine\entity\Entity;
use pocketmine\math\Vector3;

/**
 * Called when an entity takes damage from a lightning bolt.
 */
class EntityDamageByLightningEvent extends EntityDamageEvent
{
	public function __construct(
		private readonly Vector3 $lightning,
		Entity                    $entity,
		float                     $damage = 5
	)
	{
		parent::__construct($entity, self::CAUSE_LIGHTNING_BOLT, $damage);
	}

	/*** @return Vector3 */
	public function getLightningPosition(): Vector3
	{
		return $this->lightning;
	}
}
