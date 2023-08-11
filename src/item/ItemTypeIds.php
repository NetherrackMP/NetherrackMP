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

namespace pocketmine\item;

/**
 * Every item in {@link VanillaItems} has a corresponding constant in this class. These constants can be used to
 * identify and compare item types efficiently using {@link Item::getTypeId()}.
 *
 * WARNING: These are NOT a replacement for Minecraft legacy IDs. Do **NOT** hardcode their values, or store them in
 * configs or databases. They will change without warning.
 */
final class ItemTypeIds
{

	private function __construct()
	{
		//NOOP
	}

	public const ACACIA_BOAT = 20000;
	public const ACACIA_SIGN = 20001;
	public const APPLE = 20002;
	public const ARROW = 20003;
	public const BAKED_POTATO = 20004;
	public const BAMBOO = 20005;
	public const BANNER = 20006;
	public const BEETROOT = 20007;
	public const BEETROOT_SEEDS = 20008;
	public const BEETROOT_SOUP = 20009;
	public const BIRCH_BOAT = 20010;
	public const BIRCH_SIGN = 20011;
	public const BLAZE_POWDER = 20012;
	public const BLAZE_ROD = 20013;
	public const BLEACH = 20014;
	public const BONE = 20015;
	public const BONE_MEAL = 20016;
	public const BOOK = 20017;
	public const BOW = 20018;
	public const BOWL = 20019;
	public const BREAD = 20020;
	public const BRICK = 20021;
	public const BUCKET = 20022;
	public const CARROT = 20023;
	public const CHAINMAIL_BOOTS = 20024;
	public const CHAINMAIL_CHESTPLATE = 20025;
	public const CHAINMAIL_HELMET = 20026;
	public const CHAINMAIL_LEGGINGS = 20027;
	public const CHARCOAL = 20028;
	public const CHEMICAL_ALUMINIUM_OXIDE = 20029;
	public const CHEMICAL_AMMONIA = 20030;
	public const CHEMICAL_BARIUM_SULPHATE = 20031;
	public const CHEMICAL_BENZENE = 20032;
	public const CHEMICAL_BORON_TRIOXIDE = 20033;
	public const CHEMICAL_CALCIUM_BROMIDE = 20034;
	public const CHEMICAL_CALCIUM_CHLORIDE = 20035;
	public const CHEMICAL_CERIUM_CHLORIDE = 20036;
	public const CHEMICAL_CHARCOAL = 20037;
	public const CHEMICAL_CRUDE_OIL = 20038;
	public const CHEMICAL_GLUE = 20039;
	public const CHEMICAL_HYDROGEN_PEROXIDE = 20040;
	public const CHEMICAL_HYPOCHLORITE = 20041;
	public const CHEMICAL_INK = 20042;
	public const CHEMICAL_IRON_SULPHIDE = 20043;
	public const CHEMICAL_LATEX = 20044;
	public const CHEMICAL_LITHIUM_HYDRIDE = 20045;
	public const CHEMICAL_LUMINOL = 20046;
	public const CHEMICAL_MAGNESIUM_NITRATE = 20047;
	public const CHEMICAL_MAGNESIUM_OXIDE = 20048;
	public const CHEMICAL_MAGNESIUM_SALTS = 20049;
	public const CHEMICAL_MERCURIC_CHLORIDE = 20050;
	public const CHEMICAL_POLYETHYLENE = 20051;
	public const CHEMICAL_POTASSIUM_CHLORIDE = 20052;
	public const CHEMICAL_POTASSIUM_IODIDE = 20053;
	public const CHEMICAL_RUBBISH = 20054;
	public const CHEMICAL_SALT = 20055;
	public const CHEMICAL_SOAP = 20056;
	public const CHEMICAL_SODIUM_ACETATE = 20057;
	public const CHEMICAL_SODIUM_FLUORIDE = 20058;
	public const CHEMICAL_SODIUM_HYDRIDE = 20059;
	public const CHEMICAL_SODIUM_HYDROXIDE = 20060;
	public const CHEMICAL_SODIUM_HYPOCHLORITE = 20061;
	public const CHEMICAL_SODIUM_OXIDE = 20062;
	public const CHEMICAL_SUGAR = 20063;
	public const CHEMICAL_SULPHATE = 20064;
	public const CHEMICAL_TUNGSTEN_CHLORIDE = 20065;
	public const CHEMICAL_WATER = 20066;
	public const CHORUS_FRUIT = 20067;
	public const CLAY = 20068;
	public const CLOCK = 20069;
	public const CLOWNFISH = 20070;
	public const COAL = 20071;
	public const COCOA_BEANS = 20072;
	public const COMPASS = 20073;
	public const COOKED_CHICKEN = 20074;
	public const COOKED_FISH = 20075;
	public const COOKED_MUTTON = 20076;
	public const COOKED_PORKCHOP = 20077;
	public const COOKED_RABBIT = 20078;
	public const COOKED_SALMON = 20079;
	public const COOKIE = 20080;
	public const CORAL_FAN = 20081;
	public const DARK_OAK_BOAT = 20082;
	public const DARK_OAK_SIGN = 20083;
	public const DIAMOND = 20084;
	public const DIAMOND_AXE = 20085;
	public const DIAMOND_BOOTS = 20086;
	public const DIAMOND_CHESTPLATE = 20087;
	public const DIAMOND_HELMET = 20088;
	public const DIAMOND_HOE = 20089;
	public const DIAMOND_LEGGINGS = 20090;
	public const DIAMOND_PICKAXE = 20091;
	public const DIAMOND_SHOVEL = 20092;
	public const DIAMOND_SWORD = 20093;
	public const DRAGON_BREATH = 20094;
	public const DRIED_KELP = 20095;
	public const DYE = 20096;
	public const EGG = 20097;
	public const EMERALD = 20098;
	public const ENCHANTED_GOLDEN_APPLE = 20099;
	public const ENDER_PEARL = 20100;
	public const EXPERIENCE_BOTTLE = 20101;
	public const FEATHER = 20102;
	public const FERMENTED_SPIDER_EYE = 20103;
	public const FISHING_ROD = 20104;
	public const FLINT = 20105;
	public const FLINT_AND_STEEL = 20106;
	public const GHAST_TEAR = 20107;
	public const GLASS_BOTTLE = 20108;
	public const GLISTERING_MELON = 20109;
	public const GLOWSTONE_DUST = 20110;
	public const GOLD_INGOT = 20111;
	public const GOLD_NUGGET = 20112;
	public const GOLDEN_APPLE = 20113;
	public const GOLDEN_AXE = 20114;
	public const GOLDEN_BOOTS = 20115;
	public const GOLDEN_CARROT = 20116;
	public const GOLDEN_CHESTPLATE = 20117;
	public const GOLDEN_HELMET = 20118;
	public const GOLDEN_HOE = 20119;
	public const GOLDEN_LEGGINGS = 20120;
	public const GOLDEN_PICKAXE = 20121;
	public const GOLDEN_SHOVEL = 20122;
	public const GOLDEN_SWORD = 20123;
	public const GUNPOWDER = 20124;
	public const HEART_OF_THE_SEA = 20125;
	public const INK_SAC = 20126;
	public const IRON_AXE = 20127;
	public const IRON_BOOTS = 20128;
	public const IRON_CHESTPLATE = 20129;
	public const IRON_HELMET = 20130;
	public const IRON_HOE = 20131;
	public const IRON_INGOT = 20132;
	public const IRON_LEGGINGS = 20133;
	public const IRON_NUGGET = 20134;
	public const IRON_PICKAXE = 20135;
	public const IRON_SHOVEL = 20136;
	public const IRON_SWORD = 20137;
	public const JUNGLE_BOAT = 20138;
	public const JUNGLE_SIGN = 20139;
	public const LAPIS_LAZULI = 20140;
	public const LAVA_BUCKET = 20141;
	public const LEATHER = 20142;
	public const LEATHER_BOOTS = 20143;
	public const LEATHER_CAP = 20144;
	public const LEATHER_PANTS = 20145;
	public const LEATHER_TUNIC = 20146;
	public const MAGMA_CREAM = 20147;
	public const MELON = 20148;
	public const MELON_SEEDS = 20149;
	public const MILK_BUCKET = 20150;
	public const MINECART = 20151;
	public const MUSHROOM_STEW = 20152;
	public const NAUTILUS_SHELL = 20153;
	public const NETHER_BRICK = 20154;
	public const NETHER_QUARTZ = 20155;
	public const NETHER_STAR = 20156;
	public const OAK_BOAT = 20157;
	public const OAK_SIGN = 20158;
	public const PAINTING = 20159;
	public const PAPER = 20160;
	public const POISONOUS_POTATO = 20161;
	public const POPPED_CHORUS_FRUIT = 20162;
	public const POTATO = 20163;
	public const POTION = 20164;
	public const PRISMARINE_CRYSTALS = 20165;
	public const PRISMARINE_SHARD = 20166;
	public const PUFFERFISH = 20167;
	public const PUMPKIN_PIE = 20168;
	public const PUMPKIN_SEEDS = 20169;
	public const RABBIT_FOOT = 20170;
	public const RABBIT_HIDE = 20171;
	public const RABBIT_STEW = 20172;
	public const RAW_BEEF = 20173;
	public const RAW_CHICKEN = 20174;
	public const RAW_FISH = 20175;
	public const RAW_MUTTON = 20176;
	public const RAW_PORKCHOP = 20177;
	public const RAW_RABBIT = 20178;
	public const RAW_SALMON = 20179;
	public const RECORD_11 = 20180;
	public const RECORD_13 = 20181;
	public const RECORD_BLOCKS = 20182;
	public const RECORD_CAT = 20183;
	public const RECORD_CHIRP = 20184;
	public const RECORD_FAR = 20185;
	public const RECORD_MALL = 20186;
	public const RECORD_MELLOHI = 20187;
	public const RECORD_STAL = 20188;
	public const RECORD_STRAD = 20189;
	public const RECORD_WAIT = 20190;
	public const RECORD_WARD = 20191;
	public const REDSTONE_DUST = 20192;
	public const ROTTEN_FLESH = 20193;
	public const SCUTE = 20194;
	public const SHEARS = 20195;
	public const SHULKER_SHELL = 20196;
	public const SLIMEBALL = 20197;
	public const SNOWBALL = 20198;
	public const SPIDER_EYE = 20199;
	public const SPLASH_POTION = 20200;
	public const SPRUCE_BOAT = 20201;
	public const SPRUCE_SIGN = 20202;
	public const STEAK = 20203;
	public const STICK = 20204;
	public const STONE_AXE = 20205;
	public const STONE_HOE = 20206;
	public const STONE_PICKAXE = 20207;
	public const STONE_SHOVEL = 20208;
	public const STONE_SWORD = 20209;
	public const STRING = 20210;
	public const SUGAR = 20211;
	public const SWEET_BERRIES = 20212;
	public const TOTEM = 20213;
	public const WATER_BUCKET = 20214;
	public const WHEAT = 20215;
	public const WHEAT_SEEDS = 20216;
	public const WOODEN_AXE = 20217;
	public const WOODEN_HOE = 20218;
	public const WOODEN_PICKAXE = 20219;
	public const WOODEN_SHOVEL = 20220;
	public const WOODEN_SWORD = 20221;
	public const WRITABLE_BOOK = 20222;
	public const WRITTEN_BOOK = 20223;
	public const CRIMSON_SIGN = 20224;
	public const MANGROVE_SIGN = 20225;
	public const WARPED_SIGN = 20226;
	public const AMETHYST_SHARD = 20227;
	public const COPPER_INGOT = 20228;
	public const DISC_FRAGMENT_5 = 20229;
	public const ECHO_SHARD = 20230;
	public const GLOW_INK_SAC = 20231;
	public const HONEY_BOTTLE = 20232;
	public const HONEYCOMB = 20233;
	public const RECORD_5 = 20234;
	public const RECORD_OTHERSIDE = 20235;
	public const RECORD_PIGSTEP = 20236;
	public const NETHERITE_INGOT = 20237;
	public const NETHERITE_AXE = 20238;
	public const NETHERITE_HOE = 20239;
	public const NETHERITE_PICKAXE = 20240;
	public const NETHERITE_SHOVEL = 20241;
	public const NETHERITE_SWORD = 20242;
	public const NETHERITE_BOOTS = 20243;
	public const NETHERITE_CHESTPLATE = 20244;
	public const NETHERITE_HELMET = 20245;
	public const NETHERITE_LEGGINGS = 20246;
	public const PHANTOM_MEMBRANE = 20247;
	public const RAW_COPPER = 20248;
	public const RAW_IRON = 20249;
	public const RAW_GOLD = 20250;
	public const SPYGLASS = 20251;
	public const NETHERITE_SCRAP = 20252;
	public const POWDER_SNOW_BUCKET = 20253;
	public const LINGERING_POTION = 20254;
	public const FIRE_CHARGE = 20255;
	public const SUSPICIOUS_STEW = 20256;
	public const TURTLE_HELMET = 20257;
	public const MEDICINE = 20258;
	public const MANGROVE_BOAT = 20259;
	public const GLOW_BERRIES = 20260;
	public const CHERRY_SIGN = 20261;
	public const FIREWORKS = 20262;
	public const ELYTRA = 20263;
	public const ENDER_EYE = 20264;

	public const SHEEP_SPAWN_EGG = 20265;
	public const ZOMBIE_SPAWN_EGG = 20266;
	public const VILLAGER_SPAWN_EGG = 20267;
	public const SQUID_SPAWN_EGG = 20268;
	public const CHICKEN_SPAWN_EGG = 20269;
	public const COW_SPAWN_EGG = 20270;
	public const DONKEY_SPAWN_EGG = 20271;

	public const FIRST_UNUSED_ITEM_ID = 20272;

	private static int $nextDynamicId = self::FIRST_UNUSED_ITEM_ID;

	/**
	 * Returns a new runtime item type ID, e.g. for use by a custom item.
	 */
	public static function newId(): int
	{
		return self::$nextDynamicId++;
	}

	public static function fromBlockTypeId(int $blockTypeId): int
	{
		if ($blockTypeId < 0) {
			throw new \InvalidArgumentException("Block type IDs cannot be negative");
		}
		//negative item type IDs are treated as block IDs
		return -$blockTypeId;
	}

	public static function toBlockTypeId(int $itemTypeId): ?int
	{
		if ($itemTypeId > 0) { //not a blockitem
			return null;
		}
		return -$itemTypeId;
	}
}
