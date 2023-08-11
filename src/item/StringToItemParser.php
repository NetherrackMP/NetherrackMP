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

use pocketmine\block\Block;
use pocketmine\block\Light;
use pocketmine\block\utils\CopperOxidation;
use pocketmine\block\utils\CoralType;
use pocketmine\block\utils\DirtType;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\utils\FroglightType;
use pocketmine\block\utils\MobHeadType;
use pocketmine\block\utils\SlabType;
use pocketmine\block\VanillaBlocks as Blocks;
use pocketmine\item\VanillaItems as Items;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\StringToTParser;
use function array_keys;

/**
 * Handles parsing items from strings. This is used to interpret names from the /give command (and others).
 *
 * @phpstan-extends StringToTParser<Item>
 */
final class StringToItemParser extends StringToTParser
{
	use SingletonTrait;

	private static function make(): self
	{
		$result = new self();

		self::registerDynamicBlocks($result);
		self::registerBlocks($result);
		self::registerDynamicItems($result);
		self::registerItems($result);

		return $result;
	}

	private static function registerDynamicBlocks(self $result): void
	{
		foreach (DyeColor::getAll() as $color) {
			$register = fn(string $name, \Closure $callback) => $result->registerBlock($color->name() . "_" . $name, $callback);
			//wall and floor banner are the same item
			$register("banner", fn() => Blocks::BANNER()->setColor($color));
			$register("bed", fn() => Blocks::BED()->setColor($color));
			$register("candle", fn() => Blocks::DYED_CANDLE()->setColor($color));
			$register("carpet", fn() => Blocks::CARPET()->setColor($color));
			$register("concrete", fn() => Blocks::CONCRETE()->setColor($color));
			$register("concrete_powder", fn() => Blocks::CONCRETE_POWDER()->setColor($color));
			$register("glazed_terracotta", fn() => Blocks::GLAZED_TERRACOTTA()->setColor($color));
			$register("stained_clay", fn() => Blocks::STAINED_CLAY()->setColor($color));
			$register("stained_glass", fn() => Blocks::STAINED_GLASS()->setColor($color));
			$register("stained_glass_pane", fn() => Blocks::STAINED_GLASS_PANE()->setColor($color));
			$register("stained_hardened_glass", fn() => Blocks::STAINED_HARDENED_GLASS()->setColor($color));
			$register("stained_hardened_glass_pane", fn() => Blocks::STAINED_HARDENED_GLASS_PANE()->setColor($color));
			$register("wool", fn() => Blocks::WOOL()->setColor($color));
			$register("shulker_box", fn() => Blocks::DYED_SHULKER_BOX()->setColor($color));
		}

		foreach (CoralType::getAll() as $coralType) {
			$register = fn(string $name, \Closure $callback) => $result->registerBlock($coralType->name() . "_" . $name, $callback);
			$register("coral", fn() => Blocks::CORAL()->setCoralType($coralType));
			$register("coral_block", fn() => Blocks::CORAL_BLOCK()->setCoralType($coralType));
			//wall and floor coral fans are the same item
			$register("coral_fan", fn() => Blocks::CORAL_FAN()->setCoralType($coralType));
		}
		for ($i = Light::MIN_LIGHT_LEVEL; $i <= Light::MAX_LIGHT_LEVEL; $i++) {
			//helper aliases, since we don't support passing data values in /give
			$result->registerBlock("light_$i", fn() => Blocks::LIGHT()->setLightLevel($i));
			$result->registerBlock("light_block_$i", fn() => Blocks::LIGHT()->setLightLevel($i));
		}

		foreach (CopperOxidation::getAll() as $oxidation) {
			$oxPrefix = $oxidation->equals(CopperOxidation::NONE()) ? "" : $oxidation->name() . "_";
			if ($oxPrefix == "") continue;

			foreach (["" => false, "waxed_" => true] as $waxedPrefix => $waxed) {
				$register = fn(string $name, \Closure $callback) => $result->registerBlock($waxedPrefix . $oxPrefix . $name, $callback);
				$register("copper_block", fn() => Blocks::COPPER()->setOxidation($oxidation)->setWaxed($waxed));
				$register("cut_copper_block", fn() => Blocks::CUT_COPPER()->setOxidation($oxidation)->setWaxed($waxed));
				$register("cut_copper_stairs", fn() => Blocks::CUT_COPPER_STAIRS()->setOxidation($oxidation)->setWaxed($waxed));
				$register("cut_copper_slab", fn() => Blocks::CUT_COPPER_SLAB()->setOxidation($oxidation)->setWaxed($waxed));
			}
		}

		foreach (FroglightType::getAll() as $froglightType) {
			$result->registerBlock($froglightType->name() . "_froglight", fn() => Blocks::FROGLIGHT()->setFroglightType($froglightType));
		}
	}

