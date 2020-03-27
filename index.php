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
$app->get('/', function(Request $req, Response $res, array $args) use($conn,$view){
    $regions=$conn->prepare('SELECT DISTINCT region FROM pointsofinterest');
    $regions->execute();
    $statement=$conn->prepare('SELECT * FROM pointsofinterest ORDER BY recommended DESC');
    $statement->execute();
    $res=$view->render($res, 'points_of_interest.phtml', ['results'=>$statement, 'regions'=>$regions]);
    return $res;
});

$app->get('/view/{id}', function(Request $req, Response $res, array $args) use($conn,$view){
    $_SESSION['pageID']=$args['id'];
    $regions=$conn->prepare('SELECT DISTINCT region FROM pointsofinterest');
    $regions->execute();
    $reviews=$conn->prepare('SELECT * FROM poi_reviews WHERE poi_id=?');
    $reviews->execute([$args['id']]);
    $statement=$conn->prepare('SELECT * FROM pointsofinterest WHERE id=? ORDER BY recommended DESC');
    $statement->execute([$args['id']]);
    $res=$view->render($res, 'points_of_interest.phtml', ['results'=>$statement, 'regions'=>$regions, 'reviews'=>$reviews]);
    return $res;
});

$app->post('/recommend', function(Request $req, Response $res, array $args) use($conn){
    $post=$req->getParsedBody();
    // Cannot redirect using $post['ID'] for some reason
    $ID=$post['ID'];
    $statement=$conn->prepare('UPDATE pointsofinterest SET recommended=recommended+1 WHERE ID=?');
    $statement->execute([$ID]);
    return $res->withHeader('Location', "/pointsofinterest/view/$ID");
});

$app->get('/region', function(Request $req, Response $res, array $args) use($conn){
    $get=$req->getQueryParams();
    $region=$get['region'];
    return $res->withHeader('Location', "/pointsofinterest/region/$region");
});

// AFTER THIS IS FIXED, REDO ACCOUNTS SYSTEM TO REMOVE NON-SLIM ROUTING
$app->get('/region/{region}', function(Request $req, Response $res, array $args) use($conn,$view){
    $regions=$conn->prepare('SELECT DISTINCT region FROM pointsofinterest');
    $regions->execute();
    $statement=$conn->prepare('SELECT * FROM pointsofinterest WHERE region=? ORDER BY recommended DESC');
    $statement->execute([$args['region']]);
    $res=$view->render($res, 'points_of_interest.phtml', ['results'=>$statement, 'regions'=>$regions]);
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

$app->get('/view/{id}/review', function(Request $req, Response $res, array $args) use($conn,$view){
    $statement=$conn->prepare('SELECT * FROM pointsofinterest WHERE ID=?');
    $statement->execute([$args['id']]);
    $res=$view->render($res, 'review_poi.phtml', ['results'=>$statement]);
    return $res;
});

$app->post('/review_poi', function(Request $req, Response $res, array $args) use($conn){
    $post=$req->getParsedBody();
    $ID=$post['poi_id'];
    $statement=$conn->prepare('INSERT INTO poi_reviews (poi_id,review) VALUES (?,?)');
    $statement->execute([$ID,$post['review']]);
    return $res->withHeader('Location', "/pointsofinterest/view/$ID");
});

// User account pages
$app->get('/accounts', function(Request $req, Response $res, array $args) use($view){
    $res=$view->render($res, '/accounts.phtml');
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
