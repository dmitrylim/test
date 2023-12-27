<?php

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GameServiceTest extends KernelTestCase
{
    private $entityManager;
    private $gameService;
    private $tournamentService;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = self::$container;
        $this->entityManager = $container->get('doctrine')->getManager();
        $this->gameService = $container->get(App\Service\GameService::class);
        $this->tournamentService = $container->get(App\Service\TournamentService::class);
    }

    public function testPlayQualifyingMatches()
    {
        // Assuming there's an existing tournament in the database
        $tournamentName = $this->tournamentService->startNewTournament();
        $tournament = $this->tournamentService->checkTournament($tournamentName);

        // Test tournament with teams from the same division
        $teamsSameDivision = $this->entityManager->getRepository(App\Entity\Team::class)->findBy(['division' => 1]);
        $matchResultsSameDivision = $this->gameService->playQualifyingMatches($teamsSameDivision, $tournament);
        $this->assertNotEmpty($matchResultsSameDivision);

        // Test tournament with teams from different divisions
        $teamsDifferentDivisions = $this->entityManager->getRepository(App\Entity\Team::class)->findAll();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Teams from different divisions cannot play in qualifying matches.');

        $this->gameService->playQualifyingMatches($teamsDifferentDivisions, $tournament);
    }

    public function testPlayPlayoffGames()
    {
        // Assuming there's a finished tournament named 'Finished Tournament' in the database
        $tournamentName = 'Grapevine';
        $tournament = $this->tournamentService->checkTournament($tournamentName);

        // Test playPlayoffGames method with a finished tournament
        $result = $this->gameService->playPlayoffGames($tournament);
        $this->assertArrayHasKey('tournamentResults', $result);
    }

    public function testCreateGame()
    {
        // Assuming there's a tournament named 'Grapevine' in the database
        $tournamentName = 'Grapevine';
        $tournament = $this->tournamentService->checkTournament($tournamentName);

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