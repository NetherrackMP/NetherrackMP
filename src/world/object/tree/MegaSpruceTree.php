<?php

declare(strict_types=1);

namespace pocketmine\world\object\tree;

use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;

class MegaSpruceTree extends MegaPineTree{

	public function __construct(Random $random, BlockTransaction $transaction){
		parent::__construct($random, $transaction);
		$this->setLeavesHeight($this->leavesHeight + 10);
	}
}
