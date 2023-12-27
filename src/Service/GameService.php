<?php

namespace App\Service;

use App\Entity\Game;
use App\Entity\Tournament;
use App\Entity\Team;
use App\Repository\QualifyingScoreRepository;

use Doctrine\ORM\EntityManagerInterface;

class GameService
{
    private QualifyingScoreRepository $qualifyingScoreRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager, QualifyingScoreRepository $qualifyingScoreRepository)
    {
        $this->qualifyingScoreRepository = $qualifyingScoreRepository;
        $this->entityManager = $entityManager;
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

    /**
     * Check if games for the specified tournament and division already exist.
     */
    public function gamesExist(Tournament $tournament, int $divisionType): bool
    {
        $existingGames = $this->entityManager->getRepository(Game::class)->createQueryBuilder('g')
            ->join('g.team1', 't1')
            ->join('g.team2', 't2')
            ->andWhere('g.tournament = :tournament')
            ->andWhere('t1.division = :division OR t2.division = :division')
            ->setParameter('tournament', $tournament)
            ->setParameter('division', $divisionType)
            ->getQuery()
            ->getResult();

        return !empty($existingGames);
    }
}