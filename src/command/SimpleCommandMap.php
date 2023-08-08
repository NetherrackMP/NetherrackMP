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

namespace pocketmine\command;

use InvalidArgumentException;
use pocketmine\command\defaults\BanCommand;
use pocketmine\command\defaults\BanIpCommand;
use pocketmine\command\defaults\BanListCommand;
use pocketmine\command\defaults\ClearCommand;
use pocketmine\command\defaults\ClsCommand;
use pocketmine\command\defaults\DefaultGamemodeCommand;
use pocketmine\command\defaults\DeopCommand;
use pocketmine\command\defaults\DifficultyCommand;
use pocketmine\command\defaults\DumpMemoryCommand;
use pocketmine\command\defaults\EffectCommand;
use pocketmine\command\defaults\EnchantCommand;
use pocketmine\command\defaults\GamemodeCommand;
use pocketmine\command\defaults\GarbageCollectorCommand;
use pocketmine\command\defaults\GiveCommand;
use pocketmine\command\defaults\HelpCommand;
use pocketmine\command\defaults\KickCommand;
use pocketmine\command\defaults\KillCommand;
use pocketmine\command\defaults\ListCommand;
use pocketmine\command\defaults\MeCommand;
use pocketmine\command\defaults\OpCommand;
use pocketmine\command\defaults\PardonCommand;
use pocketmine\command\defaults\PardonIpCommand;
use pocketmine\command\defaults\ParticleCommand;
use pocketmine\command\defaults\PluginsCommand;
use pocketmine\command\defaults\SaveCommand;
use pocketmine\command\defaults\SaveOffCommand;
use pocketmine\command\defaults\SaveOnCommand;
use pocketmine\command\defaults\SayCommand;
use pocketmine\command\defaults\SeedCommand;
use pocketmine\command\defaults\SetWorldSpawnCommand;
use pocketmine\command\defaults\SpawnpointCommand;
use pocketmine\command\defaults\StatusCommand;
use pocketmine\command\defaults\StopCommand;
use pocketmine\command\defaults\TeleportCommand;
use pocketmine\command\defaults\TellCommand;
use pocketmine\command\defaults\TimeCommand;
use pocketmine\command\defaults\TimingsCommand;
use pocketmine\command\defaults\TitleCommand;
use pocketmine\command\defaults\TransferServerCommand;
use pocketmine\command\defaults\VanillaCommand;
use pocketmine\command\defaults\VersionCommand;
use pocketmine\command\defaults\WeatherCommand;
use pocketmine\command\defaults\WhitelistCommand;
use pocketmine\command\utils\CommandStringHelper;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\entity\effect\StringToEffectParser;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\ItemBlock;
use pocketmine\item\StringToItemParser;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\lang\Translatable;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandData;
use pocketmine\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\network\mcpe\protocol\types\command\CommandEnumConstraint;
use pocketmine\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\network\mcpe\protocol\types\LevelEvent;
use pocketmine\network\mcpe\protocol\types\ParticleIds;
use pocketmine\network\mcpe\protocol\UpdateSoftEnumPacket;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\timings\Timings;
use pocketmine\utils\TextFormat;
use pocketmine\world\World;
use ReflectionClass;
use function array_shift;
use function count;
use function implode;
use function str_contains;
use function strcasecmp;
use function strtolower;
use function trim;

class SimpleCommandMap implements CommandMap
{

	/** @var Command[] */
	protected array $knownCommands = [];
	/** @var CommandData[] $manualOverrides */
	private array $manualOverrides = [];
	/** @var string[] $debugCommands */
	private array $debugCommands = ["dumpmemory", "gc", "timings", "status"];
	/** @var CommandEnum[] $hardcodedEnums */
	private array $hardcodedEnums = [];
	/** @var CommandEnum[] $softEnums */
	private array $softEnums = [];
	/** @var CommandEnumConstraint[] $enumConstraints */
	private array $enumConstraints = [];

	public function __construct(private readonly Server $server)
	{
		$this->setDefaultCommands();
		$this->setDefaultEnumData();
	}

