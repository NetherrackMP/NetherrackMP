<?php

namespace pocketmine\event\block;

use InvalidArgumentException;
use pocketmine\block\Block;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\item\Item;

/**
 * Called when a non-player destroys a block somewhere in the world.
 */
class NaturalBlockBreakEvent extends BlockEvent implements Cancellable
{
	use CancellableTrait;

	/** @var Item[] */
	protected array $blockDrops = [];

	/**
	 * @param Item[] $drops
	 */
	public function __construct(
		Block          $block,
		protected Item $item,
		array          $drops = [],
		protected int  $xpDrops = 0
	)
	{
		parent::__construct($block);
		$this->setDrops($drops);
	}

	/**
	 * Returns the item used to destroy the block.
	 */
	public function getItem(): Item
	{
		return clone $this->item;
	}

	/**
	 * @return Item[]
	 */
	public function getDrops(): array
	{
		return $this->blockDrops;
	}

	/**
	 * @param Item[] $drops
	 */
	public function setDrops(array $drops): void
	{
		$this->setDropsVariadic(...$drops);
	}

	/**
	 * Variadic hack for easy array member type enforcement.
	 */
	public function setDropsVariadic(Item ...$drops): void
	{
		$this->blockDrops = $drops;
	}

	/**
	 * Returns how much XP will be dropped by breaking this block.
	 */
	public function getXpDropAmount(): int
	{
		return $this->xpDrops;
	}

	/**
	 * Sets how much XP will be dropped by breaking this block.
	 */
	public function setXpDropAmount(int $amount): void
	{
		if ($amount < 0) throw new InvalidArgumentException("Amount must be at least zero");
		$this->xpDrops = $amount;
	}
}
