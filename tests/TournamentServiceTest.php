<?php

require_once __DIR__ . '/WinnerAssertionTrait.php';

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TournamentServiceTest extends KernelTestCase
{
    use WinnerAssertionTrait;

    private $entityManager;
    private $tournamentService;
    private $qualifyingScoreRepository;
    private $teamRepository;
    private $tournamentRepository;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = self::$container;
        $this->entityManager = $container->get('doctrine')->getManager();
        $this->tournamentService = $container->get(App\Service\TournamentService::class);
        $this->qualifyingScoreRepository = $container->get(App\Repository\QualifyingScoreRepository::class);
        $this->teamRepository = $container->get(App\Repository\TeamRepository::class);
        $this->tournamentRepository = $container->get(App\Repository\TournamentRepository::class);
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

    public function testGetTournamentResults()
    {
        $tournament = $this->entityManager->getRepository(App\Entity\Tournament::class)->findOneBy(['name' => 'Plano']);

        // Check if the tournament exists
        $this->assertInstanceOf(App\Entity\Tournament::class, $tournament);

        $gameResults = $this->tournamentService->getTournamentResults($tournament);

        // We should get 28 qualifying games + score for every division
        $this->assertArrayHasKey('qualifying', $gameResults);
        $this->assertArrayHasKey('a', $gameResults['qualifying']);
        $this->assertCount(29, $gameResults['qualifying']['a']);
        $this->assertArrayHasKey('score', $gameResults['qualifying']['a']);

        $this->assertArrayHasKey('b', $gameResults['qualifying']);
        $this->assertCount(29, $gameResults['qualifying']['b']);
        $this->assertArrayHasKey('score', $gameResults['qualifying']['b']);

        $this->assertArrayHasKey('quarterFinal', $gameResults);
        $this->assertCount(4, $gameResults['quarterFinal']);

        $this->assertArrayHasKey('semiFinal', $gameResults);
        $this->assertCount(2, $gameResults['semiFinal']);

        $this->assertArrayHasKey('final', $gameResults);
        $this->assertCount(2, $gameResults['final']);

        $this->assertArrayHasKey('tournamentResults', $gameResults);
        $this->assertCount(4, $gameResults['tournamentResults']);

        // Check if the champion was winner in all stages and second team lost only on final
        $tournamentResults = $gameResults['tournamentResults'];

        $this->assertStageWinners($gameResults, $tournamentResults);
    }
}