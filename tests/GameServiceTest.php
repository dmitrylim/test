<?php

require_once __DIR__ . '/WinnerAssertionTrait.php';

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GameServiceTest extends KernelTestCase
{
    use WinnerAssertionTrait;

    private $entityManager;
    private $gameService;
    private $tournamentService;
    private $qualifyingScoreRepository;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = self::$container;
        $this->entityManager = $container->get('doctrine')->getManager();
        $this->gameService = $container->get(App\Service\GameService::class);
        $this->tournamentService = $container->get(App\Service\TournamentService::class);
        $this->qualifyingScoreRepository = $container->get(App\Repository\QualifyingScoreRepository::class);
    }

    public function testPlayQualifyingMatches()
    {
        // Assuming there's an existing tournament in the database
        $tournamentName = $this->tournamentService->startNewTournament();
        $tournament = $this->tournamentService->checkTournament($tournamentName);

        // Test tournament with teams from division "a", should return 28 games and score
        $matchResults = $this->gameService->playQualifyingMatches('a', $tournament);
        $this->assertNotEmpty($matchResults);
        $this->assertArrayHasKey('score', $matchResults);
        $this->assertCount(29, $matchResults);
    }

    public function testPlayPlayoffGames()
    {
        // Assuming there's a finished tournament named 'Temecula' in the database
        $tournamentName = 'Temecula';
        $tournament = $this->tournamentService->checkTournament($tournamentName);

        // Test if tournament has results
        $result = $this->gameService->playPlayoffGames($tournament);
        $this->assertArrayHasKey('tournamentResults', $result);

        // Check if strongest "A" team played with weakest "B" team and if strongest "B" team played with weakest "A" team
        $teamsA = $this->qualifyingScoreRepository->findTopTeamsByTournamentAndDivision($tournament->getId(), 1);
        $teamsB = $this->qualifyingScoreRepository->findTopTeamsByTournamentAndDivision($tournament->getId(), 2);
        $this->assertQuarterFinalTeams($teamsA, $teamsB, $result);

        // Check if first two winners of Quarter-final met in the next game
        $this->assertSemiFinalTeams($result);

        // Check if two winners of Semi-final met in Final
        $this->assertFinalTeams($result);

        // Check if the loser of Semi-final met that won in Final is on 3rd place
        $this->assertThirdPlace($result);

        // Check if the champion was winner in all stages and second team lost only on final
        $tournamentResults = $result['tournamentResults'];

        $this->assertStageWinners($result, $tournamentResults);
    }

    public function testCreateGame()
    {
        // Assuming there's a tournament named 'Grapevine' in the database
        $tournamentName = 'Portland';
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