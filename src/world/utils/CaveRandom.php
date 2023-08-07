<?php

namespace pocketmine\world\utils;

use pocketmine\utils\Random;

class CaveRandom extends Random
{

	/**
	 * @return int
	 */
	public function nextLong(): int
	{
		return (($this->nextSignedInt()) << 32) | $this->nextSignedInt();
	}
}
