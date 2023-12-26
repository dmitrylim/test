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

use Doctrine\ORM\EntityManagerInterface;

class GameService
{
    private TournamentRepository $tournamentRepository;
    private TeamRepository $teamRepository;
    private QualifyingScoreRepository $qualifyingScoreRepository;
    private EntityManagerInterface $entityManager;
    private Gamerepository $gameRepository;
    private array $cities;

    public function __construct(TournamentRepository $tournamentRepository, EntityManagerInterface $entityManager, TeamRepository $teamRepository,QualifyingScoreRepository $qualifyingScoreRepository, GameRepository $gameRepository)
    {
        $this->tournamentRepository = $tournamentRepository;
        $this->teamRepository = $teamRepository;
        $this->qualifyingScoreRepository = $qualifyingScoreRepository;
        $this->gameRepository = $gameRepository;
        $this->entityManager = $entityManager;
        $this->cities = [
            'New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'Philadelphia', 'San Antonio', 'San Diego', 'Dallas', 'San Jose',
            'Austin', 'Jacksonville', 'Indianapolis', 'San Francisco', 'Columbus', 'Fort Worth', 'Charlotte', 'Detroit', 'El Paso', 'Memphis',
            'Seattle', 'Denver', 'Washington', 'Boston', 'Nashville', 'Baltimore', 'Oklahoma City', 'Louisville', 'Portland', 'Las Vegas',
            'Milwaukee', 'Albuquerque', 'Tucson', 'Fresno', 'Sacramento', 'Long Beach', 'Kansas City', 'Mesa', 'Atlanta', 'Colorado Springs',
            'Raleigh', 'Omaha', 'Miami', 'Tulsa', 'Oakland', 'Cleveland', 'Minneapolis', 'Wichita', 'Arlington', 'New Orleans', 'Bakersfield',
            'Tampa', 'Honolulu', 'Anaheim', 'Aurora', 'Santa Ana', 'St. Louis', 'Riverside', 'Corpus Christi', 'Lexington', 'Pittsburgh',
            'Anchorage', 'Stockton', 'Cincinnati', 'St. Paul', 'Toledo', 'Newark', 'Greensboro', 'Plano', 'Henderson', 'Lincoln', 'Buffalo',
            'Jersey City', 'Chula Vista', 'Fort Wayne', 'Orlando', 'St. Petersburg', 'Chandler', 'Laredo', 'Norfolk', 'Durham', 'Madison',
            'Lubbock', 'Irvine', 'Winston-Salem', 'Glendale', 'Hialeah', 'Garland', 'Scottsdale', 'Irving', 'Chesapeake', 'North Las Vegas',
            'Fremont', 'Baton Rouge', 'Richmond', 'Boise', 'San Bernardino', 'Birmingham', 'Spokane', 'Rochester', 'Modesto', 'Des Moines',
            'Oxnard', 'Tacoma', 'Fontana', 'Akron', 'Yonkers', 'Moreno Valley', 'Fayetteville', 'Aurora', 'Glendale', 'Huntington Beach',
            'Montgomery', 'Amarillo', 'Little Rock', 'Columbus', 'Grand Rapids', 'Salt Lake City', 'Tallahassee', 'Worcester', 'Newport News',
            'Huntsville', 'Knoxville', 'Providence', 'Santa Clarita', 'Grand Prairie', 'Brownsville', 'Jackson', 'Overland Park', 'Garden Grove',
            'Santa Rosa', 'Chattanooga', 'Oceanside', 'Fort Lauderdale', 'Rancho Cucamonga', 'Port St. Lucie', 'Ontario', 'Vancouver', 'Tempe',
            'Springfield', 'Lancaster', 'Eugene', 'Pembroke Pines', 'Salem', 'Cape Coral', 'Peoria', 'Sioux Falls', 'Springfield', 'Elk Grove',
            'Rockford', 'Palmdale', 'Corona', 'Salinas', 'Pomona', 'Pasadena', 'Joliet', 'Paterson', 'Kansas City', 'Torrance', 'Bridgeport',
            'Alexandria', 'Sunnyvale', 'Escondido', 'Savannah', 'Orange', 'Naperville', 'Mesquite', 'Dayton', 'Pasadena', 'Fullerton', 'McAllen',
            'Killeen', 'Frisco', 'Hampton', 'Bellevue', 'Warren', 'West Valley City', 'Columbia', 'Olathe', 'Sterling Heights', 'New Haven',
            'Miramar', 'Waco', 'Thousand Oaks', 'Cedar Rapids', 'Charleston', 'Visalia', 'Topeka', 'Elizabeth', 'Gainesville', 'Thornton',
            'Roseville', 'Carrollton', 'Coral Springs', 'Stamford', 'Simi Valley', 'Concord', 'Hartford', 'Kent', 'Lafayette', 'Midland',
            'Surprise', 'Denton', 'Victorville', 'Evansville', 'Santa Clara', 'Abilene', 'Athens', 'Vallejo', 'Allentown', 'Norman', 'Beaumont',
            'Independence', 'Murfreesboro', 'Ann Arbor', 'Springfield', 'Berkeley', 'Peoria', 'Provo', 'El Monte', 'Columbia', 'Lansing',
            'Fargo', 'Downey', 'Costa Mesa', 'Wilmington', 'Arvada', 'Inglewood', 'Miami Gardens', 'Carlsbad', 'Westminster', 'Rochester',
            'Odessa', 'Manchester', 'Elgin', 'West Jordan', 'Round Rock', 'Clearwater', 'Waterbury', 'Gresham', 'Fairfield', 'Billings',
            'Lowell', 'San Buenaventura (Ventura)', 'Pueblo', 'High Point', 'West Covina', 'Richmond', 'Murrieta', 'Cambridge', 'Antioch',
            'Temecula', 'Norwalk', 'Centennial', 'Everett', 'Palm Bay', 'Wichita Falls', 'Green Bay', 'Daly City', 'Burbank', 'Richardson',
            'Pompano Beach', 'North Charleston', 'Broken Arrow', 'Boulder', 'West Palm Beach', 'Santa Maria', 'El Cajon', 'Davenport', 'Rialto',
            'Las Cruces', 'San Mateo', 'Lewisville', 'South Bend', 'Lakeland', 'Erie', 'Tyler', 'Pearland', 'College Station', 'Kenosha',
            'Sandy Springs', 'Clovis', 'Flint', 'Roanoke', 'Albany', 'Jurupa Valley', 'Compton', 'San Angelo', 'Hillsboro', 'Lawton',
            'Renton', 'Vista', 'Davie', 'Greeley', 'Mission Viejo', 'Portsmouth', 'Dearborn', 'South Gate', 'Tuscaloosa', 'Livonia', 'New Bedford',
            'Vacaville', 'Brockton', 'Roswell', 'Beaverton', 'Quincy', 'Sparks', 'Yakima', 'Lee\'s Summit', 'Federal Way', 'Carson', 'Santa Monica',
            'Hesperia', 'Allen', 'Rio Rancho', 'Yuma', 'Westminster', 'Orem', 'Lynn', 'Redding', 'Spokane Valley', 'Miami Beach', 'League City',
            'Lawrence', 'Santa Barbara', 'Plantation', 'Sandy', 'Sunrise', 'Macon', 'Longmont', 'Boca Raton', 'San Marcos', 'Greenville',
            'Waukegan', 'Fall River', 'Chico', 'Newton', 'Gresham', 'Cedar Park', 'Coral Springs', 'Clearwater', 'Brockton', 'Parma',
            'New Bedford', 'Murfreesboro', 'Frisco', 'Ogden', 'West Jordan', 'Southfield', 'St. Joseph', 'Danbury', 'Rio Rancho', 'Meridian',
            'Schaumburg', 'Edmond', 'Waltham', 'Lakewood', 'Buena Park', 'Mountain View', 'Brooklyn Park', 'Springdale', 'Paradise', 'Columbia',
            'Largo', 'Bellingham', 'Maple Grove', 'Springfield', 'Taylor', 'Fishers', 'Rapid City', 'Muncie', 'Lynchburg', 'Missoula',
            'Santa Fe', 'Lauderhill', 'Waukesha', 'Redwood City', 'Pharr', 'Kennewick', 'Hemet', 'Quincy', 'Victoria', 'Southaven', 'New Rochelle',
            'Bellingham', 'Pico Rivera', 'Pocatello', 'Pasco', 'Glen Burnie', 'Cheyenne', 'Watsonville', 'Grapevine', 'Bossier City', 'Germantown',
            'Fountain Valley', 'Dearborn Heights', 'Euless', 'North Richland Hills', 'Grand Forks', 'Wheaton', 'Royal Oak', 'Coral Gables', 'Lakewood',
            'Troy', 'Urbandale', 'Sanford', 'Huntington Park', 'La Mesa', 'Taylor', 'Redmond', 'Madera', 'East Orange', 'Redlands', 'Lehi',
            'Chapel Hill', 'Kettering', 'San Clemente', 'Albany', 'Missouri City', 'Kokomo', 'St. Cloud', 'Gadsden', 'National City', 'Edinburg',
            'Conway', 'Sioux City', 'Woodbury', 'St. Peters', 'San Luis Obispo', 'Fitchburg', 'Maplewood', 'Arcadia', 'Santee', 'Tigard',
            'Folsom', 'Bossier City', 'Rosemead', 'Puyallup', 'Monroe', 'Apopka', 'Grand Island', 'West New York', 'Hattiesburg', 'Grand Junction',
            'Cerritos', 'Commerce City', 'Binghamton', 'Dubuque', 'Weymouth Town', 'Wylie', 'Westfield', 'Linden', 'Coppell', 'La Quinta',
            'Midwest City', 'Charlottesville', 'La Crosse', 'Shawnee', 'Lawrence', 'Auburn', 'Sanford', 'Royal Oak', 'Royal Palm Beach', 'Danville',
            'San Bruno', 'Concord', 'Hanford', 'Chelsea', 'Duluth', 'Beaumont', 'Gilroy', 'El Centro', 'Burlington', 'Lakeville', 'DeSoto',
            'Monrovia', 'Joplin', 'Bartlett', 'Collierville', 'San Gabriel', 'Hickory', 'Oakley', 'Placentia', 'Concord', 'La Habra', 'Fairfield',
            'Niagara Falls', 'Farmington', 'Sarasota', 'Texas City', 'Palm Springs', 'Minnetonka', 'Cooper City', 'Pacific Grove', 'Lindenhurst',
            'Hendersonville', 'Revere', 'Lodi', 'Carmel', 'Greenwood', 'Encinitas', 'Naperville', 'Woonsocket', 'Moorhead', 'La Vergne', 'Sherman',
            'Huntington', 'Warwick', 'Coeur d\'Alene', 'Cuyahoga Falls', 'Fort Pierce', 'Apex', 'Palm Desert', 'Cedar Hill', 'Salem', 'Lancaster',
            'Maricopa', 'South San Francisco', 'Orange', 'Janesville', 'Sarasota Springs', 'St. Clair Shores', 'Springboro', 'Hilton Head Island',
            'Bellevue', 'Bellflower', 'Bozeman', 'Littleton', 'Haltom City', 'Menifee', 'Marlborough', 'Oak Lawn', 'La Quinta', 'Hackensack',
            'Casa Grande', 'Haltom City', 'Littleton', 'Grand Forks', 'Cheyenne', 'Manhattan', 'Frisco', 'Yorba Linda', 'Rogers', 'Berwyn',
            'West Allis', 'Edmonds', 'Blue Springs', 'Haverhill', 'Colton', 'Sammamish', 'Hamilton', 'Milford', 'Pontiac', 'Loveland', 'Conroe',
            'Mishawaka', 'Waltham', 'Diamond Bar', 'New Brunswick', 'Turlock', 'Meridian', 'Newnan', 'Tamarac', 'Dunwoody', 'Holyoke', 'Apple Valley',
            'Missoula', 'Spartanburg', 'San Jacinto', 'Lake Elsinore', 'Ankeny', 'Burien', 'Mentor', 'Richland', 'Novi', 'Roswell', 'Union City',
            'Midland', 'Bolingbrook', 'Florence', 'Baytown', 'Yuba City', 'Westland', 'Bristol', 'Dublin', 'Fitchburg', 'Mason', 'Mount Prospect',
            'Encinitas', 'Tigard', 'Livermore', 'Gaithersburg', 'O\'Fallon', 'Rocklin', 'Hillsboro', 'Wheaton', 'Saint Peters', 'Rancho Santa Margarita',
            'Alameda', 'Newark', 'Lake Forest', 'Wauwatosa', 'South Jordan', 'Littleton', 'Summerville', 'Palm Beach Gardens', 'Dublin', 'Rocky Mount',
            'Hanover Park', 'Diamond Bar', 'Coconut Creek', 'Glenview', 'Cleveland Heights', 'North Lauderdale', 'Apopka', 'Peabody', 'Montebello',
            'Margate', 'Palm Harbor', 'Jackson', 'Huntington Park', 'Des Plaines', 'Chicopee', 'Cerritos', 'Wilson', 'Cerritos', 'Cuyahoga Falls',
            'Barnstable Town', 'Goodyear', 'Kannapolis', 'Sioux City', 'Hendersonville', 'Edina', 'Commerce City', 'Rancho Cordova', 'San Marcos',
            'Saginaw', 'Cleveland Heights', 'Gainesville', 'St. Cloud', 'La Crosse', 'San Gabriel', 'East Lansing', 'Clifton', 'Shelton', 'Greenwood',
            'Wauwatosa', 'Kettering', 'Vineland', 'Marlborough', 'San Juan Cap'];
    }

    public function startNewTournament()
    {
        $tournament = new Tournament();
        $tournament->setStatus('new');

        $name = $this->getRandomCity();

        // Check if the city is unique in the database
        while (!$this->isCityUnique($name)) {
            $name = $this->getRandomCity();
        }

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

    private function getRandomCity(): string
    {
        // Get a random city from the array
        $randomIndex = array_rand($this->cities);
        return $this->cities[$randomIndex];
    }

    private function isCityUnique(string $city): bool
    {
        // Check if the city already exists in the database
        $existingTeam = $this->tournamentRepository->findOneBy(['name' => $city]);

        return $existingTeam === null;
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