<?php

namespace App\Controller;

use App\Entity\Team;
use App\Entity\Tournament;
use App\Entity\Game;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\GameService;
use App\Service\TournamentService;

class IndexController extends AbstractController
{
    private GameService $gameService;
    private TournamentService $tournamentService;

    public function __construct(GameService $gameService, TournamentService $tournamentService)
    {
        $this->gameService = $gameService;
        $this->tournamentService = $tournamentService;
    }

    #[Route('/', name: 'app_index')]
    public function index(): Response
    {
        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
        ]);
    }

    #[Route('/test', name: 'app_index_test')]
    public function test(): Response
    {
        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
        ]);
    }

    #[Route('/start-new-tournament', name: 'app_start_new_tournament')]
    public function startNewTournament(Request $request)
    {
        if ($request->isXmlHttpRequest() || $request->query->get('showJson') == 1) {

            $tournamentName = $this->tournamentService->startNewTournament();

            return new JsonResponse(array(
                'code' => 200,
                'error' => 'no',
                'tournament' => $tournamentName),
                200);
        }

        else {
            return $this->render('index/index.html.twig', [
                'controller_name' => 'IndexController',
            ]);
        }
    }

    #[Route('/get-tournament', name: 'app_get_tournament')]
    public function getTournament(Request $request)
    {
        if ($request->isXmlHttpRequest() || $request->query->get('showJson') == 1) {

            $tournamentName = $request->query->get('tournament');

            $tournament = $this->tournamentService->getTournament($tournamentName);

            if($tournament) {
                return new JsonResponse(array(
                'code' => 200,
                'error' => 'no',
                'tournament' => $tournament),
                200);
            }
            else {
                return new JsonResponse(array(
                'code' => 200,
                'error' => 'yes',
                'message' => 'no such tournament'),
                200);
            }
        }

        else {
            return $this->render('index/index.html.twig', [
                'controller_name' => 'IndexController',
            ]);
        }
    }

    #[Route('/generate-division', name: 'app_generate_division')]
    public function generateDivision(Request $request): Response
    {
        if ($this->isAjaxRequest($request) || $request->query->get('showJson') == 1) {
            $tournamentName = $request->query->get('tournament');
            $tournament = $this->tournamentService->checkTournament($tournamentName);

            if (!$tournament) {
                return $this->jsonResponse([
                    'code' => 200,
                    'error' => 'yes',
                    'message' => 'no such tournament',
                ]);
            }

            $divisionType = $request->query->get('type');
            
            if ($this->gamesExist($tournament, $divisionType)) {
                return $this->jsonResponse([
                    'code' => 200,
                    'error' => 'yes',
                    'message' => 'Games for this division already exist',
                ]);
            }

            $divisionType = $request->query->get('type');
            if ($divisionType == 1) {
                $teams = $this->getDoctrine()->getRepository(Team::class)->findBy(['division' => 1]);
                $matchResults['a'] = $this->gameService->playQualifyingMatches($teams, $tournament);
            }
            else if ($divisionType == 2) {
                $teams = $this->getDoctrine()->getRepository(Team::class)->findBy(['division' => 2]);
                $matchResults['b'] = $this->gameService->playQualifyingMatches($teams, $tournament);
            }

            return $this->jsonResponse([
                'code' => 200,
                'error' => 'no',
                'results' => $matchResults,
            ]);
        }

        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
        ]);
    }

    /**
     * Check if games for the specified tournament and division already exist.
     */
    private function gamesExist(Tournament $tournament, int $divisionType): bool
    {
        $existingGames = $this->getDoctrine()->getRepository(Game::class)->createQueryBuilder('g')
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

    #[Route('/generate-playoff', name: 'app_generate_playoff')]
    public function generatePlayoff(Request $request): Response
    {
        if ($this->isAjaxRequest($request) || $request->query->get('showJson') == 1) {
            $tournamentName = $request->query->get('tournament');
            $tournament = $this->tournamentService->checkTournament($tournamentName);

            if (!$tournament) {
                return $this->jsonResponse([
                    'code' => 200,
                    'error' => 'yes',
                    'message' => 'no such tournament',
                ]);
            }

            if ($tournament->getStatus() == 'finished') {
                return $this->jsonResponse([
                    'code' => 200,
                    'error' => 'yes',
                    'message' => 'tournament finished',
                ]);
            }

            if (!$this->gamesExist($tournament, 1) || !$this->gamesExist($tournament, 2)) {
                return $this->jsonResponse([
                    'code' => 200,
                    'error' => 'yes',
                    'message' => 'Please, generate A and B divisions first',
                ]);
            }

            $matchResults = $this->gameService->playPlayoffGames($tournament);

            return $this->jsonResponse([
                'code' => 200,
                'error' => 'no',
                'tournament' => $matchResults,
            ]);
        }

        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
        ]);
    }

    /**
     * Check if the request is an Ajax request.
     */
    private function isAjaxRequest(Request $request): bool
    {
        return $request->isXmlHttpRequest() || $request->query->get('showJson') == 1;
    }

    /**
     * Return a JSON response.
     */
    private function jsonResponse(array $data, int $statusCode = 200): JsonResponse
    {
        return new JsonResponse($data, $statusCode);
    }
}
