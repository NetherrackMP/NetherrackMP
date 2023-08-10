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

namespace pocketmine\updater;

use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Internet;
use function json_decode;

class UpdateCheckTask extends AsyncTask
{
	private const TLS_KEY_UPDATER = "updater";

	private string $error = "Unknown error";

	public function __construct(
		UpdateChecker  $updater,
		private string $endpoint
	)
	{
		$this->storeLocal(self::TLS_KEY_UPDATER, $updater);
	}

	public function onRun(): void
	{
		$error = "";
		$response = Internet::getURL($this->endpoint, 5, [], $error);
		$this->error = $error;
		if (is_null($response)) return;
		$response = json_decode($response->getBody(), true);
		if (isset($response["message"])) {
			$this->error = $response["message"];
			return;
		}
		$latest = null;
		foreach ($response as $res) {
			$res["published_timestamp"] = strtotime($res["published_at"]);
			if (!$latest || $res["published_timestamp"] > $latest["published_timestamp"]) {
				$latest = $res;
			}
		}
		if (is_null($latest)) {
			$this->error = "No release was found.";
			return;
		}
		$response = $latest;
		$asset = null;
		foreach ($response["assets"] as $a) {
			if ($a["name"] == "Netherrack-MP.phar") {
				$asset = $a["browser_download_url"];
				break;
			}
		}
		$this->setResult(new UpdateInfo(
			$response["tag_name"],
			$response["published_timestamp"],
			$response["url"],
			$asset
		));
	}

	public function onCompletion(): void
	{
		/** @var UpdateChecker $updater */
		$updater = $this->fetchLocal(self::TLS_KEY_UPDATER);
		if ($this->hasResult()) {
			/** @var UpdateInfo $response */
			$response = $this->getResult();
			$updater->checkUpdateCallback($response);
		} else {
			$updater->checkUpdateError($this->error);
		}
	}
}
