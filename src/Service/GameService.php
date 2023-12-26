<?php

namespace App\Service;

use App\Entity\Tournament;
use App\Entity\Team;
use App\Entity\Game;
use App\Entity\QualifyingScore;
use App\Repository\GameRepository;
use App\Repository\TournamentRepository;
use App\Repository\TeamRepository;
use App\Repository\QualifyingScoreRepository;
use App\Service\CityService;

use Doctrine\ORM\EntityManagerInterface;

class GameService
{
    private TournamentRepository $tournamentRepository;
    private TeamRepository $teamRepository;
    private QualifyingScoreRepository $qualifyingScoreRepository;
    private EntityManagerInterface $entityManager;
    private Gamerepository $gameRepository;
    private CityService $cityService;

    public function __construct(TournamentRepository $tournamentRepository, EntityManagerInterface $entityManager, TeamRepository $teamRepository,QualifyingScoreRepository $qualifyingScoreRepository, GameRepository $gameRepository, CityService $cityService)
    {
        $this->tournamentRepository = $tournamentRepository;
        $this->teamRepository = $teamRepository;
        $this->qualifyingScoreRepository = $qualifyingScoreRepository;
        $this->gameRepository = $gameRepository;
        $this->entityManager = $entityManager;
        $this->cityService = $cityService;
    }

    public function startNewTournament()
    {
        $tournament = new Tournament();
        $tournament->setStatus('new');

        $name = $this->cityService->getRandomUniqueCity();

        $tournament->setName($name);

        $this->entityManager->persist($tournament);
        $this->entityManager->flush();

        $teams = $this->teamRepository->findAll();

        foreach ($teams as $team) {
            $qualifyingScore = new QualifyingScore();
            $qualifyingScore->setTournament($tournament);
            $qualifyingScore->setTeam($team);
            $qualifyingScore->setScore(0);

            $this->entityManager->persist($qualifyingScore);
        }

        $this->entityManager->flush();

        return $name;
    }

    public function checkTournament($name)
    {
        $tournament = $this->tournamentRepository->findOneBy(['name' => $name]);

        return $tournament;
    }

    public function getTournament($name)
    {
        $tournament = $this->tournamentRepository->findOneBy(['name' => $name]);

        if($tournament) {
            $tournamentInfo = [];
            $tournamentInfo['name'] = $tournament->getName();

            if($tournament->getStatus() != 'new') {
                $tournamentInfo['results'] = $this->getTournamentResults($tournament);
            }

            return $tournamentInfo;
        }

        return $tournament;
    }

    public function getTournamentResults(Tournament $tournament): array
    {
        $gameResults = [
            'qualifying' => ['a' => [], 'b' => []],
            'quarterFinal' => [],
            'semiFinal' => [],
            'final' => [],
            'tournamentResults' => [],
        ];

        $games = $this->gameRepository->findByTournament($tournament);

        foreach ($games as $game) {
            switch ($game->getStage()) {
                case 'qualifying':
                    $this->processQualifyingGame($game, $gameResults);
                    break;
                case 'quarterFinal':
                    $this->processStageGame($game, $gameResults['quarterFinal']);
                    break;
                case 'semiFinal':
                    $this->processStageGame($game, $gameResults['semiFinal']);
                    break;
                case 'final':
                    $this->processStageGame($game, $gameResults['final']);
                    $this->processTournamentResults($game, $gameResults['tournamentResults']);
                    break;
            }
        }

        $teamsInDivisionA = $this->entityManager->getRepository(Team::class)->findBy(['division' => 1]);
        $gameResults['qualifying']['a']['score'] = $this->getDivisionScores($teamsInDivisionA, $tournament);

        $teamsInDivisionB = $this->entityManager->getRepository(Team::class)->findBy(['division' => 2]);
        $gameResults['qualifying']['b']['score'] = $this->getDivisionScores($teamsInDivisionB, $tournament);

        return $gameResults;
    }

