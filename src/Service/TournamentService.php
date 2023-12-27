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

class TournamentService
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

    public function getTournamentInfo($name)
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
        $stageResults = &$gameResults['qualifying'][$division];

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
}