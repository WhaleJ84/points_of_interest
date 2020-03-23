<?php
require('/opt/lampp/htdocs/pointsofinterest/vendor/autoload.php');

// Import classes from the Psr library (standardised HTTP requests and responses)
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

use Slim\Factory\AppFactory;

// Create our app.
$app=AppFactory::create();

// Add routing functionality to Slim.
$app->addRoutingMiddleware();

// Error handling
$app->addErrorMiddleware(true, true, true);

// Set base path to website root directory
$app->setBasePath('/pointsofinterest');

$conn=new PDO('mysql:host=localhost;dbname=assign225','assign225','umoodahc');
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// Create our PHP renderer object
$view=new \Slim\Views\PhpRenderer('views');

// PointsOfInterest pages
$app->get('/', function (Request $req, Response $res, array $args) use($conn, $view){
    $statement = $conn->prepare('SELECT * FROM pointsofinterest ORDER BY recommended DESC');
    $statement->execute();
    $res = $view->render($res, 'pointsofinterest.phtml', ['results'=>$statement]);
    return $res;
});

// Run the Slim app.
$app->run();
?>
