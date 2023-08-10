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

use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\math\Vector3;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\player\Player;
use pocketmine\world\generator\GeneratorManager;
use pocketmine\world\World;
use pocketmine\world\WorldCreationOptions;

class WorldCommand extends VanillaCommand
{
	public function __construct()
	{
		parent::__construct(
			"world",
			KnownTranslationFactory::pocketmine_command_world_description(),
			KnownTranslationFactory::commands_world_usage()
		);
		$this->setPermissions([
			DefaultPermissionNames::COMMAND_WORLD_LIST,
			DefaultPermissionNames::COMMAND_WORLD_CREATE,
			DefaultPermissionNames::COMMAND_WORLD_INFO,
			DefaultPermissionNames::COMMAND_WORLD_DELETE,
			DefaultPermissionNames::COMMAND_WORLD_LOAD,
			DefaultPermissionNames::COMMAND_WORLD_UNLOAD,
			DefaultPermissionNames::COMMAND_WORLD_SETSPAWN,
			DefaultPermissionNames::COMMAND_WORLD_SETDEFAULT,
			DefaultPermissionNames::COMMAND_WORLD_COPY
		]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		// /world create <name: string> [seed: int] [generator: string]
		// /world list <name: string>
		// /world info [world: string]
		// /world delete <world: string>
		// /world load <world: string>
		// /world unload <world: string>
		// /world setspawn [world: string] [player: target] OR /world setspawn [world: string] [position: x y z]
		// /world setdefault [world: string]
		// /world copy <world: string> <name: string>
		// /world move <world: string> <name: string>
		// /world tp <world: string> [player: target]
		if (!isset($args[0])) throw new InvalidCommandSyntaxException();
		$manager = $sender->getServer()->getWorldManager();
		if ($args[0] == "create") {
			if (!isset($args[1])) throw new InvalidCommandSyntaxException();
			$name = $args[1];
			if ($manager->isWorldGenerated($name)) {
				$sender->sendMessage(KnownTranslationFactory::commands_world_exists($name));
				return;
			}
			$seed = $args[2] ?? mt_rand();
			if (is_numeric($seed)) $seed = (int)$seed;
			$generator = GeneratorManager::getInstance()->getGenerator($args[3] ?? "default");
			if ($generator === null) {
				$sender->sendMessage(KnownTranslationFactory::commands_world_invalid_generator());
				return;
			}
			$manager->generateWorld($name, WorldCreationOptions::create()
				->setSeed($seed)
				->setGeneratorClass($generator->getGeneratorClass()));
			$sender->sendMessage(KnownTranslationFactory::commands_world_success_create($name));
		} else if ($args[0] == "list") {
			$files = scandir($sender->getServer()->getDataPath() . "worlds");
			if (!$files) {
				$sender->sendMessage(KnownTranslationFactory::commands_world_failed_scan());
				return;
			}
			$sender->sendMessage(KnownTranslationFactory::commands_world_list_top((string)count($files)));
			foreach ($files as $file) {
				$world = $manager->getWorldByName($file);
				if ($world instanceof World) {
					$sender->sendMessage(KnownTranslationFactory::commands_world_list_loaded($file, (string)count($world->getPlayers())));
				} else $sender->sendMessage(KnownTranslationFactory::commands_world_list_unloaded($file));
			}
		} else if ($args[0] == "delete") {
			if (!isset($args[1])) throw new InvalidCommandSyntaxException();
			$name = $args[1];
			if (!$manager->isWorldGenerated($name)) {
				$sender->sendMessage(KnownTranslationFactory::commands_generic_world_notFound($name));
				return;
			}
			$world = $manager->getWorldByName($name);
			if ($world instanceof World && $manager->getDefaultWorld()->getId() === $world->getId()) {
				$sender->sendMessage(KnownTranslationFactory::commands_world_remove_default());
				return;
			}
			$manager->deleteWorld($name);
			$sender->sendMessage(KnownTranslationFactory::commands_world_success_delete($name));
		} else if ($args[0] == "copy") {
			if (!isset($args[1]) || !isset($args[2])) throw new InvalidCommandSyntaxException();
			$from = $args[1];
			$to = $args[2];
			if ($manager->isWorldGenerated($to)) {
				$sender->sendMessage(KnownTranslationFactory::commands_world_exists($to));
				return;
			}
			if (!$manager->isWorldGenerated($from)) {
				$sender->sendMessage(KnownTranslationFactory::commands_generic_world_notFound($from));
				return;
			}
			$manager->copyWorld($from, $to);
			$sender->sendMessage(KnownTranslationFactory::commands_world_success_copy($from, $to));
		} else if ($args[0] == "load") {
			if (!isset($args[1])) throw new InvalidCommandSyntaxException();
			$name = $args[1];
			if (!$manager->isWorldGenerated($name)) {
				$sender->sendMessage(KnownTranslationFactory::commands_generic_world_notFound($name));
				return;
			}
			if ($manager->isWorldLoaded($name)) {
				$sender->sendMessage(KnownTranslationFactory::commands_world_already_loaded($name));
				return;
			}
			$manager->loadWorld($name);
			$sender->sendMessage(KnownTranslationFactory::commands_world_success_load($name));
		} else if ($args[0] == "unload") {
			if (!isset($args[1])) throw new InvalidCommandSyntaxException();
			$name = $args[1];
			if (!$manager->isWorldGenerated($name)) {
				$sender->sendMessage(KnownTranslationFactory::commands_generic_world_notFound($name));
				return;
			}
			$world = $manager->getWorldByName($name);
			if (!$world instanceof World) {
				$sender->sendMessage(KnownTranslationFactory::commands_world_already_unloaded($name));
				return;
			}
			$manager->unloadWorld($world);
			$sender->sendMessage(KnownTranslationFactory::commands_world_success_unload($name));
		} else if ($args[0] == "tp") {
			if (!isset($args[1])) throw new InvalidCommandSyntaxException();
			$name = $args[1];
			if (!$manager->isWorldGenerated($name)) {
				$sender->sendMessage(KnownTranslationFactory::commands_generic_world_notFound($name));
				return;
			}
			$manager->loadWorld($name);
			$world = $manager->getWorldByName($name);
			if (!$world instanceof World) {
				$sender->sendMessage(KnownTranslationFactory::commands_generic_world_notFound($name));
				return;
			}
			$pName = $args[2] ?? $sender->getName();
			$player = $sender->getServer()->getPlayerByPrefix($pName);
			if (!$player instanceof Player) {
				$sender->sendMessage(KnownTranslationFactory::commands_generic_player_notFound());
				return;
			}
			$player->teleport($world->getSpawnLocation());
			$sender->sendMessage(KnownTranslationFactory::commands_world_success_tp($pName, $name));
		} else if ($args[0] == "info") {
			if (!isset($args[1]) && !$sender instanceof Player) throw new InvalidCommandSyntaxException();
			$name = $args[1] ?? $sender->getWorld()->getDisplayName();
			if (!$manager->isWorldGenerated($name)) {
				$sender->sendMessage(KnownTranslationFactory::commands_generic_world_notFound($name));
				return;
			}
			$manager->loadWorld($name);
			$world = $manager->getWorldByName($name);
			if (!$world instanceof World) {
				$sender->sendMessage(KnownTranslationFactory::commands_generic_world_notFound($name));
				return;
			}
			$sender->sendMessage(KnownTranslationFactory::commands_world_info_top());
			$sender->sendMessage(KnownTranslationFactory::commands_world_info_name($world->getDisplayName()));
			$sender->sendMessage(KnownTranslationFactory::commands_world_info_folderName($world->getFolderName()));
			$sender->sendMessage(KnownTranslationFactory::commands_world_info_players((string)count($world->getPlayers())));
			$sender->sendMessage(KnownTranslationFactory::commands_world_info_generator($world->getProvider()->getWorldData()->getGenerator()));
			$sender->sendMessage(KnownTranslationFactory::commands_world_info_seed((string)$world->getSeed()));
			$sender->sendMessage(KnownTranslationFactory::commands_world_info_time((string)$world->getTime()));
			$sender->sendMessage(KnownTranslationFactory::commands_world_info_weather(WeatherCommand::translateWeather($world->getWeather())));
		} else if ($args[0] == "setspawn") {
			if (!isset($args[1]) && !$sender instanceof Player) throw new InvalidCommandSyntaxException();
			$name = $args[1] ?? $sender->getWorld()->getDisplayName();
			if (!$manager->isWorldGenerated($name)) {
				$sender->sendMessage(KnownTranslationFactory::commands_generic_world_notFound($name));
				return;
			}
			$manager->loadWorld($name);
			$world = $manager->getWorldByName($name);
			if (!$world instanceof World) {
				$sender->sendMessage(KnownTranslationFactory::commands_generic_world_notFound($name));
				return;
			}

			if (count($args) == 5) { // setspawn <world> <x> <y> <z>
				if (
					!is_numeric($args[2]) ||
					!is_numeric($args[3]) ||
					!is_numeric($args[4])
				) {
					$sender->sendMessage(KnownTranslationFactory::commands_world_failure_xyz());
					return;
				}
				$world->setSpawnLocation($set = new Vector3((float)$args[2], (float)$args[3], (float)$args[4]));
			} else if (count($args) == 3) { // setspawn <world> <player>
				$player = $sender->getServer()->getPlayerByPrefix($args[2]);
				if (!$player instanceof Player) {
					$sender->sendMessage(KnownTranslationFactory::commands_generic_player_notFound());
					return;
				}
				$world->setSpawnLocation($set = $player->getLocation());
			} else if (count($args) == 2) { // setspawn <world>
				if (!$sender instanceof Player) throw new InvalidCommandSyntaxException();
				$world->setSpawnLocation($set = $sender->getLocation());
			} else if (count($args) == 1) { // setspawn
				if (!$sender instanceof Player) throw new InvalidCommandSyntaxException();
				$world->setSpawnLocation($set = $sender->getLocation());
			} else throw new InvalidCommandSyntaxException();
			$sender->sendMessage(KnownTranslationFactory::commands_world_success_setspawn(
				$world->getDisplayName(),
				number_format($set->x, 3),
				number_format($set->y, 3),
				number_format($set->z, 3)
			));
		} else if ($args[0] == "setdefault") {
			if (!isset($args[1]) && !$sender instanceof Player) throw new InvalidCommandSyntaxException();
			$name = $args[1] ?? $sender->getWorld()->getDisplayName();
			if (!$manager->isWorldGenerated($name)) {
				$sender->sendMessage(KnownTranslationFactory::commands_generic_world_notFound($name));
				return;
			}
			$manager->loadWorld($name);
			$world = $manager->getWorldByName($name);
			if (!$world instanceof World) {
				$sender->sendMessage(KnownTranslationFactory::commands_generic_world_notFound($name));
				return;
			}
			$manager->setDefaultWorld($world);
			$sender->sendMessage(KnownTranslationFactory::commands_world_success_setdefault($name));
		} else throw new InvalidCommandSyntaxException();
	}
}
