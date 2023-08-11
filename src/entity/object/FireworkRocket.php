<?php

declare(strict_types=1);

namespace pocketmine\entity\object;

use pocketmine\entity\animation\FireworkParticleAnimation;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Living;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Fireworks;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\utils\AssumptionFailedError;

class FireworkRocket extends Entity
{
	public const DATA_FIREWORK_ITEM = 16; //firework item

	public static function getNetworkTypeId(): string
	{
		return EntityIds::FIREWORKS_ROCKET;
	}

	protected int $lifeTime = 0;
	protected Fireworks $fireworks;

	public function __construct(Location $location, Item $fireworks, ?int $lifeTime = null)
	{
		if (!$fireworks instanceof Fireworks) throw new AssumptionFailedError("Expected a Fireworks item instance.");
		$this->fireworks = $fireworks;
		parent::__construct($location, $fireworks->getNamedTag());
		$this->setMotion(new Vector3(0.001, 0.05, 0.001));
		if ($fireworks->getNamedTag()->getCompoundTag("Fireworks") !== null)
			$this->lifeTime = $lifeTime ?? $fireworks->getRandomizedFlightDuration();
		$location->getWorld()->broadcastPacketToViewers($location, LevelSoundEventPacket::nonActorSound(LevelSoundEvent::LAUNCH, $location, false));
	}

	protected function tryChangeMovement(): void
	{
		$this->motion->x *= 1.15;
		$this->motion->y += 0.04;
		$this->motion->z *= 1.15;
	}

	public function entityBaseTick(int $tickDiff = 1): bool
	{
		if ($this->closed) return false;
		$hasUpdate = parent::entityBaseTick($tickDiff);
		if ($this->doLifeTimeTick()) $hasUpdate = true;
		return $hasUpdate;
	}

	protected function doLifeTimeTick(): bool
	{
		if (--$this->lifeTime < 0 && !$this->isFlaggedForDespawn()) {
			$this->doExplosionAnimation();
			$this->playSounds();
			$this->flagForDespawn();
			foreach ($this->getViewers() as $player) {
				$dist = $this->location->distance($player->getEyePos());
				if ($dist <= 5) {
					$damage = 7 - $dist / 5 * 7;
					$player->attack(new EntityDamageByEntityEvent(
						$this,
						$player,
						EntityDamageEvent::CAUSE_ENTITY_EXPLOSION,
						$damage,
						[],
						Living::DEFAULT_KNOCKBACK_FORCE,
						0
					));
				}
			}
			return true;
		}

		return false;
	}

	protected function doExplosionAnimation(): void
	{
		$this->broadcastAnimation(new FireworkParticleAnimation($this), $this->getViewers());
	}

	public function playSounds(): void
	{
		$fireworksTag = $this->fireworks->getNamedTag()->getCompoundTag("Fireworks");
		if ($fireworksTag === null) return;
		$explosionsTag = $fireworksTag->getListTag("Explosions");
		if ($explosionsTag === null) return;
		foreach ($explosionsTag->getValue() as $info) {
			if ($info instanceof CompoundTag) {
				$this->getWorld()->broadcastPacketToViewers($this->location, LevelSoundEventPacket::nonActorSound(
					$info->getByte("FireworkType", 0) === Fireworks::TYPE_HUGE_SPHERE
						? LevelSoundEvent::LARGE_BLAST : LevelSoundEvent::BLAST,

					$this->location->asVector3(),
					false
				));
				if ($info->getByte("FireworkFlicker", 0) === 1) {
					$this->getWorld()->broadcastPacketToViewers($this->location, LevelSoundEventPacket::nonActorSound(LevelSoundEvent::TWINKLE, $this->location->asVector3(), false));
				}
			}
		}
	}

	public function syncNetworkData(EntityMetadataCollection $properties): void
	{
		parent::syncNetworkData($properties);
		$properties->setCompoundTag(self::DATA_FIREWORK_ITEM, new CacheableNbt($this->fireworks->getNamedTag()));
	}

	protected function getInitialSizeInfo(): EntitySizeInfo
	{
		return new EntitySizeInfo(0.25, 0.25);
	}

	public function saveNBT(): CompoundTag
	{
		$nbt = parent::saveNBT();
		$nbt->setTag("Item", $this->fireworks->nbtSerialize());
		return $nbt;
	}

	protected function getInitialDragMultiplier(): float
	{
		return 0;
	}

	protected function getInitialGravity(): float
	{
		return 0;
	}
}
