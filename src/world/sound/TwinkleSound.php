<?php

namespace pocketmine\world\sound;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;

class TwinkleSound implements Sound
{
	public function encode(Vector3 $pos): array
	{
		return [LevelSoundEventPacket::nonActorSound(LevelSoundEvent::TWINKLE, $pos, false)];
	}
}
