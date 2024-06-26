<?php

//configuration CORS

//localhost:3000 access (accès autorisé à l'API)
header("Access-Control-Allow-Origin: http://localhost:3000");
//allowed HTTP methods (méthode autorisées)
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
//Allowed headers (en-tête autorisés)
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-TOKEN");
header("Access-Control-Allow-Credentials: true");


//require controllers
require_once './vparrot-server/controllers/CarsController.php';
require_once './vparrot-server/controllers/ServicesController.php';
require_once './vparrot-server/controllers/SchedulesController.php';
require_once './vparrot-server/controllers/TestimoniesController.php';
require_once './vparrot-server/controllers/UsersController.php';
require_once './vparrot-server/controllers/AuthController.php';
require_once './vparrot-server/controllers/ContactController.php';
require_once './vparrot-server/controllers/RoleController.php';
require_once './vparrot-server/controllers/TreatedContactController.php';

require_once './vparrot-server/repository/ContactRepository.php';
require_once './vparrot-server/repository/RejectedTestimoniesRepository.php';
require_once './vparrot-server/repository/ServicesRepository.php';
require_once './vparrot-server/repository/CarsRepository.php';
require_once './vparrot-server/repository/SchedulesRepository.php';
require_once './vparrot-server/repository/TestimoniesRepository.php';
require_once './vparrot-server/repository/UserRepository.php';
require_once './vparrot-server/repository/RoleRepository.php';
require_once './vparrot-server/repository/TreatedContactRepository.php';

require_once './vparrot-server/util/EmailService.php';

require_once './vparrot-server/models/RejectedTestimonies.php';
require_once './vparrot-server/models/Testimonies.php';
require_once './vparrot-server/models/AuthModel.php';
require_once './vparrot-server/models/Users.php';
require_once './vparrot-server/models/Role.php';
require_once './vparrot-server/Validator/Validator.php';


if($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

//Instanciation des repository
$contactRepo = new contactRepository();
$treatedContactRepo = new TreatedContactRepository($contactRepo);
$usersRepo = new UserRepository();
$rolesRepo = new RoleRepository();
$servicesRepo = new ServicesRepository();
$carRepo = new CarsRepository();
$schedulesRepo = new SchedulesRepository();
$testimoniesRepo = new TestimoniesRepository();

//Instanciation du service d email
$emailService = new EmailService();

//Instanciation des models
$authModel = new AuthModel();
$validator = new Validator();


//instanciations des controllers
$controllers = [
    'treatedContact' => new TreatedContactController($treatedContactRepo, $validator, $authModel),
    'roles' => new RoleController($rolesRepo, $validator, $authModel),
    'contact' => new ContactController($contactRepo, $validator),
    'cars' => new CarsController($carRepo, $validator),
    'services' => new ServicesController($servicesRepo, $validator),
    'schedules' => new SchedulesController($schedulesRepo, $validator, $authModel),
    'testimonies' => new TestimoniesController($testimoniesRepo, $validator, $authModel),
    'users' => new UsersController($usersRepo, $validator, $emailService, $authModel),
    'auth' => new AuthController($validator, $authModel, $usersRepo),
];


//Définition des routes
$routes = [
    'GET' => [

        '/vparrot/contact/treated' => [$controllers['treatedContact'], 'getAllTreatedContactList'],
        '#^/vparrot/cars/details/(\d+)$#' => [$controllers['cars'], 'getFullCarDetails'],
        '/vparrot/cars/filtered' => [$controllers['cars'], 'getFilteredCarsList'],
        '/vparrot/cars/distinct-transmission-types' => [$controllers['cars'], 'getAllDistinctTransmissionTypes'],
        '/vparrot/cars/distinct-fuel-types' => [$controllers['cars'], 'getAllDistinctFuelTypes'],
        '/vparrot/cars/distinct-models' => [$controllers['cars'], 'getAllDistinctModels'],
        '/vparrot/cars/distinct-brands' => [$controllers['cars'], 'getAllDistinctBrands'],
        '/vparrot/cars/briefs' => [$controllers['cars'], 'getCarBriefDetails'],
        '/vparrot/services' => [$controllers['services'], 'getAllServicesListGroupedByType' ],
        '/vparrot/schedules' => [$controllers['schedules'], 'getSchedulesList'],
        '/vparrot/testimonies' => [$controllers['testimonies'], 'getAllTestimoniesList'],
        '/vparrot/users' => [$controllers['users'], 'getAllUsersList'],
        '/vparrot/roles' => [$controllers['roles'], 'getAllRolesList'],
        '/vparrot/check-session' => [$controllers['auth'], 'checkSession'],
        '/vparrot/contact' => [$controllers['contact'], 'getContactList'],

    ],

    'POST' => [ 
        '/vparrot/users' => [$controllers['users'], 'addThisUser'],  
        '/vparrot/testimonies' => [$controllers['testimonies'], 'createTestimony'],
        '/vparrot/change-password' => [$controllers['users'], 'resetPassword'],
        '/vparrot/request-password-reset' => [$controllers['users'], 'requestPasswordReset'],  
        '/vparrot/login' => [$controllers['auth'], "login"],
        '/vparrot/logout' => [$controllers['auth'], "logout"],
        '/vparrot/contact' => [$controllers['contact'], 'createContact'],
        '/vparrot/contact/treat' => [$controllers['treatedContact'], 'treatThisContact'],

    ],

    'PUT' => [
      '/vparrot/users/update' => [$controllers['users'], 'updateThisUser'],
      '#^/vparrot/testimonies/(\d+)/approve$#' => [$controllers['testimonies'], 'approveThisTestimony'],
      '#^/vparrot/testimonies/(\d+)/reject$#' => [$controllers['testimonies'], 'rejectThisTestimony'],
      '#^/vparrot/schedules/(\d+)/update$#' => [$controllers['schedules'], 'updateSchedule'],

    ],

    'DELETE' => [
      '#^/vparrot/users/(\d+)/delete$#' => [$controllers['users'], 'deleteThisUser'],
      '#^/vparrot/testimonies/delete$#' => [$controllers['testimonies'], 'deleteThisTestimony'],

    ]

];


//extractionde l uri de la requête
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);



$requestMethod = $_SERVER['REQUEST_METHOD'];

$foundRoute = false;

//Vérification des routes exactes
if(isset($routes[$requestMethod][$uri])) {
  $routes[$requestMethod][$uri]();
  $foundRoute = true;
}

//Si la route n'est pas exacre contrôle les routes avec les regex
if (!$foundRoute) {

    foreach($routes[$requestMethod] as $pattern =>$function) {    
        if (preg_match($pattern, $uri, $matches)) {
   
            array_shift($matches); 
            $function(...$matches);
            $foundRoute = true;
            break;
        }
    }
}

//Retourne une erreur 404 si la route n est pas trouvée
if(!$foundRoute) {
  header('HTTP/1.1 404 Not Found');
  echo json_encode(['status' => 'error', 'message' => 'Route not found']);
}

