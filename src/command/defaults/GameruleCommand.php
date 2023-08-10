<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
 */

declare(strict_types=1);

namespace pocketmine\command\defaults;

use Exception;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use function count;
use function memory_get_usage;
use function number_format;
use function round;

class GameruleCommand extends VanillaCommand
{

	public function __construct()
	{
		parent::__construct(
			"gamerule",
			KnownTranslationFactory::pocketmine_command_gamerule_description(),
			KnownTranslationFactory::pocketmine_command_gamerule_usage()
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_GAMERULE);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args): void
	{
		if ($sender instanceof Player) $worlds = [$sender->getWorld()];
		else $worlds = $sender->getServer()->getWorldManager()->getWorlds();
		if (isset($args[2])) {
			$worlds = [$sender->getServer()->getWorldManager()->getWorldByName($args[2])];
			if (is_null($worlds[0])) {
				$sender->sendMessage(KnownTranslationFactory::commands_generic_world_notFound($args[2]));
				return;
			}
		}
		if (!isset($args[0])) throw new InvalidCommandSyntaxException();
		$rule = $args[0];
		$set = $args[1] ?? null;
		foreach ($worlds as $world) {
			try {
				if (is_null($set)) {
					$got = $world->getGameRule($rule);
					if (is_bool($got)) $got = $got ? "true" : "false";
					$sender->sendMessage(
						count($worlds) == 1 ?
							KnownTranslationFactory::pocketmine_command_gamerule_success($rule, $got) :
							KnownTranslationFactory::pocketmine_command_gamerule_success_more($rule, $got, $world->getDisplayName())
					);
				} else {
					if ($set == "true" || $set == "false") $set = $set == "true";
					else if (is_numeric($set)) $set = str_contains($set, ".") ? (float)$set : (int)$set;
					else throw new InvalidCommandSyntaxException();
					$world->setGameRule($rule, $set);
					if (is_bool($set)) $set = $set ? "true" : "false";
					$sender->sendMessage(
						count($worlds) == 1 ?
							KnownTranslationFactory::pocketmine_command_gamerule_success($rule, (string)$set) :
							KnownTranslationFactory::pocketmine_command_gamerule_success_more($rule, (string)$set, $world->getDisplayName())
					);
				}
			} catch (Exception $e) {
				$sender->sendMessage(
					count($worlds) == 1 ?
						KnownTranslationFactory::pocketmine_command_gamerule_failed($rule) :
						KnownTranslationFactory::pocketmine_command_gamerule_failed_more($rule, $world->getDisplayName())
				);
			}
		}
	}
}
