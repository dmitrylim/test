<?php

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TournamentServiceTest extends KernelTestCase
{
    private $entityManager;
    private $tournamentService;
    private $qualifyingScoreRepository;
    private $teamRepository;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = self::$container;
        $this->entityManager = $container->get('doctrine')->getManager();
        $this->tournamentService = $container->get(App\Service\TournamentService::class);
        $this->qualifyingScoreRepository = $container->get(App\Repository\QualifyingScoreRepository::class);
        $this->teamRepository = $container->get(App\Repository\TeamRepository::class);
    }

    public function testStartNewTournament()
    {
        $tournamentName = $this->tournamentService->startNewTournament();

        // Check tournament creation
        $this->assertNotEmpty($tournamentName);

        // Check tournament existense
        $tournament = $this->tournamentService->checkTournament($tournamentName);
        $this->assertInstanceOf(App\Entity\Tournament::class, $tournament);

        // Check QualifyingScores existense
        $teams = $this->teamRepository->findAll();

        foreach ($teams as $team) {
            $score = $this->qualifyingScoreRepository->findByTeamAndTournament($team, $tournament);

            $this->assertInstanceOf(App\Entity\QualifyingScore::class, $score);

            // Check that QualifyingScore is 0
            $this->assertEquals(0, $score->getScore());
        }
    }

    public function testCheckTournament()
    {
        // Test the checkTournament method with an existing tournament
        $existingTournament = $this->tournamentService->checkTournament('Grapevine');
        $this->assertInstanceOf(App\Entity\Tournament::class, $existingTournament);

        // Test the checkTournament method with a non-existing tournament
        $nonExistingTournament = $this->tournamentService->checkTournament('Non-Existing Tournament');
        $this->assertNull($nonExistingTournament);
    }

    public function testGetTournamentInfo()
    {
        // Test the getTournament method with an existing tournament
        $existingTournament = $this->tournamentService->getTournamentInfo('Grapevine');
        $this->assertIsArray($existingTournament);
        $this->assertArrayHasKey('name', $existingTournament);

        // Test the getTournament method with a new tournament
        $newTournament = $this->tournamentService->getTournamentInfo('Lancaster');
        $this->assertArrayNotHasKey('results', $newTournament);

        // Test the getTournament method with a finished tournament
        $finishedTournament = $this->tournamentService->getTournamentInfo('Grapevine');
        $this->assertArrayHasKey('results', $finishedTournament);

        // Test the getTournament method with a non-existing tournament
        $nonExistingTournament = $this->tournamentService->getTournamentInfo('Non-Existing Tournament');
        $this->assertNull($nonExistingTournament);
    }

    public function testGetDivisionScores()
    {
        $tournamentName = 'Grapevine';
        $tournament = $this->tournamentService->checkTournament($tournamentName);

        // Assuming there are teams in both divisions (1 and 2)
        $teamsInDivisionA = $this->entityManager->getRepository(App\Entity\Team::class)->findBy(['division' => 1]);
        $teamsInDivisionB = $this->entityManager->getRepository(App\Entity\Team::class)->findBy(['division' => 2]);

        // Test the getDivisionScores method
        $scoresDivisionA = $this->tournamentService->getDivisionScores($teamsInDivisionA, $tournament);
        $scoresDivisionB = $this->tournamentService->getDivisionScores($teamsInDivisionB, $tournament);

        $this->assertIsArray($scoresDivisionA);
        $this->assertIsArray($scoresDivisionB);
        $this->assertNotEmpty($scoresDivisionA);
        $this->assertNotEmpty($scoresDivisionB);
    }

    /*public function testGetTournamentResults()
    {

    }*/
}