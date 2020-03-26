<?php
require('/opt/lampp/htdocs/pointsofinterest/vendor/autoload.php');
session_start();

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
$app->get('/', function(Request $req, Response $res, array $args) use($conn, $view){
    $statement=$conn->prepare('SELECT * FROM pointsofinterest ORDER BY recommended DESC');
    $statement->execute();
    $res=$view->render($res, 'points_of_interest.phtml', ['results'=>$statement]);
    return $res;
});

$app->get('/add', function(Request $req, Response $res, array $args) use($view){
    $res=$view->render($res, 'add_poi.phtml');
    return $res;
});

$app->post('/add_poi', function(Request $req, Response $res, array $args) use($conn){
    $post=$req->getParsedBody();
    $statement=$conn->prepare('INSERT INTO pointsofinterest (name,type,country,region,lon,lat,description,username) VALUES (?,?,?,?,?,?,?,?)');
    $statement->execute([$post['name'],$post['type'],$post['country'],$post['region'],$post['lon'],$post['lat'],$post['description'],$post['username']]);
    return $res->withHeader('Location', '/pointsofinterest');
});

// User account pages
$app->get('/accounts', function(Request $req, Response $res, array $args) use($view){
    $res=$view->render($res, 'accounts.phtml');
    return $res;
});

$app->post('/login', function(Request $req, Response $res, array $args) use($conn){
    $post=$req->getParsedBody();
    $statement=$conn->prepare('SELECT * FROM poi_users WHERE username=? AND password=?');
    $statement->execute([$post['username'], $post['password']]);
    $row=$statement->fetch(PDO::FETCH_ASSOC);
    $_SESSION['gatekeeper']=$row['username'];
    return $res->withHeader('Location', '/pointsofinterest');
});

$app->get('/logout', function(Request $req, Response $res, array $args){
    session_destroy();
    return $res->withHeader('Location', '/pointsofinterest');
});

$app->post('/password', function(Request $req, Response $res, array $args) use($conn){
    $post=$req->getParsedBody();
    $statement=$conn->prepare('UPDATE poi_users SET password=? WHERE username=?');
    $statement->execute([$post['password'], $_SESSION['gatekeeper']]);
    return $res->withHeader('Location', '/pointsofinterest');
});

$app->post('/signup', function(Request $req, Response $res, array $args) use($conn){
    $post=$req->getParsedBody();
    $statement=$conn->prepare('INSERT INTO poi_users (username,password) VALUES (?,?)');
    $statement->execute([$post['username'], $post['password']]);
    return $res->withHeader('Location', '/pointsofinterest');
});

// Run the Slim app.
$app->run();
?>
