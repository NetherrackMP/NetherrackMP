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

use pocketmine\event\server\UpdateNotifyEvent;
use pocketmine\Server;
use pocketmine\utils\VersionString;
use pocketmine\VersionInfo;
use PrefixedLogger;

class UpdateChecker
{

	protected Server $server;
	protected string $endpoint;
	protected ?UpdateInfo $updateInfo = null;
	private \Logger $logger;

	public function __construct(Server $server, string $endpoint)
	{
		$this->server = $server;
		$this->logger = new PrefixedLogger($server->getLogger(), "Update Checker");
		$this->endpoint = "https://api.github.com/repositories/$endpoint/releases";
		if ($server->getConfigGroup()->getPropertyBool("auto-updater.enabled", true))
			$this->doCheck();
	}

	public function checkUpdateError(string $error): void
	{
		$this->logger->debug("Async update check failed due to \"$error\"");
	}

	/**
	 * Callback used at the end of the update checking task
	 */
	public function checkUpdateCallback(UpdateInfo $updateInfo): void
	{
		$this->checkUpdate($updateInfo);
		if ($this->hasUpdate()) {
			(new UpdateNotifyEvent($this))->call();
			if ($this->server->getConfigGroup()->getPropertyBool("auto-updater.on-update.warn-console", true)) {
				$this->showConsoleUpdate();
			}
		}
	}

	/**
	 * Returns whether there is an update available.
	 */
	public function hasUpdate(): bool
	{
		return $this->updateInfo !== null;
	}

	/**
	 * Posts a warning to the console to tell the user there is an update available
	 */
	public function showConsoleUpdate(): void
	{
		if ($this->updateInfo === null) return;
		$this->logger->warning("Your version of " . $this->server->getName() . " is out of date. Version " . $this->updateInfo->base_version . " was released on " . date("D M j h:i:s Y", $this->updateInfo->date));
		$this->logger->warning("Details: " . $this->updateInfo->details_url);
		$this->logger->warning("Download: " . $this->updateInfo->download_url);
	}

	/**
	 * Returns the last retrieved update data.
	 */
	public function getUpdateInfo(): ?UpdateInfo
	{
		return $this->updateInfo;
	}

	/**
	 * Schedules an AsyncTask to check for an update.
	 */
	public function doCheck(): void
	{
		$this->server->getAsyncPool()->submitTask(new UpdateCheckTask($this, $this->endpoint));
	}

	/**
	 * Checks the update information against the current server version to decide if there's an update
	 */
	protected function checkUpdate(UpdateInfo $updateInfo): void
	{
		if ((new VersionString(VersionInfo::BASE_VERSION))->compare(new VersionString($updateInfo->base_version), true) > 0)
			$this->updateInfo = $updateInfo;
	}

	/**
	 * Returns the host used for update checks.
	 */
	public function getEndpoint(): string
	{
		return $this->endpoint;
	}
}