	private static function registerBlocks(self $result): void
	{
		foreach (array_keys(Blocks::getAll()) as $k)
			$result->registerBlock(strtolower($k), fn() => Blocks::__callStatic($k, []));
		$result->registerBlock("acacia_door_block", fn() => Blocks::ACACIA_DOOR());
		$result->registerBlock("acacia_standing_sign", fn() => Blocks::ACACIA_SIGN());
		$result->registerBlock("acacia_wood_stairs", fn() => Blocks::ACACIA_STAIRS());
		$result->registerBlock("acacia_wooden_stairs", fn() => Blocks::ACACIA_STAIRS());
		$result->registerBlock("active_redstone_lamp", fn() => Blocks::REDSTONE_LAMP()->setPowered(true));
		$result->registerBlock("amethyst_block", fn() => Blocks::AMETHYST());
		$result->registerBlock("ateupd_block", fn() => Blocks::INFO_UPDATE2());
		$result->registerBlock("bed_block", fn() => Blocks::BED());
		$result->registerBlock("beetroot_block", fn() => Blocks::BEETROOTS());
		$result->registerBlock("big_dripleaf", fn() => Blocks::BIG_DRIPLEAF_HEAD());
		$result->registerBlock("birch_door_block", fn() => Blocks::BIRCH_DOOR());
		$result->registerBlock("birch_standing_sign", fn() => Blocks::BIRCH_SIGN());
		$result->registerBlock("birch_wood_stairs", fn() => Blocks::BIRCH_STAIRS());
		$result->registerBlock("birch_wooden_stairs", fn() => Blocks::BIRCH_STAIRS());
		$result->registerBlock("brewing_stand_block", fn() => Blocks::BREWING_STAND());
		$result->registerBlock("brick_block", fn() => Blocks::BRICKS());
		$result->registerBlock("bricks_block", fn() => Blocks::BRICKS());
		$result->registerBlock("burning_furnace", fn() => Blocks::FURNACE());
		$result->registerBlock("bush", fn() => Blocks::DEAD_BUSH());
		$result->registerBlock("cake_block", fn() => Blocks::CAKE());
		$result->registerBlock("carrot_block", fn() => Blocks::CARROTS());
		$result->registerBlock("chemistry_table", fn() => Blocks::COMPOUND_CREATOR());
		$result->registerBlock("chipped_anvil", fn() => Blocks::ANVIL()->setDamage(1));
		$result->registerBlock("clay_block", fn() => Blocks::CLAY());
		$result->registerBlock("coal_block", fn() => Blocks::COAL());
		$result->registerBlock("coarse_dirt", fn() => Blocks::DIRT()->setDirtType(DirtType::COARSE()));
		$result->registerBlock("cobble", fn() => Blocks::COBBLESTONE());
		$result->registerBlock("cobble_stairs", fn() => Blocks::COBBLESTONE_STAIRS());
		$result->registerBlock("cobble_wall", fn() => Blocks::COBBLESTONE_WALL());
		$result->registerBlock("cocoa", fn() => Blocks::COCOA_POD());
		$result->registerBlock("cocoa_block", fn() => Blocks::COCOA_POD());
		$result->registerBlock("cocoa_pods", fn() => Blocks::COCOA_POD());
		$result->registerBlock("colored_torch_bp", fn() => Blocks::BLUE_TORCH());
		$result->registerBlock("colored_torch_rg", fn() => Blocks::RED_TORCH());
		$result->registerBlock("comparator", fn() => Blocks::REDSTONE_COMPARATOR());
		$result->registerBlock("comparator_block", fn() => Blocks::REDSTONE_COMPARATOR());
		$result->registerBlock("concretepowder", fn() => Blocks::CONCRETE_POWDER());
		$result->registerBlock("coral_fan_dead", fn() => Blocks::CORAL_FAN()->setCoralType(CoralType::TUBE())->setDead(true));
		$result->registerBlock("coral_fan_hang", fn() => Blocks::WALL_CORAL_FAN());
		$result->registerBlock("coral_fan_hang2", fn() => Blocks::WALL_CORAL_FAN()->setCoralType(CoralType::BUBBLE()));
		$result->registerBlock("coral_fan_hang3", fn() => Blocks::WALL_CORAL_FAN()->setCoralType(CoralType::HORN()));
		$result->registerBlock("creeper_head", fn() => Blocks::MOB_HEAD()->setMobHeadType(MobHeadType::CREEPER()));
		$result->registerBlock("damaged_anvil", fn() => Blocks::ANVIL()->setDamage(2));
		$result->registerBlock("dark_oak_door_block", fn() => Blocks::DARK_OAK_DOOR());
		$result->registerBlock("dark_oak_standing_sign", fn() => Blocks::DARK_OAK_SIGN());
		$result->registerBlock("dark_oak_wood_stairs", fn() => Blocks::DARK_OAK_STAIRS());
		$result->registerBlock("dark_oak_wooden_stairs", fn() => Blocks::DARK_OAK_STAIRS());
		$result->registerBlock("darkoak_sign", fn() => Blocks::DARK_OAK_SIGN());
		$result->registerBlock("darkoak_standing_sign", fn() => Blocks::DARK_OAK_SIGN());
		$result->registerBlock("darkoak_wall_sign", fn() => Blocks::DARK_OAK_WALL_SIGN());
		$result->registerBlock("daylight_detector", fn() => Blocks::DAYLIGHT_SENSOR());
		$result->registerBlock("daylight_detector_inverted", fn() => Blocks::DAYLIGHT_SENSOR()->setInverted(true));
		$result->registerBlock("daylight_sensor_inverted", fn() => Blocks::DAYLIGHT_SENSOR()->setInverted(true));
		$result->registerBlock("deadbush", fn() => Blocks::DEAD_BUSH());
		$result->registerBlock("diamond_block", fn() => Blocks::DIAMOND());
		$result->registerBlock("dirt_with_roots", fn() => Blocks::DIRT()->setDirtType(DirtType::ROOTED()));
		$result->registerBlock("door_block", fn() => Blocks::OAK_DOOR());
		$result->registerBlock("double_plant", fn() => Blocks::SUNFLOWER());
		$result->registerBlock("double_red_sandstone_slab", fn() => Blocks::RED_SANDSTONE_SLAB()->setSlabType(SlabType::DOUBLE()));
		$result->registerBlock("double_slab", fn() => Blocks::STONE_SLAB()->setSlabType(SlabType::DOUBLE()));
		$result->registerBlock("double_slabs", fn() => Blocks::STONE_SLAB()->setSlabType(SlabType::DOUBLE()));
		$result->registerBlock("double_stone_slab", fn() => Blocks::STONE_SLAB()->setSlabType(SlabType::DOUBLE()));
		$result->registerBlock("double_stone_slab2", fn() => Blocks::RED_SANDSTONE_SLAB()->setSlabType(SlabType::DOUBLE()));
		$result->registerBlock("double_stone_slab3", fn() => Blocks::END_STONE_BRICK_SLAB()->setSlabType(SlabType::DOUBLE()));
		$result->registerBlock("double_stone_slab4", fn() => Blocks::MOSSY_STONE_BRICK_SLAB()->setSlabType(SlabType::DOUBLE()));
		$result->registerBlock("double_wood_slab", fn() => Blocks::OAK_SLAB()->setSlabType(SlabType::DOUBLE()));
		$result->registerBlock("double_wood_slabs", fn() => Blocks::OAK_SLAB()->setSlabType(SlabType::DOUBLE()));
		$result->registerBlock("double_wooden_slab", fn() => Blocks::OAK_SLAB()->setSlabType(SlabType::DOUBLE()));
		$result->registerBlock("double_wooden_slabs", fn() => Blocks::OAK_SLAB()->setSlabType(SlabType::DOUBLE()));
		$result->registerBlock("dragon_head", fn() => Blocks::MOB_HEAD()->setMobHeadType(MobHeadType::DRAGON()));
		$result->registerBlock("dried_kelp_block", fn() => Blocks::DRIED_KELP());
		$result->registerBlock("element_0", fn() => Blocks::ELEMENT_ZERO());
		$result->registerBlock("element_1", fn() => Blocks::ELEMENT_HYDROGEN());
		$result->registerBlock("element_10", fn() => Blocks::ELEMENT_NEON());
		$result->registerBlock("element_100", fn() => Blocks::ELEMENT_FERMIUM());
		$result->registerBlock("element_101", fn() => Blocks::ELEMENT_MENDELEVIUM());
		$result->registerBlock("element_102", fn() => Blocks::ELEMENT_NOBELIUM());
		$result->registerBlock("element_103", fn() => Blocks::ELEMENT_LAWRENCIUM());
		$result->registerBlock("element_104", fn() => Blocks::ELEMENT_RUTHERFORDIUM());
		$result->registerBlock("element_105", fn() => Blocks::ELEMENT_DUBNIUM());
		$result->registerBlock("element_106", fn() => Blocks::ELEMENT_SEABORGIUM());
		$result->registerBlock("element_107", fn() => Blocks::ELEMENT_BOHRIUM());
		$result->registerBlock("element_108", fn() => Blocks::ELEMENT_HASSIUM());
		$result->registerBlock("element_109", fn() => Blocks::ELEMENT_MEITNERIUM());
		$result->registerBlock("element_11", fn() => Blocks::ELEMENT_SODIUM());
		$result->registerBlock("element_110", fn() => Blocks::ELEMENT_DARMSTADTIUM());
		$result->registerBlock("element_111", fn() => Blocks::ELEMENT_ROENTGENIUM());
		$result->registerBlock("element_112", fn() => Blocks::ELEMENT_COPERNICIUM());
		$result->registerBlock("element_113", fn() => Blocks::ELEMENT_NIHONIUM());
		$result->registerBlock("element_114", fn() => Blocks::ELEMENT_FLEROVIUM());
		$result->registerBlock("element_115", fn() => Blocks::ELEMENT_MOSCOVIUM());
		$result->registerBlock("element_116", fn() => Blocks::ELEMENT_LIVERMORIUM());
		$result->registerBlock("element_117", fn() => Blocks::ELEMENT_TENNESSINE());
		$result->registerBlock("element_118", fn() => Blocks::ELEMENT_OGANESSON());
		$result->registerBlock("element_12", fn() => Blocks::ELEMENT_MAGNESIUM());
		$result->registerBlock("element_13", fn() => Blocks::ELEMENT_ALUMINUM());
		$result->registerBlock("element_14", fn() => Blocks::ELEMENT_SILICON());
		$result->registerBlock("element_15", fn() => Blocks::ELEMENT_PHOSPHORUS());
		$result->registerBlock("element_16", fn() => Blocks::ELEMENT_SULFUR());
		$result->registerBlock("element_17", fn() => Blocks::ELEMENT_CHLORINE());
		$result->registerBlock("element_18", fn() => Blocks::ELEMENT_ARGON());
		$result->registerBlock("element_19", fn() => Blocks::ELEMENT_POTASSIUM());
		$result->registerBlock("element_2", fn() => Blocks::ELEMENT_HELIUM());
		$result->registerBlock("element_20", fn() => Blocks::ELEMENT_CALCIUM());
		$result->registerBlock("element_21", fn() => Blocks::ELEMENT_SCANDIUM());
		$result->registerBlock("element_22", fn() => Blocks::ELEMENT_TITANIUM());
		$result->registerBlock("element_23", fn() => Blocks::ELEMENT_VANADIUM());
		$result->registerBlock("element_24", fn() => Blocks::ELEMENT_CHROMIUM());
		$result->registerBlock("element_25", fn() => Blocks::ELEMENT_MANGANESE());
		$result->registerBlock("element_26", fn() => Blocks::ELEMENT_IRON());
		$result->registerBlock("element_27", fn() => Blocks::ELEMENT_COBALT());
		$result->registerBlock("element_28", fn() => Blocks::ELEMENT_NICKEL());
		$result->registerBlock("element_29", fn() => Blocks::ELEMENT_COPPER());
		$result->registerBlock("element_3", fn() => Blocks::ELEMENT_LITHIUM());
		$result->registerBlock("element_30", fn() => Blocks::ELEMENT_ZINC());
		$result->registerBlock("element_31", fn() => Blocks::ELEMENT_GALLIUM());
		$result->registerBlock("element_32", fn() => Blocks::ELEMENT_GERMANIUM());
		$result->registerBlock("element_33", fn() => Blocks::ELEMENT_ARSENIC());
		$result->registerBlock("element_34", fn() => Blocks::ELEMENT_SELENIUM());
		$result->registerBlock("element_35", fn() => Blocks::ELEMENT_BROMINE());
		$result->registerBlock("element_36", fn() => Blocks::ELEMENT_KRYPTON());
		$result->registerBlock("element_37", fn() => Blocks::ELEMENT_RUBIDIUM());
		$result->registerBlock("element_38", fn() => Blocks::ELEMENT_STRONTIUM());
		$result->registerBlock("element_39", fn() => Blocks::ELEMENT_YTTRIUM());
		$result->registerBlock("element_4", fn() => Blocks::ELEMENT_BERYLLIUM());
		$result->registerBlock("element_40", fn() => Blocks::ELEMENT_ZIRCONIUM());
		$result->registerBlock("element_41", fn() => Blocks::ELEMENT_NIOBIUM());
		$result->registerBlock("element_42", fn() => Blocks::ELEMENT_MOLYBDENUM());
		$result->registerBlock("element_43", fn() => Blocks::ELEMENT_TECHNETIUM());
		$result->registerBlock("element_44", fn() => Blocks::ELEMENT_RUTHENIUM());
		$result->registerBlock("element_45", fn() => Blocks::ELEMENT_RHODIUM());
		$result->registerBlock("element_46", fn() => Blocks::ELEMENT_PALLADIUM());
		$result->registerBlock("element_47", fn() => Blocks::ELEMENT_SILVER());
		$result->registerBlock("element_48", fn() => Blocks::ELEMENT_CADMIUM());
		$result->registerBlock("element_49", fn() => Blocks::ELEMENT_INDIUM());
		$result->registerBlock("element_5", fn() => Blocks::ELEMENT_BORON());
		$result->registerBlock("element_50", fn() => Blocks::ELEMENT_TIN());
		$result->registerBlock("element_51", fn() => Blocks::ELEMENT_ANTIMONY());
		$result->registerBlock("element_52", fn() => Blocks::ELEMENT_TELLURIUM());
		$result->registerBlock("element_53", fn() => Blocks::ELEMENT_IODINE());
		$result->registerBlock("element_54", fn() => Blocks::ELEMENT_XENON());
		$result->registerBlock("element_55", fn() => Blocks::ELEMENT_CESIUM());
		$result->registerBlock("element_56", fn() => Blocks::ELEMENT_BARIUM());
		$result->registerBlock("element_57", fn() => Blocks::ELEMENT_LANTHANUM());
		$result->registerBlock("element_58", fn() => Blocks::ELEMENT_CERIUM());
		$result->registerBlock("element_59", fn() => Blocks::ELEMENT_PRASEODYMIUM());
		$result->registerBlock("element_6", fn() => Blocks::ELEMENT_CARBON());
		$result->registerBlock("element_60", fn() => Blocks::ELEMENT_NEODYMIUM());
		$result->registerBlock("element_61", fn() => Blocks::ELEMENT_PROMETHIUM());
		$result->registerBlock("element_62", fn() => Blocks::ELEMENT_SAMARIUM());
		$result->registerBlock("element_63", fn() => Blocks::ELEMENT_EUROPIUM());
		$result->registerBlock("element_64", fn() => Blocks::ELEMENT_GADOLINIUM());
		$result->registerBlock("element_65", fn() => Blocks::ELEMENT_TERBIUM());
		$result->registerBlock("element_66", fn() => Blocks::ELEMENT_DYSPROSIUM());
		$result->registerBlock("element_67", fn() => Blocks::ELEMENT_HOLMIUM());
		$result->registerBlock("element_68", fn() => Blocks::ELEMENT_ERBIUM());
		$result->registerBlock("element_69", fn() => Blocks::ELEMENT_THULIUM());
		$result->registerBlock("element_7", fn() => Blocks::ELEMENT_NITROGEN());
		$result->registerBlock("element_70", fn() => Blocks::ELEMENT_YTTERBIUM());
		$result->registerBlock("element_71", fn() => Blocks::ELEMENT_LUTETIUM());
		$result->registerBlock("element_72", fn() => Blocks::ELEMENT_HAFNIUM());
		$result->registerBlock("element_73", fn() => Blocks::ELEMENT_TANTALUM());
		$result->registerBlock("element_74", fn() => Blocks::ELEMENT_TUNGSTEN());
		$result->registerBlock("element_75", fn() => Blocks::ELEMENT_RHENIUM());
		$result->registerBlock("element_76", fn() => Blocks::ELEMENT_OSMIUM());
		$result->registerBlock("element_77", fn() => Blocks::ELEMENT_IRIDIUM());
		$result->registerBlock("element_78", fn() => Blocks::ELEMENT_PLATINUM());
		$result->registerBlock("element_79", fn() => Blocks::ELEMENT_GOLD());
		$result->registerBlock("element_8", fn() => Blocks::ELEMENT_OXYGEN());
		$result->registerBlock("element_80", fn() => Blocks::ELEMENT_MERCURY());
		$result->registerBlock("element_81", fn() => Blocks::ELEMENT_THALLIUM());
		$result->registerBlock("element_82", fn() => Blocks::ELEMENT_LEAD());
		$result->registerBlock("element_83", fn() => Blocks::ELEMENT_BISMUTH());
		$result->registerBlock("element_84", fn() => Blocks::ELEMENT_POLONIUM());
		$result->registerBlock("element_85", fn() => Blocks::ELEMENT_ASTATINE());
		$result->registerBlock("element_86", fn() => Blocks::ELEMENT_RADON());
		$result->registerBlock("element_87", fn() => Blocks::ELEMENT_FRANCIUM());
		$result->registerBlock("element_88", fn() => Blocks::ELEMENT_RADIUM());
		$result->registerBlock("element_89", fn() => Blocks::ELEMENT_ACTINIUM());
		$result->registerBlock("element_9", fn() => Blocks::ELEMENT_FLUORINE());
		$result->registerBlock("element_90", fn() => Blocks::ELEMENT_THORIUM());
		$result->registerBlock("element_91", fn() => Blocks::ELEMENT_PROTACTINIUM());
		$result->registerBlock("element_92", fn() => Blocks::ELEMENT_URANIUM());
		$result->registerBlock("element_93", fn() => Blocks::ELEMENT_NEPTUNIUM());
		$result->registerBlock("element_94", fn() => Blocks::ELEMENT_PLUTONIUM());
		$result->registerBlock("element_95", fn() => Blocks::ELEMENT_AMERICIUM());
		$result->registerBlock("element_96", fn() => Blocks::ELEMENT_CURIUM());
		$result->registerBlock("element_97", fn() => Blocks::ELEMENT_BERKELIUM());
		$result->registerBlock("element_98", fn() => Blocks::ELEMENT_CALIFORNIUM());
		$result->registerBlock("element_99", fn() => Blocks::ELEMENT_EINSTEINIUM());
		$result->registerBlock("emerald_block", fn() => Blocks::EMERALD());
		$result->registerBlock("enchant_table", fn() => Blocks::ENCHANTING_TABLE());
		$result->registerBlock("enchantment_table", fn() => Blocks::ENCHANTING_TABLE());
		$result->registerBlock("end_brick_stairs", fn() => Blocks::END_STONE_BRICK_STAIRS());
		$result->registerBlock("end_bricks", fn() => Blocks::END_STONE_BRICKS());
		$result->registerBlock("fence", fn() => Blocks::OAK_FENCE());
		$result->registerBlock("fence_gate", fn() => Blocks::OAK_FENCE_GATE());
		$result->registerBlock("fence_gate_acacia", fn() => Blocks::ACACIA_FENCE_GATE());
		$result->registerBlock("fence_gate_birch", fn() => Blocks::BIRCH_FENCE_GATE());
		$result->registerBlock("fence_gate_dark_oak", fn() => Blocks::DARK_OAK_FENCE_GATE());
		$result->registerBlock("fence_gate_jungle", fn() => Blocks::JUNGLE_FENCE_GATE());
		$result->registerBlock("fence_gate_spruce", fn() => Blocks::SPRUCE_FENCE_GATE());
		$result->registerBlock("flower_pot_block", fn() => Blocks::FLOWER_POT());
		$result->registerBlock("flowing_lava", fn() => Blocks::LAVA());
		$result->registerBlock("flowing_water", fn() => Blocks::WATER());
		$result->registerBlock("frame", fn() => Blocks::ITEM_FRAME());
		$result->registerBlock("frame_block", fn() => Blocks::ITEM_FRAME());
		$result->registerBlock("glass_panel", fn() => Blocks::GLASS_PANE());
		$result->registerBlock("glow_frame", fn() => Blocks::GLOWING_ITEM_FRAME());
		$result->registerBlock("glow_item_frame", fn() => Blocks::GLOWING_ITEM_FRAME());
		$result->registerBlock("glowing_redstone_ore", fn() => Blocks::REDSTONE_ORE()->setLit(true));
		$result->registerBlock("glowingobsidian", fn() => Blocks::GLOWING_OBSIDIAN());
		$result->registerBlock("glowstone_block", fn() => Blocks::GLOWSTONE());
		$result->registerBlock("gold_block", fn() => Blocks::GOLD());
		$result->registerBlock("gold_pressure_plate", fn() => Blocks::WEIGHTED_PRESSURE_PLATE_LIGHT());
		$result->registerBlock("golden_rail", fn() => Blocks::POWERED_RAIL());
		$result->registerBlock("hard_glass", fn() => Blocks::HARDENED_GLASS());
		$result->registerBlock("hard_glass_pane", fn() => Blocks::HARDENED_GLASS_PANE());
		$result->registerBlock("hard_stained_glass", fn() => Blocks::STAINED_HARDENED_GLASS());
		$result->registerBlock("hard_stained_glass_pane", fn() => Blocks::STAINED_HARDENED_GLASS_PANE());
		$result->registerBlock("hay_block", fn() => Blocks::HAY_BALE());
		$result->registerBlock("heavy_weighted_pressure_plate", fn() => Blocks::WEIGHTED_PRESSURE_PLATE_HEAVY());
		$result->registerBlock("honeycomb_block", fn() => Blocks::HONEYCOMB());
		$result->registerBlock("hopper_block", fn() => Blocks::HOPPER());
		$result->registerBlock("inactive_redstone_lamp", fn() => Blocks::REDSTONE_LAMP());
		$result->registerBlock("info_reserved6", fn() => Blocks::RESERVED6());
		$result->registerBlock("inverted_daylight_sensor", fn() => Blocks::DAYLIGHT_SENSOR()->setInverted(true));
		$result->registerBlock("invisiblebedrock", fn() => Blocks::INVISIBLE_BEDROCK());
		$result->registerBlock("iron_bar", fn() => Blocks::IRON_BARS());
		$result->registerBlock("iron_block", fn() => Blocks::IRON());
		$result->registerBlock("iron_door_block", fn() => Blocks::IRON_DOOR());
		$result->registerBlock("iron_pressure_plate", fn() => Blocks::WEIGHTED_PRESSURE_PLATE_HEAVY());
		$result->registerBlock("item_frame_block", fn() => Blocks::ITEM_FRAME());
		$result->registerBlock("jack_o_lantern", fn() => Blocks::LIT_PUMPKIN());
		$result->registerBlock("jungle_door_block", fn() => Blocks::JUNGLE_DOOR());
		$result->registerBlock("jungle_standing_sign", fn() => Blocks::JUNGLE_SIGN());
		$result->registerBlock("jungle_wood_stairs", fn() => Blocks::JUNGLE_STAIRS());
		$result->registerBlock("jungle_wooden_stairs", fn() => Blocks::JUNGLE_STAIRS());
		$result->registerBlock("lapis_block", fn() => Blocks::LAPIS_LAZULI());
		$result->registerBlock("lapis_lazuli_block", fn() => Blocks::LAPIS_LAZULI());
		$result->registerBlock("lapis_ore", fn() => Blocks::LAPIS_LAZULI_ORE());
		$result->registerBlock("leave", fn() => Blocks::OAK_LEAVES());
		$result->registerBlock("leave2", fn() => Blocks::ACACIA_LEAVES());
		$result->registerBlock("leaves", fn() => Blocks::OAK_LEAVES());
		$result->registerBlock("leaves2", fn() => Blocks::ACACIA_LEAVES());
		$result->registerBlock("light_block", fn() => Blocks::LIGHT());
		$result->registerBlock("light_weighted_pressure_plate", fn() => Blocks::WEIGHTED_PRESSURE_PLATE_LIGHT());
		$result->registerBlock("lit_blast_furnace", fn() => Blocks::BLAST_FURNACE());
		$result->registerBlock("lit_furnace", fn() => Blocks::FURNACE());
		$result->registerBlock("lit_redstone_lamp", fn() => Blocks::REDSTONE_LAMP()->setPowered(true));
		$result->registerBlock("lit_redstone_ore", fn() => Blocks::REDSTONE_ORE()->setLit(true));
		$result->registerBlock("lit_redstone_torch", fn() => Blocks::REDSTONE_TORCH());
		$result->registerBlock("lit_smoker", fn() => Blocks::SMOKER());
		$result->registerBlock("log", fn() => Blocks::OAK_LOG()->setStripped(false));
		$result->registerBlock("log2", fn() => Blocks::ACACIA_LOG()->setStripped(false));
		$result->registerBlock("melon_block", fn() => Blocks::MELON());
		$result->registerBlock("mob_head_block", fn() => Blocks::MOB_HEAD());
		$result->registerBlock("mob_spawner", fn() => Blocks::MONSTER_SPAWNER());
		$result->registerBlock("monster_egg", fn() => Blocks::INFESTED_STONE());
		$result->registerBlock("monster_egg_block", fn() => Blocks::INFESTED_STONE());
		$result->registerBlock("moss_stone", fn() => Blocks::MOSSY_COBBLESTONE());
		$result->registerBlock("mossy_stone", fn() => Blocks::MOSSY_COBBLESTONE());
		$result->registerBlock("nether_brick_block", fn() => Blocks::NETHER_BRICKS());
		$result->registerBlock("nether_bricks_stairs", fn() => Blocks::NETHER_BRICK_STAIRS());
		$result->registerBlock("nether_reactor", fn() => Blocks::NETHER_REACTOR_CORE());
		$result->registerBlock("nether_wart_plant", fn() => Blocks::NETHER_WART());
		$result->registerBlock("netherite_block", fn() => Blocks::NETHERITE());
		$result->registerBlock("netherreactor", fn() => Blocks::NETHER_REACTOR_CORE());
		$result->registerBlock("normal_stone_stairs", fn() => Blocks::STONE_STAIRS());
		$result->registerBlock("noteblock", fn() => Blocks::NOTE_BLOCK());
		$result->registerBlock("oak_door_block", fn() => Blocks::OAK_DOOR());
		$result->registerBlock("oak_standing_sign", fn() => Blocks::OAK_SIGN());
		$result->registerBlock("oak_wood_stairs", fn() => Blocks::OAK_STAIRS());
		$result->registerBlock("oak_wooden_stairs", fn() => Blocks::OAK_STAIRS());
		$result->registerBlock("piglin_head", fn() => Blocks::MOB_HEAD()->setMobHeadType(MobHeadType::PIGLIN()));
		$result->registerBlock("plank", fn() => Blocks::OAK_PLANKS());
		$result->registerBlock("planks", fn() => Blocks::OAK_PLANKS());
		$result->registerBlock("player_head", fn() => Blocks::MOB_HEAD()->setMobHeadType(MobHeadType::PLAYER()));
		$result->registerBlock("portal", fn() => Blocks::NETHER_PORTAL());
		$result->registerBlock("portal_block", fn() => Blocks::NETHER_PORTAL());
		$result->registerBlock("potato_block", fn() => Blocks::POTATOES());
		$result->registerBlock("powered_comparator", fn() => Blocks::REDSTONE_COMPARATOR());
		$result->registerBlock("powered_comparator_block", fn() => Blocks::REDSTONE_COMPARATOR());
		$result->registerBlock("powered_repeater", fn() => Blocks::REDSTONE_REPEATER()->setPowered(true));
		$result->registerBlock("powered_repeater_block", fn() => Blocks::REDSTONE_REPEATER()->setPowered(true));
		$result->registerBlock("purpur_block", fn() => Blocks::PURPUR());
		$result->registerBlock("quartz_block", fn() => Blocks::QUARTZ());
		$result->registerBlock("quartz_ore", fn() => Blocks::NETHER_QUARTZ_ORE());
		$result->registerBlock("raw_copper_block", fn() => Blocks::RAW_COPPER());
		$result->registerBlock("raw_gold_block", fn() => Blocks::RAW_GOLD());
		$result->registerBlock("raw_iron_block", fn() => Blocks::RAW_IRON());
		$result->registerBlock("red_flower", fn() => Blocks::POPPY());
		$result->registerBlock("red_nether_brick", fn() => Blocks::RED_NETHER_BRICKS());
		$result->registerBlock("redstone_block", fn() => Blocks::REDSTONE());
		$result->registerBlock("reeds", fn() => Blocks::SUGARCANE());
		$result->registerBlock("reeds_block", fn() => Blocks::SUGARCANE());
		$result->registerBlock("repeater", fn() => Blocks::REDSTONE_REPEATER());
		$result->registerBlock("repeater_block", fn() => Blocks::REDSTONE_REPEATER());
		$result->registerBlock("rooted_dirt", fn() => Blocks::DIRT()->setDirtType(DirtType::ROOTED()));
		$result->registerBlock("rose", fn() => Blocks::POPPY());
		$result->registerBlock("sapling", fn() => Blocks::OAK_SAPLING());
		$result->registerBlock("sealantern", fn() => Blocks::SEA_LANTERN());
		$result->registerBlock("sign", fn() => Blocks::OAK_SIGN());
		$result->registerBlock("sign_post", fn() => Blocks::OAK_SIGN());
		$result->registerBlock("skeleton_skull", fn() => Blocks::MOB_HEAD()->setMobHeadType(MobHeadType::SKELETON()));
		$result->registerBlock("skull", fn() => Blocks::MOB_HEAD()->setMobHeadType(MobHeadType::SKELETON()));
		$result->registerBlock("skull_block", fn() => Blocks::MOB_HEAD());
		$result->registerBlock("slab", fn() => Blocks::SMOOTH_STONE_SLAB());
		$result->registerBlock("slabs", fn() => Blocks::SMOOTH_STONE_SLAB());
		$result->registerBlock("slime_block", fn() => Blocks::SLIME());
		$result->registerBlock("snow_block", fn() => Blocks::SNOW());
		$result->registerBlock("spruce_door_block", fn() => Blocks::SPRUCE_DOOR());
		$result->registerBlock("spruce_standing_sign", fn() => Blocks::SPRUCE_SIGN());
		$result->registerBlock("spruce_wood_stairs", fn() => Blocks::SPRUCE_STAIRS());
		$result->registerBlock("spruce_wooden_stairs", fn() => Blocks::SPRUCE_STAIRS());
		$result->registerBlock("stained_hardened_clay", fn() => Blocks::STAINED_CLAY());
		$result->registerBlock("standing_banner", fn() => Blocks::BANNER());
		$result->registerBlock("standing_sign", fn() => Blocks::OAK_SIGN());
		$result->registerBlock("still_lava", fn() => Blocks::LAVA()->setStill(true));
		$result->registerBlock("still_water", fn() => Blocks::WATER()->setStill(true));
		$result->registerBlock("stone_brick", fn() => Blocks::STONE_BRICKS());
		$result->registerBlock("stone_slab2", fn() => Blocks::RED_SANDSTONE_SLAB());
		$result->registerBlock("stone_slab3", fn() => Blocks::END_STONE_BRICK_SLAB());
		$result->registerBlock("stone_slab4", fn() => Blocks::MOSSY_STONE_BRICK_SLAB());
		$result->registerBlock("stone_wall", fn() => Blocks::COBBLESTONE_WALL());
		$result->registerBlock("stonebrick", fn() => Blocks::STONE_BRICKS());
		$result->registerBlock("stonecutter_block", fn() => Blocks::STONECUTTER());
		$result->registerBlock("stripped_acacia_log", fn() => Blocks::ACACIA_LOG()->setStripped(true));
		$result->registerBlock("stripped_acacia_wood", fn() => Blocks::ACACIA_WOOD()->setStripped(true));
		$result->registerBlock("stripped_birch_log", fn() => Blocks::BIRCH_LOG()->setStripped(true));
		$result->registerBlock("stripped_birch_wood", fn() => Blocks::BIRCH_WOOD()->setStripped(true));
		$result->registerBlock("stripped_crimson_hyphae", fn() => Blocks::CRIMSON_HYPHAE()->setStripped(true));
		$result->registerBlock("stripped_crimson_stem", fn() => Blocks::CRIMSON_STEM()->setStripped(true));
		$result->registerBlock("stripped_dark_oak_log", fn() => Blocks::DARK_OAK_LOG()->setStripped(true));
		$result->registerBlock("stripped_dark_oak_wood", fn() => Blocks::DARK_OAK_WOOD()->setStripped(true));
		$result->registerBlock("stripped_jungle_log", fn() => Blocks::JUNGLE_LOG()->setStripped(true));
		$result->registerBlock("stripped_jungle_wood", fn() => Blocks::JUNGLE_WOOD()->setStripped(true));
		$result->registerBlock("stripped_mangrove_log", fn() => Blocks::MANGROVE_LOG()->setStripped(true));
		$result->registerBlock("stripped_mangrove_wood", fn() => Blocks::MANGROVE_WOOD()->setStripped(true));
		$result->registerBlock("stripped_oak_log", fn() => Blocks::OAK_LOG()->setStripped(true));
		$result->registerBlock("stripped_oak_wood", fn() => Blocks::OAK_WOOD()->setStripped(true));
		$result->registerBlock("stripped_spruce_log", fn() => Blocks::SPRUCE_LOG()->setStripped(true));
		$result->registerBlock("stripped_spruce_wood", fn() => Blocks::SPRUCE_WOOD()->setStripped(true));
		$result->registerBlock("stripped_warped_hyphae", fn() => Blocks::WARPED_HYPHAE()->setStripped(true));
		$result->registerBlock("stripped_warped_stem", fn() => Blocks::WARPED_STEM()->setStripped(true));
		$result->registerBlock("sugar_cane", fn() => Blocks::SUGARCANE());
		$result->registerBlock("sugar_canes", fn() => Blocks::SUGARCANE());
		$result->registerBlock("sugarcane_block", fn() => Blocks::SUGARCANE());
		$result->registerBlock("tallgrass", fn() => Blocks::FERN());
		$result->registerBlock("terracotta", fn() => Blocks::STAINED_CLAY());
		$result->registerBlock("trapdoor", fn() => Blocks::OAK_TRAPDOOR());
		$result->registerBlock("trip_wire", fn() => Blocks::TRIPWIRE());
		$result->registerBlock("trunk", fn() => Blocks::OAK_PLANKS());
		$result->registerBlock("trunk2", fn() => Blocks::ACACIA_LOG()->setStripped(false));
		$result->registerBlock("underwater_tnt", fn() => Blocks::TNT()->setWorksUnderwater(true));
		$result->registerBlock("undyed_shulker_box", fn() => Blocks::SHULKER_BOX());
		$result->registerBlock("unlit_redstone_torch", fn() => Blocks::REDSTONE_TORCH());
		$result->registerBlock("unpowered_comparator", fn() => Blocks::REDSTONE_COMPARATOR());
		$result->registerBlock("unpowered_comparator_block", fn() => Blocks::REDSTONE_COMPARATOR());
		$result->registerBlock("unpowered_repeater", fn() => Blocks::REDSTONE_REPEATER());
		$result->registerBlock("unpowered_repeater_block", fn() => Blocks::REDSTONE_REPEATER());
		$result->registerBlock("update_block", fn() => Blocks::INFO_UPDATE());
		$result->registerBlock("vine", fn() => Blocks::VINES());
		$result->registerBlock("wall_sign", fn() => Blocks::OAK_WALL_SIGN());
		$result->registerBlock("water_lily", fn() => Blocks::LILY_PAD());
		$result->registerBlock("waterlily", fn() => Blocks::LILY_PAD());
		$result->registerBlock("web", fn() => Blocks::COBWEB());
		$result->registerBlock("wheat_block", fn() => Blocks::WHEAT());
		$result->registerBlock("wither_skeleton_skull", fn() => Blocks::MOB_HEAD()->setMobHeadType(MobHeadType::WITHER_SKELETON()));
		$result->registerBlock("wood", fn() => Blocks::OAK_LOG()->setStripped(false));
		$result->registerBlock("wood2", fn() => Blocks::ACACIA_LOG()->setStripped(false));
		$result->registerBlock("wood_door_block", fn() => Blocks::OAK_DOOR());
		$result->registerBlock("wood_slab", fn() => Blocks::OAK_SLAB());
		$result->registerBlock("wood_slabs", fn() => Blocks::OAK_SLAB());
		$result->registerBlock("wood_stairs", fn() => Blocks::OAK_STAIRS());
		$result->registerBlock("wooden_button", fn() => Blocks::OAK_BUTTON());
		$result->registerBlock("wooden_door", fn() => Blocks::OAK_DOOR());
		$result->registerBlock("wooden_door_block", fn() => Blocks::OAK_DOOR());
		$result->registerBlock("wooden_plank", fn() => Blocks::OAK_PLANKS());
		$result->registerBlock("wooden_planks", fn() => Blocks::OAK_PLANKS());
		$result->registerBlock("wooden_pressure_plate", fn() => Blocks::OAK_PRESSURE_PLATE());
		$result->registerBlock("wooden_slab", fn() => Blocks::OAK_SLAB());
		$result->registerBlock("wooden_slabs", fn() => Blocks::OAK_SLAB());
		$result->registerBlock("wooden_stairs", fn() => Blocks::OAK_STAIRS());
		$result->registerBlock("wooden_trapdoor", fn() => Blocks::OAK_TRAPDOOR());
		$result->registerBlock("workbench", fn() => Blocks::CRAFTING_TABLE());
		$result->registerBlock("yellow_flower", fn() => Blocks::DANDELION());
		$result->registerBlock("zombie_head", fn() => Blocks::MOB_HEAD()->setMobHeadType(MobHeadType::ZOMBIE()));
	}

