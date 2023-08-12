<?php

namespace pocketmine\world;

final class GameRules
{
	public const TYPE_BOOL = 0;
	public const TYPE_INT = 1;
	public const TYPE_FLOAT = 2;

	public const FALL_DAMAGE = "falldamage";
	public const FIRE_DAMAGE = "firedamage";
	public const DO_DAYLIGHT_CYCLE = "dodaylightcycle";
	public const DO_TILE_DROPS = "dotiledrops";
	public const DROWNING_DAMAGE = "drowningdamage";
	public const KEEP_INVENTORY = "keepinventory";
	public const SHOW_COORDINATES = "showcoordinates";
	public const SHOW_DEATH_MESSAGES = "showdeathmessages";
	public const TNT_EXPLODES = "tntexplodes";
	public const PVP = "pvp";
	public const DO_FIRE_TICK = "dofiretick";
	public const COMMAND_BLOCK_OUTPUT = "commandblockoutput"; // todo
	public const COMMAND_BLOCKS_ENABLED = "commandblocksenabled"; // todo
	public const DO_ENTITY_DROPS = "doentitydrops"; // todo
	public const DO_IMMEDIATE_RESPAWN = "doimmediaterespawn"; // todo
	public const DO_INSOMNIA = "doinsomnia"; // todo
	public const DO_MOB_LOOT = "domobloot"; // todo
	public const DO_MOB_SPAWNING = "domobspawning"; // todo
	public const DO_WEATHER_CYCLE = "doweathercycle"; // todo
	public const FREEZE_DAMAGE = "freezedamage"; // todo
	public const MOB_GRIEFING = "mobgriefing"; // todo
	public const NATURAL_REGENERATION = "naturalregeneration"; // todo
	public const RESPAWN_BLOCKS_EXPLODE = "respawnblocksexplode"; // todo
	public const SEND_COMMAND_FEEDBACK = "sendcommandfeedback"; // todo
	public const SHOW_BORDER_EFFECT = "showbordereffect"; // todo
	public const SHOW_TAGS = "showtags"; // todo: is this client-sided?
	public const FUNCTION_COMMAND_LIMIT = "functioncommandlimit"; // todo
	public const MAX_COMMAND_CHAIN_LENGTH = "maxcommandchainlength"; // todo
	public const RANDOM_TICK_SPEED = "randomtickspeed"; // todo
	public const SPAWN_RADIUS = "spawnradius"; // todo

	public const TYPES = [
		self::PVP => self::TYPE_BOOL,
		self::FALL_DAMAGE => self::TYPE_BOOL,
		self::FIRE_DAMAGE => self::TYPE_BOOL,
		self::COMMAND_BLOCK_OUTPUT => self::TYPE_BOOL,
		self::COMMAND_BLOCKS_ENABLED => self::TYPE_BOOL,
		self::DO_DAYLIGHT_CYCLE => self::TYPE_BOOL,
		self::DO_ENTITY_DROPS => self::TYPE_BOOL,
		self::DO_FIRE_TICK => self::TYPE_BOOL,
		self::DO_IMMEDIATE_RESPAWN => self::TYPE_BOOL,
		self::DO_INSOMNIA => self::TYPE_BOOL,
		self::DO_MOB_LOOT => self::TYPE_BOOL,
		self::DO_MOB_SPAWNING => self::TYPE_BOOL,
		self::DO_TILE_DROPS => self::TYPE_BOOL,
		self::DO_WEATHER_CYCLE => self::TYPE_BOOL,
		self::DROWNING_DAMAGE => self::TYPE_BOOL,
		self::FREEZE_DAMAGE => self::TYPE_BOOL,
		self::KEEP_INVENTORY => self::TYPE_BOOL,
		self::MOB_GRIEFING => self::TYPE_BOOL,
		self::NATURAL_REGENERATION => self::TYPE_BOOL,
		self::RESPAWN_BLOCKS_EXPLODE => self::TYPE_BOOL,
		self::SEND_COMMAND_FEEDBACK => self::TYPE_BOOL,
		self::SHOW_BORDER_EFFECT => self::TYPE_BOOL,
		self::SHOW_COORDINATES => self::TYPE_BOOL,
		self::SHOW_DEATH_MESSAGES => self::TYPE_BOOL,
		self::SHOW_TAGS => self::TYPE_BOOL,
		self::TNT_EXPLODES => self::TYPE_BOOL,
		self::FUNCTION_COMMAND_LIMIT => self::TYPE_INT,
		self::MAX_COMMAND_CHAIN_LENGTH => self::TYPE_INT,
		self::RANDOM_TICK_SPEED => self::TYPE_INT,
		self::SPAWN_RADIUS => self::TYPE_INT
	];

	private function __construct()
	{
	}
}