	private function setDefaultEnumData(): void
	{
		$worldConstants = array_keys((new ReflectionClass(World::class))->getConstants());
		$levelEventConstants = array_keys((new ReflectionClass(LevelEvent::class))->getConstants());

		$this->addHardcodedEnum(new CommandEnum("boolean", ["true", "false"], false), false);
		$this->addHardcodedEnum(new CommandEnum("bool", ["true", "false"], false), false);

		$difficultyOptions = array_filter($worldConstants, fn(string $constant) => str_starts_with($constant, "DIFFICULTY_"));
		$difficultyOptions = array_map(fn(string $difficultyString) => substr($difficultyString, strlen("DIFFICULTY_")), $difficultyOptions);
		$difficultyOptions = array_merge($difficultyOptions, array_map(fn(string $difficultyString) => $difficultyString[0], $difficultyOptions));
		$difficultyOptions = array_map(fn(string $difficultyString) => mb_strtolower($difficultyString), $difficultyOptions);
		$this->addHardcodedEnum(new CommandEnum("Difficulty", $difficultyOptions, false), false);

		$gamemodeOptions = array_keys(GameMode::getAll());
		$gamemodeOptions = array_merge($gamemodeOptions, array_map(fn(string $gameModeString) => $gameModeString[0], $gamemodeOptions));
		$gamemodeOptions = array_map(fn(string $gameModeString) => mb_strtolower($gameModeString), $gamemodeOptions);
		$this->addHardcodedEnum(new CommandEnum("GameMode", $gamemodeOptions, false), false); // TODO: change to translated strings

		$particleOptions = array_filter($levelEventConstants, fn(string $constant) => str_starts_with($constant, "PARTICLE_"));
		$particleOptions = array_map(fn(string $particleString) => substr($particleString, strlen("PARTICLE_")), $particleOptions);
		$particleOptions = array_merge($particleOptions, array_keys((new ReflectionClass(ParticleIds::class))->getConstants()));
		$particleOptions = array_unique(array_map(fn(string $particleString) => mb_strtolower($particleString), $particleOptions));
		$this->addHardcodedEnum(new CommandEnum("Particle", $particleOptions, false), false);

		$soundOptions = array_filter($levelEventConstants, fn(string $constant) => str_starts_with($constant, "SOUND_"));
		$soundOptions = array_map(fn(string $soundString) => substr($soundString, strlen("SOUND_")), $soundOptions);
		$soundOptions = array_map(fn(string $soundString) => mb_strtolower($soundString), $soundOptions);
		$this->addHardcodedEnum(new CommandEnum("Sound", $soundOptions, false), false);

		$timeSpecOptions = array_filter($worldConstants, fn(string $constant) => str_starts_with($constant, "TIME_"));
		$timeSpecOptions = array_map(fn(string $timeSpecString) => substr($timeSpecString, strlen("TIME_")), $timeSpecOptions);
		$timeSpecOptions = array_map(fn(string $timeSpecString) => mb_strtolower($timeSpecString), $timeSpecOptions);
		$this->addHardcodedEnum(new CommandEnum("TimeSpec", $timeSpecOptions, false), false);

		/** @var string[] $effectOptions */
		$effectOptions = StringToEffectParser::getInstance()->getKnownAliases();
		$this->addSoftEnum(new CommandEnum("Effect", $effectOptions, true), false);
		$this->addSoftEnum(new CommandEnum("Effects", $effectOptions, true), false);
		/** @var string[] $enchantmentOptions */
		$enchantmentOptions = StringToEnchantmentParser::getInstance()->getKnownAliases();
		$this->addSoftEnum(new CommandEnum("Enchant", $enchantmentOptions, true), false);
		$this->addSoftEnum(new CommandEnum("Enchants", $enchantmentOptions, true), false);
		$this->addSoftEnum(new CommandEnum("Enchantment", $enchantmentOptions, true), false); // proper english word
		$this->addSoftEnum(new CommandEnum("Enchantments", $enchantmentOptions, true), false); // proper english word (plural)
		/** @var string[] $itemOptions */
		$itemOptions = StringToItemParser::getInstance()->getKnownAliases();
		$itemOptions = array_filter($itemOptions, fn(string $itemName) => str_starts_with($itemName, "minecraft:"));
		$this->addSoftEnum(new CommandEnum("Item", $itemOptions, true), false);
		$this->addSoftEnum(new CommandEnum("Items", $itemOptions, true), false);

		$blocks = [];
		foreach ($itemOptions as $alias) {
			$item = StringToItemParser::getInstance()->parse($alias);
			if ($item instanceof ItemBlock)
				$blocks[] = $alias;
		}
		$this->addSoftEnum(new CommandEnum("Block", $blocks, true), false);
	}

