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

namespace pocketmine\lang;

use pocketmine\utils\Utils;
use Symfony\Component\Filesystem\Path;
use function array_filter;
use function array_map;
use function count;
use function explode;
use function file_exists;
use function is_dir;
use function ord;
use function parse_ini_file;
use function scandir;
use function str_ends_with;
use function str_replace;
use function str_starts_with;
use function strlen;
use function strpos;
use function strtolower;
use function substr;
use const INI_SCANNER_RAW;
use const pocketmine\LOCALE_DATA_PATH;
use const SCANDIR_SORT_NONE;

class Language
{
	public const FALLBACK_LANGUAGE = "en_us";
	public const LANGUAGE_NAME = "language.name";
	public const LOCALES = [
		"en_us",
		"bg_bg",
		"cs_cz",
		"da_dk",
		"de_de",
		"el_gr",
		"en_gb",
		"fi_fi",
		"fr_ca",
		"fr_fr",
		"hu_hu",
		"id_id",
		"it_it",
		"ja_jp",
		"ko_kr",
		"nl_nl",
		"nb_no",
		"pl_pl",
		"pt_br",
		"pt_pt",
		"ru_ru",
		"sk_sk",
		"es_es",
		"es_mx",
		"sv_se",
		"tr_tr",
		"uk_ua",
		"zh_cn",
		"zh_tw"
	];

	/**
	 * @return string[]
	 * @phpstan-return array<string, string>
	 *
	 * @throws LanguageNotFoundException
	 */
	public static function getLanguageList(): array
	{
		$result = [];
		foreach (self::$langCache as $code => $strings)
			$result[$code] = $strings[self::LANGUAGE_NAME] ?? "Unknown";
		return $result;
	}

	protected string $langName;
	public static array $langCache = [];

	/**
	 * @throws LanguageNotFoundException
	 */
	public function __construct(string $lang)
	{
		self::loadLang($this->langName = strtolower($lang));
	}

	public static function init(): void
	{
		$path = LOCALE_DATA_PATH;
		if (is_dir($path)) {
			$allFiles = scandir($path, SCANDIR_SORT_NONE);
			if ($allFiles !== false) {
				$files = array_filter($allFiles, function (string $filename): bool {
					return str_ends_with($filename, ".ini");
				});
				foreach ($files as $file) self::loadLang(explode(".", $file)[0]);
				return;
			}
		}
		throw new LanguageNotFoundException("Language directory $path does not exist or is not a directory");
	}

	public function getName(): string
	{
		return $this->get(self::LANGUAGE_NAME);
	}

	public function getLang(): string
	{
		return $this->langName;
	}

	/**
	 * @return string[]
	 * @phpstan-return array<string, string>
	 */
	public static function loadLang(string $languageCode): array
	{
		if (isset(self::$langCache[$languageCode])) return self::$langCache[$languageCode];
		$file = Path::join(LOCALE_DATA_PATH, $languageCode . ".ini");
		if (file_exists($file)) {
			$strings = array_map('stripcslashes', Utils::assumeNotFalse(parse_ini_file($file, false, INI_SCANNER_RAW), "Missing or inaccessible required resource files"));
			if (count($strings) > 0) {
				return self::$langCache[$languageCode] = $strings;
			}
		}
		return self::loadLang(self::FALLBACK_LANGUAGE);
	}

	/**
	 * @param (float|int|string|Translatable)[] $params
	 */
	public function translateString(string $str, array $params = [], ?string $onlyPrefix = null): string
	{
		$baseText = ($onlyPrefix === null || str_starts_with($str, $onlyPrefix)) ? $this->internalGet($str) : null;
		if ($baseText === null) { //key not found, embedded inside format string, or doesn't match prefix
			$baseText = $this->parseTranslation($str, $onlyPrefix);
		}

		foreach ($params as $i => $p) {
			$replacement = $p instanceof Translatable ? $this->translate($p) : (string)$p;
			$baseText = str_replace("{%$i}", $replacement, $baseText);
		}

		return $baseText;
	}

	public function translate(Translatable $c): string
	{
		$baseText = $this->internalGet($c->getText());
		if ($baseText === null) { //key not found or embedded inside format string
			$baseText = $this->parseTranslation($c->getText());
		}

		foreach ($c->getParameters() as $i => $p) {
			$replacement = $p instanceof Translatable ? $this->translate($p) : $p;
			$baseText = str_replace("{%$i}", $replacement, $baseText);
		}

		return $baseText;
	}

	protected function internalGet(string $id): ?string
	{
		return self::$langCache[$this->langName][$id] ?? self::$langCache[self::FALLBACK_LANGUAGE][$id] ?? null;
	}

	public function get(string $id): string
	{
		return $this->internalGet($id) ?? $id;
	}

	/**
	 * @return string[]
	 * @phpstan-return array<string, string>
	 */
	public function getAll(): array
	{
		return self::$langCache[$this->langName];
	}

	/**
	 * Replaces translation keys embedded inside a string with their raw values.
	 * Embedded translation keys must be prefixed by a "%" character.
	 *
	 * This is used to allow the "text" field of a Translatable to contain formatting (e.g. colour codes) and
	 * multiple embedded translation keys.
	 *
	 * Normal translations whose "text" is just a single translation key don't need to use this method, and can be
	 * processed via get() directly.
	 *
	 * @param string|null $onlyPrefix If non-null, only translation keys with this prefix will be replaced. This is
	 *                                used to allow a client to do its own translating of vanilla strings.
	 */
	protected function parseTranslation(string $text, ?string $onlyPrefix = null): string
	{
		$newString = "";

		$replaceString = null;

		$len = strlen($text);
		for ($i = 0; $i < $len; ++$i) {
			$c = $text[$i];
			if ($replaceString !== null) {
				$ord = ord($c);
				if (
					($ord >= 0x30 && $ord <= 0x39) // 0-9
					|| ($ord >= 0x41 && $ord <= 0x5a) // A-Z
					|| ($ord >= 0x61 && $ord <= 0x7a) || // a-z
					$c === "." || $c === "-"
				) {
					$replaceString .= $c;
				} else {
					if (($t = $this->internalGet(substr($replaceString, 1))) !== null && ($onlyPrefix === null || strpos($replaceString, $onlyPrefix) === 1)) {
						$newString .= $t;
					} else {
						$newString .= $replaceString;
					}
					$replaceString = null;

					if ($c === "%") {
						$replaceString = $c;
					} else {
						$newString .= $c;
					}
				}
			} elseif ($c === "%") {
				$replaceString = $c;
			} else {
				$newString .= $c;
			}
		}

		if ($replaceString !== null) {
			if (($t = $this->internalGet(substr($replaceString, 1))) !== null && ($onlyPrefix === null || strpos($replaceString, $onlyPrefix) === 1)) {
				$newString .= $t;
			} else {
				$newString .= $replaceString;
			}
		}

		return $newString;
	}
}
