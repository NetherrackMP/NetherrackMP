<?php

declare(strict_types=1);

namespace pocketmine\world\nether\populator;

use pocketmine\world\nether\decorator\FireDecorator;
use pocketmine\world\nether\decorator\GlowstoneDecorator;
use pocketmine\world\nether\decorator\MushroomDecorator;
use pocketmine\world\Populator;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\World;
use function array_push;

class NetherPopulator implements Populator{

	/** @var Populator[] */
	private array $inGroundPopulators = [];

	/** @var Populator[] */
	private array $onGroundPopulators = [];

	private OrePopulator $orePopulator;
	private FireDecorator $fireDecorator;
	private GlowstoneDecorator $glowstoneDecorator1;
	private GlowstoneDecorator $glowstoneDecorator2;
	private MushroomDecorator $brownMushroomDecorator;
	private MushroomDecorator $redMushroomDecorator;

	public function __construct(int $worldHeight = World::Y_MAX){
		$this->orePopulator = new OrePopulator($worldHeight);
		$this->inGroundPopulators[] = $this->orePopulator;

		$this->fireDecorator = new FireDecorator();
		$this->glowstoneDecorator1 = new GlowstoneDecorator(true);
		$this->glowstoneDecorator2 = new GlowstoneDecorator();
		$this->brownMushroomDecorator = new MushroomDecorator(VanillaBlocks::BROWN_MUSHROOM());
		$this->redMushroomDecorator = new MushroomDecorator(VanillaBlocks::RED_MUSHROOM());

		array_push($this->onGroundPopulators,
			$this->fireDecorator,
			$this->glowstoneDecorator1,
			$this->glowstoneDecorator2,
			$this->fireDecorator,
			$this->brownMushroomDecorator,
			$this->redMushroomDecorator
		);

		$this->fireDecorator->setAmount(1);
		$this->glowstoneDecorator1->setAmount(1);
		$this->glowstoneDecorator2->setAmount(1);
		$this->brownMushroomDecorator->setAmount(1);
		$this->redMushroomDecorator->setAmount(1);
	}

	public function populate(ChunkManager $world, Random $random, int $chunkX, int $chunkZ, Chunk $chunk) : void{
		$this->populateInGround($world, $random, $chunkX, $chunkZ, $chunk);
		$this->populateOnGround($world, $random, $chunkX, $chunkZ, $chunk);
	}

	private function populateInGround(ChunkManager $world, Random $random, int $chunkX, int $chunkZ, Chunk $chunk) : void{
		foreach($this->inGroundPopulators as $populator){
			$populator->populate($world, $random, $chunkX, $chunkZ, $chunk);
		}
	}

	private function populateOnGround(ChunkManager $world, Random $random, int $chunkX, int $chunkZ, Chunk $chunk) : void{
		foreach($this->onGroundPopulators as $populator){
			$populator->populate($world, $random, $chunkX, $chunkZ, $chunk);
		}
	}
}