	private function setDefaultCommands(): void
	{
		$list = [];
		foreach ([
					 new BanCommand(),
					 new BanIpCommand(),
					 new BanListCommand(),
					 new ClearCommand(),
					 new ClsCommand(),
					 new DefaultGamemodeCommand(),
					 new DeopCommand(),
					 new DifficultyCommand(),
					 new DumpMemoryCommand(),
					 new EffectCommand(),
					 new EnchantCommand(),
					 new GamemodeCommand(),
					 new GarbageCollectorCommand(),
					 new GiveCommand(),
					 new HelpCommand(),
					 new KickCommand(),
					 new KillCommand(),
					 new ListCommand(),
					 new MeCommand(),
					 new OpCommand(),
					 new PardonCommand(),
					 new PardonIpCommand(),
					 new ParticleCommand(),
					 new PluginsCommand(),
					 new SaveCommand(),
					 new SaveOffCommand(),
					 new SaveOnCommand(),
					 new SayCommand(),
					 new SeedCommand(),
					 new SetWorldSpawnCommand(),
					 new SpawnpointCommand(),
					 new StatusCommand(),
					 new StopCommand(),
					 new TeleportCommand(),
					 new TellCommand(),
					 new TimeCommand(),
					 new TimingsCommand(),
					 new TitleCommand(),
					 new TransferServerCommand(),
					 new VersionCommand(),
					 new WeatherCommand(),
					 new WhitelistCommand()
				 ] as $cmd) {
			if ($this->server->getConfigGroup()->getPropertyBool("commands." . $cmd->getName() . ".enabled", true))
				$list[] = $cmd;
		}
		$this->registerAll("pocketmine", $list);
	}

	public function registerAll(string $fallbackPrefix, array $commands): void
	{
		foreach ($commands as $command) {
			$this->register($fallbackPrefix, $command);
		}
	}

	public function register(string $fallbackPrefix, Command $command, ?string $label = null): bool
	{
		if (count($command->getPermissions()) === 0) {
			throw new InvalidArgumentException("Commands must have a permission set");
		}

		if ($label === null) {
			$label = $command->getLabel();
		}
		$label = trim($label);
		$fallbackPrefix = strtolower(trim($fallbackPrefix));

		$registered = $this->registerAlias($command, false, $fallbackPrefix, $label);

		$aliases = $command->getAliases();
		foreach ($aliases as $index => $alias) {
			if (!$this->registerAlias($command, true, $fallbackPrefix, $alias)) {
				unset($aliases[$index]);
			}
		}
		$command->setAliases($aliases);

		if (!$registered) {
			$command->setLabel($fallbackPrefix . ":" . $label);
		}

		$command->register($this);

		return $registered;
	}

	public function unregister(Command $command): bool
	{
		foreach ($this->knownCommands as $lbl => $cmd) {
			if ($cmd === $command) {
				unset($this->knownCommands[$lbl]);
			}
		}

		$command->unregister($this);

		return true;
	}

	private function registerAlias(Command $command, bool $isAlias, string $fallbackPrefix, string $label): bool
	{
		$this->knownCommands[$fallbackPrefix . ":" . $label] = $command;
		if (($command instanceof VanillaCommand || $isAlias) && isset($this->knownCommands[$label])) {
			return false;
		}

		if (isset($this->knownCommands[$label]) && $this->knownCommands[$label]->getLabel() === $label) {
			return false;
		}

		if (!$isAlias) {
			$command->setLabel($label);
		}

		$this->knownCommands[$label] = $command;

		return true;
	}

	public function dispatch(CommandSender $sender, string $cmdLine): bool
	{
		$args = CommandStringHelper::parseQuoteAware($cmdLine);

		$sentCommandLabel = array_shift($args);
		if ($sentCommandLabel !== null && ($target = $this->getCommand($sentCommandLabel)) !== null) {
			$timings = Timings::getCommandDispatchTimings($target->getLabel());
			$timings->startTiming();
			// todo: command selectors etc.
			try {
				if ($target->testPermission($sender)) $target->execute($sender, $sentCommandLabel, $args);
			} catch (InvalidCommandSyntaxException) {
				$sender->sendMessage($sender->getLanguage()->translate(KnownTranslationFactory::commands_generic_usage($target->getUsage())));
			} finally {
				$timings->stopTiming();
			}
			return true;
		}

		$sender->sendMessage(KnownTranslationFactory::pocketmine_command_notFound($sentCommandLabel ?? "", "/help")->prefix(TextFormat::RED));
		return false;
	}