	private static function registerDynamicItems(self $result): void
	{
		foreach (DyeColor::getAll() as $color) {
			$prefix = fn(string $name) => $color->name() . "_" . $name;

			$result->register($prefix("dye"), fn() => Items::DYE()->setColor($color));
		}
		foreach (SuspiciousStewType::getAll() as $suspiciousStewType) {
			$prefix = fn(string $name) => $suspiciousStewType->name() . "_" . $name;

			$result->register($prefix("suspicious_stew"), fn() => Items::SUSPICIOUS_STEW()->setType($suspiciousStewType));
		}
	}

	private static function registerItems(self $result): void
	{
		foreach (array_keys(Items::getAll()) as $k) {
			$result->register(strtolower($k), fn() => Items::__callStatic($k, []));
		}
		$result->register("antidote", fn() => Items::MEDICINE()->setType(MedicineType::ANTIDOTE()));
		$result->register("apple_enchanted", fn() => Items::ENCHANTED_GOLDEN_APPLE());
		$result->register("appleenchanted", fn() => Items::ENCHANTED_GOLDEN_APPLE());
		$result->register("awkward_potion", fn() => Items::POTION()->setType(PotionType::AWKWARD()));
		$result->register("awkward_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::AWKWARD()));
		$result->register("baked_potatoes", fn() => Items::BAKED_POTATO());
		$result->register("beef", fn() => Items::RAW_BEEF());
		$result->register("beetroot_seed", fn() => Items::BEETROOT_SEEDS());
		$result->register("boat", fn() => Items::OAK_BOAT());
		$result->register("bottle_o_enchanting", fn() => Items::EXPERIENCE_BOTTLE());
		$result->register("chain_boots", fn() => Items::CHAINMAIL_BOOTS());
		$result->register("chain_chestplate", fn() => Items::CHAINMAIL_CHESTPLATE());
		$result->register("chain_helmet", fn() => Items::CHAINMAIL_HELMET());
		$result->register("chain_leggings", fn() => Items::CHAINMAIL_LEGGINGS());
		$result->register("chicken", fn() => Items::RAW_CHICKEN());
		$result->register("chorus_fruit_popped", fn() => Items::POPPED_CHORUS_FRUIT());
		$result->register("clay_ball", fn() => Items::CLAY());
		$result->register("clown_fish", fn() => Items::CLOWNFISH());
		$result->register("cod", fn() => Items::RAW_FISH());
		$result->register("compound", fn() => Items::CHEMICAL_SALT());
		$result->register("cooked_beef", fn() => Items::STEAK());
		$result->register("cooked_cod", fn() => Items::COOKED_FISH());
		$result->register("dye", fn() => Items::INK_SAC());
		$result->register("elixir", fn() => Items::MEDICINE()->setType(MedicineType::ELIXIR()));
		$result->register("enchanting_bottle", fn() => Items::EXPERIENCE_BOTTLE());
		$result->register("eye_drops", fn() => Items::MEDICINE()->setType(MedicineType::EYE_DROPS()));
		$result->register("fire_resistance_potion", fn() => Items::POTION()->setType(PotionType::FIRE_RESISTANCE()));
		$result->register("fire_resistance_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::FIRE_RESISTANCE()));
		$result->register("fish", fn() => Items::RAW_FISH());
		$result->register("flint_steel", fn() => Items::FLINT_AND_STEEL());
		$result->register("gold_axe", fn() => Items::GOLDEN_AXE());
		$result->register("gold_boots", fn() => Items::GOLDEN_BOOTS());
		$result->register("gold_chestplate", fn() => Items::GOLDEN_CHESTPLATE());
		$result->register("gold_helmet", fn() => Items::GOLDEN_HELMET());
		$result->register("gold_hoe", fn() => Items::GOLDEN_HOE());
		$result->register("gold_leggings", fn() => Items::GOLDEN_LEGGINGS());
		$result->register("gold_pickaxe", fn() => Items::GOLDEN_PICKAXE());
		$result->register("gold_shovel", fn() => Items::GOLDEN_SHOVEL());
		$result->register("gold_sword", fn() => Items::GOLDEN_SWORD());
		$result->register("golden_nugget", fn() => Items::GOLD_NUGGET());
		$result->register("harming_potion", fn() => Items::POTION()->setType(PotionType::HARMING()));
		$result->register("harming_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::HARMING()));
		$result->register("healing_potion", fn() => Items::POTION()->setType(PotionType::HEALING()));
		$result->register("healing_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::HEALING()));
		$result->register("invisibility_potion", fn() => Items::POTION()->setType(PotionType::INVISIBILITY()));
		$result->register("invisibility_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::INVISIBILITY()));
		$result->register("leaping_potion", fn() => Items::POTION()->setType(PotionType::LEAPING()));
		$result->register("leaping_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::LEAPING()));
		$result->register("leather_chestplate", fn() => Items::LEATHER_TUNIC());
		$result->register("leather_helmet", fn() => Items::LEATHER_CAP());
		$result->register("leather_leggings", fn() => Items::LEATHER_PANTS());
		$result->register("long_fire_resistance_potion", fn() => Items::POTION()->setType(PotionType::LONG_FIRE_RESISTANCE()));
		$result->register("long_fire_resistance_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::LONG_FIRE_RESISTANCE()));
		$result->register("long_invisibility_potion", fn() => Items::POTION()->setType(PotionType::LONG_INVISIBILITY()));
		$result->register("long_invisibility_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::LONG_INVISIBILITY()));
		$result->register("long_leaping_potion", fn() => Items::POTION()->setType(PotionType::LONG_LEAPING()));
		$result->register("long_leaping_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::LONG_LEAPING()));
		$result->register("long_mundane_potion", fn() => Items::POTION()->setType(PotionType::LONG_MUNDANE()));
		$result->register("long_mundane_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::LONG_MUNDANE()));
		$result->register("long_night_vision_potion", fn() => Items::POTION()->setType(PotionType::LONG_NIGHT_VISION()));
		$result->register("long_night_vision_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::LONG_NIGHT_VISION()));
		$result->register("long_poison_potion", fn() => Items::POTION()->setType(PotionType::LONG_POISON()));
		$result->register("long_poison_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::LONG_POISON()));
		$result->register("long_regeneration_potion", fn() => Items::POTION()->setType(PotionType::LONG_REGENERATION()));
		$result->register("long_regeneration_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::LONG_REGENERATION()));
		$result->register("long_slow_falling_potion", fn() => Items::POTION()->setType(PotionType::LONG_SLOW_FALLING()));
		$result->register("long_slow_falling_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::LONG_SLOW_FALLING()));
		$result->register("long_slowness_potion", fn() => Items::POTION()->setType(PotionType::LONG_SLOWNESS()));
		$result->register("long_slowness_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::LONG_SLOWNESS()));
		$result->register("long_strength_potion", fn() => Items::POTION()->setType(PotionType::LONG_STRENGTH()));
		$result->register("long_strength_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::LONG_STRENGTH()));
		$result->register("long_swiftness_potion", fn() => Items::POTION()->setType(PotionType::LONG_SWIFTNESS()));
		$result->register("long_swiftness_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::LONG_SWIFTNESS()));
		$result->register("long_turtle_master_potion", fn() => Items::POTION()->setType(PotionType::LONG_TURTLE_MASTER()));
		$result->register("long_turtle_master_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::LONG_TURTLE_MASTER()));
		$result->register("long_water_breathing_potion", fn() => Items::POTION()->setType(PotionType::LONG_WATER_BREATHING()));
		$result->register("long_water_breathing_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::LONG_WATER_BREATHING()));
		$result->register("long_weakness_potion", fn() => Items::POTION()->setType(PotionType::LONG_WEAKNESS()));
		$result->register("long_weakness_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::LONG_WEAKNESS()));
		$result->register("melon_slice", fn() => Items::MELON());
		$result->register("mundane_potion", fn() => Items::POTION()->setType(PotionType::MUNDANE()));
		$result->register("mundane_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::MUNDANE()));
		$result->register("mutton", fn() => Items::RAW_MUTTON());
		$result->register("mutton_cooked", fn() => Items::COOKED_MUTTON());
		$result->register("mutton_raw", fn() => Items::RAW_MUTTON());
		$result->register("muttoncooked", fn() => Items::COOKED_MUTTON());
		$result->register("muttonraw", fn() => Items::RAW_MUTTON());
		$result->register("netherbrick", fn() => Items::NETHER_BRICK());
		$result->register("netherstar", fn() => Items::NETHER_STAR());
		$result->register("night_vision_potion", fn() => Items::POTION()->setType(PotionType::NIGHT_VISION()));
		$result->register("night_vision_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::NIGHT_VISION()));
		$result->register("poison_potion", fn() => Items::POTION()->setType(PotionType::POISON()));
		$result->register("poison_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::POISON()));
		$result->register("porkchop", fn() => Items::RAW_PORKCHOP());
		$result->register("puffer_fish", fn() => Items::PUFFERFISH());
		$result->register("quartz", fn() => Items::NETHER_QUARTZ());
		$result->register("rabbit", fn() => Items::RAW_RABBIT());
		$result->register("redstone", fn() => Items::REDSTONE_DUST());
		$result->register("regeneration_potion", fn() => Items::POTION()->setType(PotionType::REGENERATION()));
		$result->register("regeneration_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::REGENERATION()));
		$result->register("salmon", fn() => Items::RAW_SALMON());
		$result->register("seeds", fn() => Items::WHEAT_SEEDS());
		$result->register("slime_ball", fn() => Items::SLIMEBALL());
		$result->register("slow_falling_potion", fn() => Items::POTION()->setType(PotionType::SLOW_FALLING()));
		$result->register("slow_falling_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::SLOW_FALLING()));
		$result->register("slowness_potion", fn() => Items::POTION()->setType(PotionType::SLOWNESS()));
		$result->register("slowness_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::SLOWNESS()));
		$result->register("speckled_melon", fn() => Items::GLISTERING_MELON());
		$result->register("sticks", fn() => Items::STICK());
		$result->register("strength_potion", fn() => Items::POTION()->setType(PotionType::STRENGTH()));
		$result->register("strength_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::STRENGTH()));
		$result->register("strong_harming_potion", fn() => Items::POTION()->setType(PotionType::STRONG_HARMING()));
		$result->register("strong_harming_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::STRONG_HARMING()));
		$result->register("strong_healing_potion", fn() => Items::POTION()->setType(PotionType::STRONG_HEALING()));
		$result->register("strong_healing_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::STRONG_HEALING()));
		$result->register("strong_leaping_potion", fn() => Items::POTION()->setType(PotionType::STRONG_LEAPING()));
		$result->register("strong_leaping_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::STRONG_LEAPING()));
		$result->register("strong_poison_potion", fn() => Items::POTION()->setType(PotionType::STRONG_POISON()));
		$result->register("strong_poison_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::STRONG_POISON()));
		$result->register("strong_regeneration_potion", fn() => Items::POTION()->setType(PotionType::STRONG_REGENERATION()));
		$result->register("strong_regeneration_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::STRONG_REGENERATION()));
		$result->register("strong_slowness_potion", fn() => Items::POTION()->setType(PotionType::STRONG_SLOWNESS()));
		$result->register("strong_slowness_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::STRONG_SLOWNESS()));
		$result->register("strong_strength_potion", fn() => Items::POTION()->setType(PotionType::STRONG_STRENGTH()));
		$result->register("strong_strength_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::STRONG_STRENGTH()));
		$result->register("strong_swiftness_potion", fn() => Items::POTION()->setType(PotionType::STRONG_SWIFTNESS()));
		$result->register("strong_swiftness_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::STRONG_SWIFTNESS()));
		$result->register("strong_turtle_master_potion", fn() => Items::POTION()->setType(PotionType::STRONG_TURTLE_MASTER()));
		$result->register("strong_turtle_master_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::STRONG_TURTLE_MASTER()));
		$result->register("swiftness_potion", fn() => Items::POTION()->setType(PotionType::SWIFTNESS()));
		$result->register("swiftness_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::SWIFTNESS()));
		$result->register("thick_potion", fn() => Items::POTION()->setType(PotionType::THICK()));
		$result->register("thick_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::THICK()));
		$result->register("tonic", fn() => Items::MEDICINE()->setType(MedicineType::TONIC()));
		$result->register("turtle_master_potion", fn() => Items::POTION()->setType(PotionType::TURTLE_MASTER()));
		$result->register("turtle_master_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::TURTLE_MASTER()));
		$result->register("turtle_shell_piece", fn() => Items::SCUTE());
		$result->register("water_breathing_potion", fn() => Items::POTION()->setType(PotionType::WATER_BREATHING()));
		$result->register("water_breathing_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::WATER_BREATHING()));
		$result->register("water_potion", fn() => Items::POTION()->setType(PotionType::WATER()));
		$result->register("water_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::WATER()));
		$result->register("weakness_potion", fn() => Items::POTION()->setType(PotionType::WEAKNESS()));
		$result->register("weakness_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::WEAKNESS()));
		$result->register("wither_potion", fn() => Items::POTION()->setType(PotionType::WITHER()));
		$result->register("wither_splash_potion", fn() => Items::SPLASH_POTION()->setType(PotionType::WITHER()));

		foreach (array_keys(VanillaItems::SPAWN_ITEMS) as $n)
			$result->register($n . "_spawn_egg", fn() => Items::__callStatic(strtoupper($n) . "_SPAWN_EGG", []));
	}

	/**
	 * @var true[][]
	 * @phpstan-var array<int, array<string, true>>
	 */
	private array $reverseMap = [];

	public function register(string $alias, \Closure $callback): void
	{
		parent::register($alias, $callback);
		$item = $callback($alias);
		$this->reverseMap[$item->getStateId()][$alias] = true;
	}

	/** @phpstan-param \Closure(string $input) : Block $callback */
	public function registerBlock(string $alias, \Closure $callback): void
	{
		$this->register($alias, fn(string $input) => $callback($input)->asItem());
	}

	public function parse(string $input): ?Item
	{
		return parent::parse($input);
	}

	/**
	 * Returns a list of currently registered aliases that resolve to the given item.
	 *
	 * @return string[]
	 * @phpstan-return list<string>
	 */
	public function lookupAliases(Item $item): array
	{
		$aliases = $this->reverseMap[$item->getStateId()] ?? [];
		return array_keys($aliases);
	}

	/**
	 * Returns a list of currently registered aliases that resolve to the item form of the given block.
	 *
	 * @return string[]
	 * @phpstan-return list<string>
	 */
	public function lookupBlockAliases(Block $block): array
	{
		return $this->lookupAliases($block->asItem());
	}
}
