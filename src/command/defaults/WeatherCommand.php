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

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$sender instanceof Player) {
            $worlds = $sender->getServer()->getWorldManager()->getWorlds();
        } else $worlds = [$sender->getWorld()];
        if ($args[0] === "clear") {
            foreach ($worlds as $world)
                $world->setWeather(World::WEATHER_CLEAR, 0);
            $sender->sendMessage("Weather is now clear.");
            return;
        }
		$nameMap = [
			"Clear",
			"Light Rain",
			"Moderate Rain",
			"Heavy Rain",
			"Light Thunderstorms",
			"Moderate Thunderstorms",
			"Heavy Thunderstorms"
		];
        if ($args[0] === "query") {
            foreach ($worlds as $world)
                $sender->sendMessage("Weather is currently " . $nameMap[$world->getWeather()] . (count($worlds) == 1 ? "" : " in " . $world->getDisplayName()) . ".");
            return;
        }
        $duration = $args[1] ?? null;
        if (!is_null($duration) && !is_numeric($duration)) throw new InvalidCommandSyntaxException();
        if ($args[0] === "rain") {
            foreach ($worlds as $world)
                $world->setWeather(World::WEATHER_MODERATE_RAIN, $duration);
            $sender->sendMessage("Weather is now set to Moderate Rain.");
            return;
        }
        if ($args[0] === "thunder") {
            foreach ($worlds as $world)
                $world->setWeather(World::WEATHER_MODERATE_THUNDER, $duration);
            $sender->sendMessage("Weather is now set to Moderate Thunderstorms.");
            return;
        }
        throw new InvalidCommandSyntaxException();
    }
}
