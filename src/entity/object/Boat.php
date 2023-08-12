<?php

declare(strict_types=1);

namespace pocketmine\entity\object;

use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\item\BoatType;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\types\ActorEvent;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\player\Player;
use pocketmine\world\generator\object\TreeType;

class Boat extends Entity
{
    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(0.455, 1.4);
    }

    public static function getNetworkTypeId(): string
    {
        return EntityIds::BOAT;
    }

    /** @var float */
    public float $gravity = 0.0;

    public const TAG_WOOD_TYPE = "WoodType";

    public const ACTION_ROW_RIGHT = 128;
    public const ACTION_ROW_LEFT = 129;

    private string $woodType;

    protected function initEntity(CompoundTag $nbt): void
    {
        parent::initEntity($nbt);
        $woodType = $nbt->getString(self::TAG_WOOD_TYPE, $default = TreeType::OAK()->name());
        if ($woodType > 5 || $woodType < 0) {
            $woodType = $default;
        }
        $this->setWoodType($woodType);
        $this->setMaxHealth(4);
        $this->getNetworkProperties()->setGenericFlag(EntityMetadataFlags::STACKABLE, true);
    }

    public function saveNBT(): CompoundTag
    {
        $nbt = parent::saveNBT();
        $nbt->setString(self::TAG_WOOD_TYPE, $this->woodType);
        return $nbt;
    }

    public function getWoodType(): string
    {
        return $this->woodType;
    }

    public function setWoodType(string $woodType): void
    {
        $this->woodType = $woodType;
        $this->networkPropertiesDirty = true;
    }

    protected function syncNetworkData(EntityMetadataCollection $properties): void
    {
        parent::syncNetworkData($properties);
        $properties->setString(EntityMetadataProperties::VARIANT, $this->woodType);
    }

    public function attack(EntityDamageEvent $source): void
    {
        parent::attack($source);
        if (!$source->isCancelled()) {
            $this->getWorld()->broadcastPacketToViewers($this->getPosition(), ActorEventPacket::create(
                actorRuntimeId: $this->id,
                eventId: ActorEvent::HURT_ANIMATION,
                eventData: 0
            ));
            if ($source instanceof EntityDamageByEntityEvent) {
                $damager = $source->getDamager();
                if ($damager instanceof Player && !$damager->hasFiniteResources())
                    $this->kill();
            }
        }
    }

    public function kill(): void
    {
        parent::kill(); // todo: boat is broken
        if ($this->lastDamageCause instanceof EntityDamageByEntityEvent) {
            $damager = $this->lastDamageCause->getDamager();
            if (($damager instanceof Player) && !$damager->hasFiniteResources()) {
                return;
            }
        }
        foreach ($this->getDrops() as $drop) {
            $this->getWorld()->dropItem($this->getPosition(), $drop);
        }
    }

    public function getDrops(): array
    {
        return [
            VanillaItems::OAK_BOAT()->setBoatType(BoatType::getAll()[strtoupper($this->woodType)])->setCount(1)
        ];
    }

    public function onUpdate(int $currentTick): bool
    {
        $hasUpdate = parent::onUpdate($currentTick);
        if ($this->closed) {
            return false;
        }
        $loc = $this->location;
        if (
            !$this->isUnderwater() &&
            $this->getWorld()->getBlockAt((int)$loc->x, (int)$loc->y - 1, (int)$loc->z)->getTypeId() == VanillaBlocks::WATER()->getTypeId()
        )
            $this->gravity = 0;
        else $this->gravity = 0.04;
        if ($currentTick & 10 == 0 && $this->getHealth() < $this->getMaxHealth()) {
            $this->heal(new EntityRegainHealthEvent($this, 1, EntityRegainHealthEvent::CAUSE_REGEN));
        }
        return $hasUpdate;
    }

    public function absoluteMove(Vector3 $pos, float $yaw = 0, float $pitch = 0): void
    {
        $this->location->withComponents($pos->x, $pos->y, $pos->z);
        $this->setRotation($yaw, $pitch);
        $this->updateMovement();
    }

    public function handleAnimatePacket(AnimatePacket $packet): void
    {
        switch ($packet->action) {
            case self::ACTION_ROW_RIGHT:
                $this->getNetworkProperties()->setFloat(EntityMetadataProperties::PADDLE_TIME_RIGHT, $packet->float);
                $this->networkPropertiesDirty = true;
                break;
            case self::ACTION_ROW_LEFT:
                $this->getNetworkProperties()->setFloat(EntityMetadataProperties::PADDLE_TIME_LEFT, $packet->float);
                $this->networkPropertiesDirty = true;
                break;
            default:
                break;
        }
    }

    protected function getInitialDragMultiplier(): float
    {
        return 0.3;
    }

    protected function getInitialGravity(): float
    {
        return 0;
    }

    public function onInteract(Player $player, Vector3 $clickPos): bool
    {
        $this->link($player);
        return true;
    }
}
