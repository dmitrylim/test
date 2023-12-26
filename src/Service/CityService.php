<?php

namespace App\Service;

use App\Repository\TournamentRepository;

class CityService
{
    private TournamentRepository $tournamentRepository;
    private array $cities;

    public function __construct(TournamentRepository $tournamentRepository)
    {
        $this->tournamentRepository = $tournamentRepository;
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

    public function getRandomUniqueCity(): string
    {
        do {
            // Get a random city from the array
            $randomCity = $this->getRandomCity();

            // Check if the city already exists in the database
            $isUnique = $this->isCityUnique($randomCity);

        } while (!$isUnique);

        return $randomCity;
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
}