	public function clearCommands(): void
	{
		foreach ($this->knownCommands as $command) {
			$command->unregister($this);
		}
		$this->knownCommands = [];
		$this->setDefaultCommands();
	}

	public function getCommand(string $name): ?Command
	{
		return $this->knownCommands[$name] ?? null;
	}

	/**
	 * @return Command[]
	 */
	public function getCommands(): array
	{
		return $this->knownCommands;
	}

	public function registerServerAliases(): void
	{
		$values = $this->server->getCommandAliases();

		foreach ($values as $alias => $commandStrings) {
			if (str_contains($alias, ":")) {
				$this->server->getLogger()->warning($this->server->getLanguage()->translate(KnownTranslationFactory::pocketmine_command_alias_illegal($alias)));
				continue;
			}

			$targets = [];
			$bad = [];
			$recursive = [];

			foreach ($commandStrings as $commandString) {
				$args = CommandStringHelper::parseQuoteAware($commandString);
				$commandName = array_shift($args) ?? "";
				$command = $this->getCommand($commandName);

				if ($command === null) {
					$bad[] = $commandString;
				} elseif (strcasecmp($commandName, $alias) === 0) {
					$recursive[] = $commandString;
				} else {
					$targets[] = $commandString;
				}
			}

			if (count($recursive) > 0) {
				$this->server->getLogger()->warning($this->server->getLanguage()->translate(KnownTranslationFactory::pocketmine_command_alias_recursive($alias, implode(", ", $recursive))));
				continue;
			}

			if (count($bad) > 0) {
				$this->server->getLogger()->warning($this->server->getLanguage()->translate(KnownTranslationFactory::pocketmine_command_alias_notFound($alias, implode(", ", $bad))));
				continue;
			}

			//These registered commands have absolute priority
			$lowerAlias = strtolower($alias);
			if (count($targets) > 0) {
				$this->knownCommands[$lowerAlias] = new FormattedCommandAlias($lowerAlias, $targets);
			} else {
				unset($this->knownCommands[$lowerAlias]);
			}

		}
	}

	public function generatePlayerSpecificCommandData(Command $command, Player $player): CommandData
	{
		$language = $player->getLanguage();
		$name = $command->getTranslatedName($language);
		$aliases = $command->getAliases();
		$description = $command->getDescription();
		$description = $description instanceof Translatable ? $language->translate($description) : $description;
		$usage = $command->getUsage();
		$usage = $usage instanceof Translatable ? $language->translate($usage) : $usage;
		$hasPermission = $command->testPermissionSilent($player);
		$filteredData = array_filter(
			$this->getManualOverrides(),
			fn(CommandData $data) => $name === $data->name
		);
		foreach ($filteredData as $data) {
			$data->description = $description;
			$data->permission = (int)!$hasPermission;
			if (!$data->aliases instanceof CommandEnum) $data->aliases = $this->generateAliasEnum($name, $aliases);
			return $data;
		}
		return $this->generateGenericCommandData($name, $aliases, $description, $usage, $hasPermission);
	}

