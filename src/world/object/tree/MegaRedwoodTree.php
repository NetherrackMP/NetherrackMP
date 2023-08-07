<?php

declare(strict_types=1);

namespace pocketmine\world\object\tree;

use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;
use function floor;

class MegaRedwoodTree extends MegaJungleTree
{

	protected int $leavesHeight;

	public function __construct(Random $random, BlockTransaction $transaction)
	{
		parent::__construct($random, $transaction);
		$this->setHeight($random->nextBoundedInt(15) + $random->nextBoundedInt(3) + 13);
		$this->setType(VanillaBlocks::SPRUCE_LOG(), VanillaBlocks::SPRUCE_LEAVES());
		$this->setLeavesHeight($random->nextBoundedInt(5) + ($random->nextBoolean() ? 3 : 13));
	}

	protected function setLeavesHeight(int $leavesHeight): void
	{
		$this->leavesHeight = $leavesHeight;
	}

	public function generate(ChunkManager $world, Random $random, int $sourceX, int $sourceY, int $sourceZ): bool
	{
		if ($this->cannotGenerateAt($sourceX, $sourceY, $sourceZ, $world)) {
			return false;
		}

		// generates the leaves
		$previousRadius = 0;
		for ($y = $sourceY + $this->height - $this->leavesHeight; $y <= $sourceY + $this->height; ++$y) {
			$n = $sourceY + $this->height - $y;
			$radius = (int)floor((float)$n / $this->leavesHeight * 3.5);
			if ($radius === $previousRadius && $n > 0 && $y % 2 === 0) {
				++$radius;
			}
			$this->generateLeaves($sourceX, $y, $sourceZ, $radius, false, $world);
			$previousRadius = $radius;
		}

		// generates the trunk
		$this->generateTrunk($world, $sourceX, $sourceY, $sourceZ);

		// blocks below trunk are always dirt
		$this->generateDirtBelowTrunk($sourceX, $sourceY, $sourceZ);
		return true;
	}

	protected function generateDirtBelowTrunk(int $blockX, int $blockY, int $blockZ): void
	{
		// mega redwood tree does not replaces blocks below (surely to preserves podzol)
	}
}
