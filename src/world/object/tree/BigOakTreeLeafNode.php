<?php

declare(strict_types=1);

namespace pocketmine\world\object\tree;

final class BigOakTreeLeafNode{
	public function __construct(
		public int $x,
		public int $y,
		public int $z,
		public int $branchY
	){}
}
