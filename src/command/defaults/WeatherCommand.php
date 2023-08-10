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
use pocketmine\lang\Translatable;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\player\Player;
use pocketmine\world\World;

class WeatherCommand extends VanillaCommand
{

	public function __construct()
	{
		parent::__construct(
			"weather",
			KnownTranslationFactory::pocketmine_command_weather_description(),
			KnownTranslationFactory::commands_weather_usage()
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_WEATHER);
	}

	public static function translateWeather(int $weather): Translatable
	{
		return KnownTranslationFactory::__callStatic("commands.weather.weather" . $weather, []);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args)
	{
		if ($sender instanceof Player) $world = $sender->getWorld();
		if (!isset($args[0])) throw new InvalidCommandSyntaxException();
		if ($args[0] == "clear" || $args[0] == "query") {
			if (!isset($world)) {
				if (!isset($args[1])) throw new InvalidCommandSyntaxException();
				$world = $sender->getServer()->getWorldManager()->getWorldByName($args[1]);
				if (!$world instanceof World) {
					$sender->sendMessage(KnownTranslationFactory::commands_generic_world_notFound($args[1]));
					return;
				}
			}
			if ($args[0] == "clear") {
				$world->setWeather(World::WEATHER_CLEAR, 0);
				$sender->sendMessage(KnownTranslationFactory::commands_weather_success_clear());
			} else {
				$weather = self::translateWeather($world->getWeather());
				$sender->sendMessage(KnownTranslationFactory::commands_weather_query($weather));
			}
			return;
		}
		$duration = $args[1] ?? null;
		if (!is_null($duration) && !is_numeric($duration)) throw new InvalidCommandSyntaxException();
		if ($args[0] == "rain" || $args[0] == "thunder") {
			if (!isset($world)) {
				if (!isset($args[2])) throw new InvalidCommandSyntaxException();
				$world = $sender->getServer()->getWorldManager()->getWorldByName($args[1]);
				if (!$world instanceof World) {
					$sender->sendMessage(KnownTranslationFactory::commands_generic_world_notFound($args[1]));
					return;
				}
			}
			if ($args[0] === "rain") {
				$world->setWeather(World::WEATHER_MODERATE_RAIN, $duration);
				$sender->sendMessage(KnownTranslationFactory::commands_weather_success_rain());
			} else {
				$world->setWeather(World::WEATHER_MODERATE_THUNDER, $duration);
				$sender->sendMessage(KnownTranslationFactory::commands_weather_success_thunder());
			}
			return;
		}
		throw new InvalidCommandSyntaxException();
	}
}
