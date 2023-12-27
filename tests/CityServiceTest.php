<?php

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CityServiceTest extends KernelTestCase
{
    private $tournamentRepository;
    private $cityService;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = self::$container;
        $this->tournamentRepository = $container->get(App\Repository\TournamentRepository::class);
        $this->cityService = $container->get(App\Service\CityService::class);
    }

    public function testGetRandomUniqueCity(): void
    {
        $randomUniqueCity = $this->cityService->getRandomUniqueCity();

        // Check if return value is not empty and unique
        $this->assertNotEmpty($randomUniqueCity);
        $this->assertTrue($this->cityService->isCityUnique($randomUniqueCity));
    }

    // Check if return value is not empty
    public function testGetRandomCity(): void
    {
        $randomCity = $this->cityService->getRandomCity();

        $this->assertNotEmpty($randomCity);
    }

    public function testIsCityUnique(): void
    {
        // Tournament 'Grapevine' exists in database
        $existentCity = 'Grapevine';
        $isUnique = $this->cityService->isCityUnique($existentCity);
        $this->assertFalse($isUnique);

        // Tournament 'NonExistentCity' does not exists in database
        $isUnique = $this->cityService->isCityUnique("NonExistentCity");
        $this->assertTrue($isUnique);
    }
}