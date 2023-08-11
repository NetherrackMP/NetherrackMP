<?php

namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\block\EndPortalFrame;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\sound\EndPortalFrameFillSound;

class EnderEye extends Item
{


	public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, array &$returnedItems): ItemUseResult
	{
		if (!$blockClicked instanceof EndPortalFrame || $blockClicked->hasEye()) return ItemUseResult::NONE();
		$pos = $blockClicked->getPosition();
		$pos->getWorld()->setBlock($pos, $blockClicked->setEye(true));
		$pos->getWorld()->addSound($pos, new EndPortalFrameFillSound());
		$this->pop();
		EndPortalFrame::tryCreatingPortal($pos);
		return ItemUseResult::SUCCESS();
	}
}
