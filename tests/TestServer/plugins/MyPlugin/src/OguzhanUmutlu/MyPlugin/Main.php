<?php

namespace OguzhanUmutlu\MyPlugin;

use Exception;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase
{
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
	{
		$sender->hey();
		return true;
	}
}