    public function getDivisionScores(array $teams, Tournament $tournament): array
    {
        $divisionScores = [];

        foreach ($teams as $team) {
            $score = $this->qualifyingScoreRepository->findByTeamAndTournament($team, $tournament);
            $divisionScores[] = [
                'team' => $team->getName(),
                'score' => $score->getScore(),
            ];
        }

        return $divisionScores;
    }

    private function processTournamentResults(Game $finalGame, array &$tournamentResults): void
    {
        $winner = $finalGame->getWinner()->getName();

        $loser = $winner === $finalGame->getTeam1()->getName() ? $finalGame->getTeam2()->getName() : $finalGame->getTeam1()->getName();

        $tournamentResults[] = $winner;
        $tournamentResults[] = $loser;
    }

    private function processQualifyingGame(Game $game, array &$gameResults): void
    {
        $division = $game->getTeam1()->getDivision()->getName();
        $stageResults = &$gameResults['qualifying'][strtolower($division)];

        $stageResults[] = [
            'team1' => $game->getTeam1()->getName(),
            'team2' => $game->getTeam2()->getName(),
            'winner' => $game->getWinner()->getName(),
        ];
    }

    private function processStageGame(Game $game, array &$stageResults): void
    {
        $stageResults[] = [
            'team1' => $game->getTeam1()->getName(),
            'team2' => $game->getTeam2()->getName(),
            'winner' => $game->getWinner()->getName(),
        ];
    }

    public function playQualifyingMatches(array $teams, Tournament $tournament): array
    {
        $matchResults = [];
        $playedMatches = [];

        foreach ($teams as $team1) {
            foreach ($teams as $team2) {

                // Ensure teams are from the same division
                if ($team1->getDivision() !== $team2->getDivision()) {
                    throw new \RuntimeException('Teams from different divisions cannot play in qualifying matches.');
                }

                if ($team1 !== $team2 && !$this->hasMatchBeenPlayed($playedMatches, $team1, $team2)) {
                    $winner = $this->simulateMatch($team1, $team2);
                    $game = $this->createGame($tournament, $team1, $team2, 'qualifying', $winner);
                    $this->updateScore($winner, $tournament);

                    $matchResults[] = [
                        'team1' => $team1->getName(),
                        'team2' => $team2->getName(),
                        'winner' => $winner->getName(),
                    ];

                    $playedMatches[] = [$team1, $team2];

                    $this->entityManager->persist($game);
                }
            }

            $score = $this->qualifyingScoreRepository->findByTeamAndTournament($team1, $tournament);
            $matchResults['score'][] = [
                'team' => $team1->getName(),
                'score' => $score->getScore(),
            ];
        }
        $tournament->setStatus('started');
        $this->entityManager->persist($tournament);
        $this->entityManager->flush();

        return $matchResults;
    }

    private function hasMatchBeenPlayed(array $playedMatches, Team $team1, Team $team2): bool
    {
        // Check if the pair of teams has already played against each other
        return in_array([$team1, $team2], $playedMatches) || in_array([$team2, $team1], $playedMatches);
    }

    private function updateScore(Team $winner, Tournament $tournament): void
    {
        $score = $this->qualifyingScoreRepository->findByTeamAndTournament($winner, $tournament);
        $currentScore = $score->getScore();
        $score->setScore($currentScore + 1);
        $this->entityManager->persist($score);
    }

