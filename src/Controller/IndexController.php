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

    #[Route('/get-tournament-info', name: 'app_get_tournament_info')]
    public function getTournamentInfo(Request $request)
    {
        if ($request->isXmlHttpRequest() || $request->query->get('showJson') == 1) {

            $tournamentName = $request->query->get('tournament');

            $tournament = $this->tournamentService->getTournamentInfo($tournamentName);

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

            if ($this->gameService->gamesExist($tournament, $divisionType)) {
                return $this->jsonResponse([
                    'code' => 200,
                    'error' => 'yes',
                    'message' => 'Games for this division already exist',
                ]);
            }

            if ($divisionType == "a" || $divisionType == "b") {
                $matchResults[$divisionType] = $this->gameService->playQualifyingMatches($divisionType, $tournament);
            } else {
                return $this->jsonResponse([
                    'code' => 200,
                    'error' => 'yes',
                    'message' => 'Invalid division type',
                ]);
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

            if (!$this->gameService->gamesExist($tournament, 'a') || !$this->gameService->gamesExist($tournament, 'b')) {
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