	/**
	 * @phpstan-param string[] $aliases
	 */
	public function generateGenericCommandData(string $name, array $aliases, string $description, string $usage, bool $hasPermission = false): CommandData
	{
		$hasPermission = (int)!$hasPermission;

		if ($usage === "" || $usage[0] === "%") {
			$data = $this->generateDefaultCommandData($name, $aliases, $description);
			$data->permission = $hasPermission;
			return $data;
		}
		$usages = explode(" OR ", $usage); // split command trees
		$overloads = [];
		$enumCount = 0;
		for ($tree = 0; $tree < count($usages); ++$tree) {
			$usage = $usages[$tree];
			$treeOverloads = [];
			$commandString = explode(" ", $usage)[0];
			preg_match_all("/\h*([<\[])?\h*([\w|]+)\h*:?\h*([\w\h]+)?\h*[>\]]?\h*/iu", $usage, $matches, PREG_PATTERN_ORDER, strlen($commandString)); // https://regex101.com/r/1REoJG/22
			$argumentCount = count($matches[0]);
			if ($argumentCount > 0) for ($argNumber = 0; $argNumber < $argumentCount; ++$argNumber) {
				if ($matches[1][$argNumber] === "" || $matches[3][$argNumber] === "") {
					$paramName = mb_strtolower($matches[2][$argNumber]);
					$softEnums = $this->getSoftEnums();
					if (isset($softEnums[$paramName])) {
						$enum = $softEnums[$paramName];
					} else {
						$this->addSoftEnum($enum = new CommandEnum($paramName, [$paramName], true), false);
					}
					$treeOverloads[$argNumber] = CommandParameter::enum($paramName, $enum, CommandParameter::FLAG_FORCE_COLLAPSE_ENUM); // collapse and assume required because no $optional identifier exists in usage message
					continue;
				}
				$optional = str_contains($matches[1][$argNumber], "[");
				$paramName = mb_strtolower($matches[2][$argNumber]);
				$paramType = mb_strtolower($matches[3][$argNumber] ?? "");
				if (in_array($paramType, array_keys(array_merge($this->softEnums, $this->hardcodedEnums)), true)) {
					$enum = $this->getSoftEnums()[$paramType] ?? $this->getHardcodedEnums()[$paramType];
					$treeOverloads[$argNumber] = CommandParameter::enum($paramName, $enum, 0, $optional); // do not collapse because there is an $optional identifier in usage message
				} elseif (str_contains($paramName, "|")) {
					$enumValues = explode("|", $paramName);
					$this->addSoftEnum($enum = new CommandEnum($name . " Enum#" . ++$enumCount, $enumValues, true), false);
					$treeOverloads[$argNumber] = CommandParameter::enum($paramName, $enum, CommandParameter::FLAG_FORCE_COLLAPSE_ENUM, $optional);
				} elseif (str_contains($paramName, "/")) {
					$enumValues = explode("/", $paramName);
					$this->addSoftEnum($enum = new CommandEnum($name . " Enum#" . ++$enumCount, $enumValues, true), false);
					$treeOverloads[$argNumber] = CommandParameter::enum($paramName, $enum, CommandParameter::FLAG_FORCE_COLLAPSE_ENUM, $optional);
				} else {
					$paramType = match ($paramType) {
						"int" => AvailableCommandsPacket::ARG_TYPE_INT,
						"float", "double", "number" => AvailableCommandsPacket::ARG_TYPE_FLOAT,
						"mixed" => AvailableCommandsPacket::ARG_TYPE_VALUE,
						"player", "target" => AvailableCommandsPacket::ARG_TYPE_TARGET,
						"string" => AvailableCommandsPacket::ARG_TYPE_STRING,
						"x y z" => AvailableCommandsPacket::ARG_TYPE_POSITION,
						// "message" => AvailableCommandsPacket::ARG_TYPE_MESSAGE,
						default => AvailableCommandsPacket::ARG_TYPE_RAWTEXT,
						"json" => AvailableCommandsPacket::ARG_TYPE_JSON,
						"command" => AvailableCommandsPacket::ARG_TYPE_COMMAND,
					};
					$treeOverloads[$argNumber] = CommandParameter::standard($paramName, $paramType, 0, $optional);
				}
			}
			$overloads[$tree] = new CommandOverload(false, $treeOverloads);
		}
		return new CommandData(
			mb_strtolower($name),
			$description,
			(int)in_array($name, $this->debugCommands, true),
			$hasPermission,
			$this->generateAliasEnum($name, $aliases),
			$overloads,
			[]
		);
	}

	/**
	 * @phpstan-param string[] $aliases
	 */
	public function generateAliasEnum(string $name, array $aliases): ?CommandEnum
	{
		if (count($aliases) > 0) {
			if (!in_array($name, $aliases, true)) {
				$aliases[] = $name;
			}
			return new CommandEnum(ucfirst($name) . "Aliases", $aliases, false);
		}
		return null;
	}

	/**
	 * @param string $name
	 * @phpstan-param string[] $aliases
	 * @param string $description
	 * @return CommandData
	 */
	private function generateDefaultCommandData(string $name, array $aliases, string $description): CommandData
	{
		return new CommandData(
			mb_strtolower($name),
			$description,
			0,
			1,
			$this->generateAliasEnum($name, $aliases),
			[
				new CommandOverload(false, [
					CommandParameter::standard("args", AvailableCommandsPacket::ARG_TYPE_RAWTEXT, 0, true)
				])
			],
			[]
		);
	}

	public function addManualOverride(string $commandName, CommandData $data, bool $sendPacket = true): self
	{
		$this->manualOverrides[$commandName] = $data;
		if (!$sendPacket) return $this;
		foreach ($this->server->getOnlinePlayers() as $player)
			$player->getNetworkSession()->sendDataPacket(new AvailableCommandsPacket());
		return $this;
	}