    public function playPlayoffGames(Tournament $tournament)
    {
        $teamsA = $this->qualifyingScoreRepository->findTopTeamsByTournamentAndDivision($tournament->getId(), 1);
        $teamsB = $this->qualifyingScoreRepository->findTopTeamsByTournamentAndDivision($tournament->getId(), 2);

        if (empty($teamsA) || empty($teamsB)) {
            return []; // Early return if there are no teams
        }

        $gameResults = [
            'quarterFinal' => $this->playStageMatches($teamsA, $teamsB, 'quarterFinal', $tournament),
            'semiFinal' => [],
            'final' => []
        ];

        // Semi-final
        $semiFinalists = $this->getWinners($gameResults['quarterFinal']);
        for ($i = 0; $i < count($semiFinalists); $i += 2) {
            $gameResult = $this->playMatch($semiFinalists[$i], $semiFinalists[$i + 1], 'semiFinal', $tournament);
            $gameResults['semiFinal'][] = $gameResult;
        }

        // Final
        $finalists = $this->getWinners($gameResults['semiFinal']);
        $gameResults['final'][] = $this->playMatch($finalists[0], $finalists[1], 'final', $tournament);

        $thirdPlaceFinalists = $this->getLosers($gameResults['semiFinal']);
        $gameResults['final'][] = $this->playMatch($thirdPlaceFinalists[0], $thirdPlaceFinalists[1], 'final', $tournament);

        $tournament->setStatus('finished');
        $this->entityManager->persist($tournament);
        $this->entityManager->flush();

        $gameResults['tournamentResults'] = $this->getFinalResults($gameResults['final']);

        $readableGameResults = $this->getReadableResults($gameResults);

        return $readableGameResults;
    }

    private function playStageMatches(array $teamsA, array $teamsB, string $stage, $tournament): array
    {
        $results = [];
        $count = count($teamsA);
        for ($i = 0; $i < $count; $i++) {
            $team1 = $teamsA[$i]->getTeam();
            $team2 = $teamsB[$count - $i - 1]->getTeam();

            $results[] = $this->playMatch($team1, $team2, $stage, $tournament);
        }
        return $results;
    }

    private function playMatch(Team $team1, Team $team2, string $stage, Tournament $tournament): array
    {
        $winner = $this->simulateMatch($team1, $team2);
        $game = $this->createGame($tournament, $team1, $team2, $stage, $winner);
        $this->entityManager->persist($game);

        return [
            'team1' => $team1,
            'team2' => $team2,
            'winner' => $winner
        ];
    }

    private function getWinners(array $matches): array
    {
        return array_map(fn($match) => $match['winner'], $matches);
    }

    private function getLosers(array $matches): array
    {
        return array_map(fn($match) => $match['winner'] === $match['team1'] ? $match['team2'] : $match['team1'], $matches);
    }

    private function getFinalResults(array $finalMatches): array
    {
        $winners = $this->getWinners($finalMatches);
        $losers = $this->getLosers($finalMatches);

        $finalResults = [];

        for ($i = 0; $i < count($winners); $i++) {
            $finalResults[] = $winners[$i];
            $finalResults[] = $losers[$i];
        }

        return $finalResults;
    }

    private function getReadableResults($gameResults): array
    {   $readableGameResults = [];

        $rounds = ['quarterFinal', 'semiFinal', 'final'];

        foreach ($rounds as $roundKey) {
            foreach ($gameResults[$roundKey] as $matchKey => $match) {
                $readableGameResults[$roundKey][$matchKey]['team1'] = $match['team1']->getName();
                $readableGameResults[$roundKey][$matchKey]['team2'] = $match['team2']->getName();
                $readableGameResults[$roundKey][$matchKey]['winner'] = $match['winner']->getName();
            }
        }

        $readableGameResults['tournamentResults'] = array_map(function ($result) {
            return $result->getName();
        }, $gameResults['tournamentResults']);

        return $readableGameResults;
    }

    public function createGame(Tournament $tournament, $team1, $team2, $stage, $winner)
    {
        $game = new Game();
        $game->setTeam1($team1);
        $game->setTeam2($team2);
        $game->setStage($stage);
        $game->setTournament($tournament);
        $game->setWinner($winner);

        return $game;
    }

    public function simulateMatch(Team $team1, Team $team2): Team
    {
        // Simulate a match result by randomly selecting one of the teams as the winner
        $winner = rand(0, 1) === 0 ? $team1 : $team2;

        return $winner;
    }
}