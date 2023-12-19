<?php

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GameServiceTest extends KernelTestCase
{
    private $entityManager;
    private $gameService;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = self::$container;
        $this->entityManager = $container->get('doctrine')->getManager();
        $this->gameService = $container->get(App\Service\GameService::class);
    }

    public function testStartNewTournament()
    {
        $tournamentName = $this->gameService->startNewTournament();

        // Проверяем, что турнир успешно создан
        $this->assertNotEmpty($tournamentName);

        // Проверяем, что турнир с таким именем действительно существует в базе данных
        $tournament = $this->gameService->checkTournament($tournamentName);
        $this->assertInstanceOf(App\Entity\Tournament::class, $tournament);
    }

    public function testPlayQualifyingMatches()
    {
        $tournamentName = $this->gameService->startNewTournament();
        $tournament = $this->gameService->checkTournament($tournamentName);

        $teams = $this->entityManager->getRepository(App\Entity\Team::class)->findAll();

        $matchResults = $this->gameService->playQualifyingMatches($teams, $tournament);

        $this->assertNotEmpty($matchResults);
    }

    public function testCheckTournament()
    {
        // Test the checkTournament method with an existing tournament
        $existingTournament = $this->gameService->checkTournament('Carlsbad');
        $this->assertInstanceOf(App\Entity\Tournament::class, $existingTournament);

        // Test the checkTournament method with a non-existing tournament
        $nonExistingTournament = $this->gameService->checkTournament('Non-Existing Tournament');
        $this->assertNull($nonExistingTournament);
    }

    public function testGetTournament()
    {
        // Test the getTournament method with an existing tournament
        $existingTournament = $this->gameService->getTournament('Carlsbad');
        $this->assertIsArray($existingTournament);
        $this->assertArrayHasKey('name', $existingTournament);

        // Test the getTournament method with a non-existing tournament
        $nonExistingTournament = $this->gameService->getTournament('Non-Existing Tournament');
        $this->assertNull($nonExistingTournament);
    }

    public function testPlayPlayoffGames()
    {
        // Assuming there's a finished tournament named 'Finished Tournament' in the database
        $tournamentName = 'Carlsbad';
        $tournament = $this->gameService->checkTournament($tournamentName);

        // Test playPlayoffGames method with a finished tournament
        $result = $this->gameService->playPlayoffGames($tournament);
        $this->assertArrayHasKey('tournamentResults', $result);
    }

    public function testGetDivisionScores()
    {
        // Assuming there's a tournament named 'Carlsbad' in the database
        $tournamentName = 'Carlsbad';
        $tournament = $this->gameService->checkTournament($tournamentName);

        // Assuming there are teams in both divisions (1 and 2)
        $teamsInDivisionA = $this->entityManager->getRepository(App\Entity\Team::class)->findBy(['division' => 1]);
        $teamsInDivisionB = $this->entityManager->getRepository(App\Entity\Team::class)->findBy(['division' => 2]);

        // Test the getDivisionScores method
        $scoresDivisionA = $this->gameService->getDivisionScores($teamsInDivisionA, $tournament);
        $scoresDivisionB = $this->gameService->getDivisionScores($teamsInDivisionB, $tournament);

        $this->assertIsArray($scoresDivisionA);
        $this->assertIsArray($scoresDivisionB);
        $this->assertNotEmpty($scoresDivisionA);
        $this->assertNotEmpty($scoresDivisionB);
    }

    public function testCreateGame()
    {
        // Assuming there's a tournament named 'Carlsbad' in the database
        $tournamentName = 'Carlsbad';
        $tournament = $this->gameService->checkTournament($tournamentName);

        // Assuming there are two teams in the tournament
        $team1 = $this->entityManager->getRepository(App\Entity\Team::class)->find(3);
        $team2 = $this->entityManager->getRepository(App\Entity\Team::class)->find(4);

        // Test the createGame method
        $game = $this->gameService->createGame($tournament, $team1, $team2, 'qualifying', $team1);

        $this->assertInstanceOf(App\Entity\Game::class, $game);
        $this->assertSame($tournament, $game->getTournament());
        $this->assertSame($team1, $game->getTeam1());
        $this->assertSame($team2, $game->getTeam2());
        $this->assertSame('qualifying', $game->getStage());
        $this->assertSame($team1, $game->getWinner());
    }

    public function testSimulateMatch()
    {
        // Assuming there are two teams
        $team1 = new App\Entity\Team();
        $team2 = new App\Entity\Team();

        // Test the simulateMatch method
        $winner = $this->gameService->simulateMatch($team1, $team2);

        $this->assertInstanceOf(App\Entity\Team::class, $winner);
        $this->assertTrue($winner === $team1 || $winner === $team2);
    }
}