	/**
	 * @return CommandData[]
	 */
	public function getManualOverrides(): array
	{
		return $this->manualOverrides;
	}

	public function addDebugCommand(string $commandName, bool $sendPacket = true): self
	{
		$this->debugCommands[] = $commandName;
		if (!$sendPacket) return $this;
		foreach ($this->server->getOnlinePlayers() as $player)
			$player->getNetworkSession()->sendDataPacket(new AvailableCommandsPacket());
		return $this;
	}

	/**
	 * @return string[]
	 */
	public function getDebugCommands(): array
	{
		return $this->debugCommands;
	}

	public function addHardcodedEnum(CommandEnum $enum, bool $sendPacket = true): self
	{
		foreach ($this->softEnums as $softEnum)
			if ($enum->getName() === $softEnum->getName())
				throw new InvalidArgumentException("Hardcoded enum is already in soft enum list.");
		$this->hardcodedEnums[mb_strtolower($enum->getName())] = $enum;
		if (!$sendPacket)
			return $this;
		foreach ($this->server->getOnlinePlayers() as $player)
			$player->getNetworkSession()->sendDataPacket(new AvailableCommandsPacket());
		return $this;
	}

	/**
	 * @return CommandEnum[]
	 */
	public function getHardcodedEnums(): array
	{
		return $this->hardcodedEnums;
	}

	public function addSoftEnum(CommandEnum $enum, bool $sendPacket = true): self
	{
		foreach (array_merge($this->softEnums, $this->hardcodedEnums) as $enum2)
			if ($enum->getName() === $enum2->getName())
				throw new InvalidArgumentException("Enum is already in an enum list.");
		$this->softEnums[mb_strtolower($enum->getName())] = $enum;
		if (!$sendPacket)
			return $this;
		$pk = UpdateSoftEnumPacket::create($enum->getName(), $enum->getValues(), UpdateSoftEnumPacket::TYPE_ADD);
		foreach ($this->server->getOnlinePlayers() as $player)
			$player->getNetworkSession()->sendDataPacket($pk);
		return $this;
	}

	public function updateSoftEnum(CommandEnum $enum, bool $sendPacket = true): self
	{
		if (!in_array($enum->getName(), array_keys($this->softEnums), true))
			throw new InvalidArgumentException("Enum is not in soft enum list.");
		$this->softEnums[mb_strtolower($enum->getName())] = $enum;
		if (!$sendPacket)
			return $this;
		$pk = UpdateSoftEnumPacket::create($enum->getName(), $enum->getValues(), UpdateSoftEnumPacket::TYPE_SET);
		foreach ($this->server->getOnlinePlayers() as $player)
			$player->getNetworkSession()->sendDataPacket($pk);
		return $this;
	}

	public function removeSoftEnum(CommandEnum $enum, bool $sendPacket = true): self
	{
		unset($this->softEnums[mb_strtolower($enum->getName())]);
		if (!$sendPacket)
			return $this;
		$pk = UpdateSoftEnumPacket::create($enum->getName(), $enum->getValues(), UpdateSoftEnumPacket::TYPE_REMOVE);
		foreach ($this->server->getOnlinePlayers() as $player)
			$player->getNetworkSession()->sendDataPacket($pk);
		return $this;
	}

	/*** @phpstan-return CommandEnum[] */
	public function getSoftEnums(): array
	{
		return $this->softEnums;
	}

	public function addEnumConstraint(CommandEnumConstraint $enumConstraint): self
	{
		foreach ($this->hardcodedEnums as $hardcodedEnum) if ($enumConstraint->getEnum()->getName() === $hardcodedEnum->getName()) {
			$this->enumConstraints[] = $enumConstraint;
			foreach ($this->server->getOnlinePlayers() as $player)
				$player->getNetworkSession()->sendDataPacket(new AvailableCommandsPacket());
			return $this;
		}
		foreach ($this->softEnums as $softEnum) if ($enumConstraint->getEnum()->getName() === $softEnum->getName()) {
			$this->enumConstraints[] = $enumConstraint;
			foreach ($this->server->getOnlinePlayers() as $player) {
				$player->getNetworkSession()->sendDataPacket(new AvailableCommandsPacket());
			}
			return $this;
		}
		throw new InvalidArgumentException("Enum name does not exist in any Enum list");
	}

	public function getEnumConstraints(): array
	{
		return $this->enumConstraints;
	}
}
