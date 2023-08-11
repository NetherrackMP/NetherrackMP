<?php

namespace pocketmine\world\sound;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;

class EndPortalFrameOpenSound implements Sound
{
	public function encode(Vector3 $pos): array
	{
		return [LevelSoundEventPacket::nonActorSound(LevelSoundEvent::BLOCK_END_PORTAL_SPAWN, $pos, false)];
	}
}
