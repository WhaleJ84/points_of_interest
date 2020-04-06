<?php
require('/var/www/html/share/slim4/vendor/autoload.php');
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
$app->setBasePath('/~assign225');

$conn=new PDO('mysql:host=localhost;dbname=assign225', 'assign225', 'umoodahc');
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// Create our PHP renderer object
$view=new \Slim\Views\PhpRenderer('views');

// PointsOfInterest pages
$app->get('/', function (Request $req, Response $res, array $args) use ($conn,$view) {
    unset($_SESSION['pageID']);
    $regions=$conn->prepare('SELECT DISTINCT region FROM pointsofinterest');
    $regions->execute();
    $res=$view->render($res, 'points_of_interest.phtml', ['regions'=>$regions]);
    return $res;
})->setName('root');

$app->get('/get_poi', function (Request $req, Response $res, array $args) use ($conn,$view) {
    unset($_SESSION['pageID']);
    $statement=$conn->prepare('SELECT * FROM pointsofinterest ORDER BY recommended DESC');
    $statement->execute();
    $results=$statement->fetchAll(PDO::FETCH_ASSOC);
    return $res->withJson($results);
});

$app->get('/review/{id}', function (Request $req, Response $res, array $args) use ($conn,$view) {
    if (isset($_SESSION['isadmin'])){
        $reviews=$conn->prepare('SELECT * FROM poi_reviews WHERE poi_id=?');
        $reviews->execute([$args['id']]);
    } else {
        $reviews=$conn->prepare('SELECT * FROM poi_reviews WHERE poi_id=? AND approved=1');
        $reviews->execute([$args['id']]);
    }
    $results=$reviews->fetchAll(PDO::FETCH_ASSOC);
    return $res->withJson($results);
});

$app->get('/region/{region}', function (Request $req, Response $res, array $args) use ($conn,$view) {
    unset($_SESSION['pageID']);
    $statement=$conn->prepare('SELECT * FROM pointsofinterest WHERE region=? ORDER BY recommended DESC');
    $statement->execute([$args['region']]);
    $results=$statement->fetchAll(PDO::FETCH_ASSOC);
    return $res->withJson($results);
});

$app->get('/view/{id}', function (Request $req, Response $res, array $args) use ($conn,$view) {
    unset($_SESSION['pageID']);
    $_SESSION['pageID']=$args['id'];
    $statement=$conn->prepare('SELECT * FROM pointsofinterest WHERE ID=?');
    $statement->execute([$args['id']]);
    $results=$statement->fetchAll(PDO::FETCH_ASSOC);
    return $res->withJson($results);
});

$app->post('/recommend', function (Request $req, Response $res, array $args) use ($conn) {
    $post=$req->getParsedBody();
    if (isset($_SESSION['gatekeeper'])){
        $statement=$conn->prepare('UPDATE pointsofinterest SET recommended=recommended+1 WHERE ID=?');
        $statement->execute([$post['ID']]);
        if (isset($_SESSION['pageID'])) {
            $statement=$conn->prepare('SELECT * FROM pointsofinterest WHERE ID=?');
            $statement->execute([$post['ID']]);
            $results=$statement->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $statement=$conn->prepare('SELECT * FROM pointsofinterest ORDER BY recommended DESC');
            $statement->execute();
            $results=$statement->fetchAll(PDO::FETCH_ASSOC);
        }
        return $res->withJson($results);
    } else {
        $statement=$conn->prepare('SELECT * FROM pointsofinterest ORDER BY recommended DESC');
        $statement->execute();
        $results=$statement->fetchAll(PDO::FETCH_ASSOC);
        return $res->withJson($results);
    }
});

$app->get('/admin', function (Request $req, Response $res, array $args) use ($conn,$view) {
    if (!isset($_SESSION['isadmin'])) {
        echo $routeParser->urlFor('root');
    }
    $reviews=$conn->prepare('SELECT * FROM poi_reviews WHERE approved=0 ORDER BY approved ASC');
    $reviews->execute();
    $res=$view->render($res, 'admin.phtml', ['reviews'=>$reviews]);
    return $res;
});

$app->post('/admin/approve', function (Request $req, Response $res, array $args) use ($conn) {
    $post=$req->getParsedBody();
    $approve=$conn->prepare('UPDATE poi_reviews SET approved=1 WHERE id=?');
    $approve->execute([$post['id']]);
    return $res->withHeader('Location', '/~assign225/admin');
});

$app->get('/add', function (Request $req, Response $res, array $args) use ($view) {
    if (isset($_SESSION['gatekeeper'])){
        $res=$view->render($res, 'add_poi.phtml');
        return $res;
    } else {
        return $res->withHeader('Location', '/~assign225');
    }
});

$app->post('/add_poi', function (Request $req, Response $res, array $args) use ($conn) {
    if (isset($_SESSION['gatekeeper'])){
        $post=$req->getParsedBody();
        $statement=$conn->prepare('INSERT INTO pointsofinterest (name,type,country,region,lon,lat,description,username) VALUES (?,?,?,?,?,?,?,?)');
        $statement->execute([$post['name'],$post['type'],$post['country'],$post['region'],$post['lon'],$post['lat'],$post['description'],$post['username']]);
    }
    return $res->withHeader('Location', '/~assign225');
});

$app->post('/add_review/{id}', function (Request $req, Response $res, array $args) use ($conn) {
    if (isset($_SESSION['gatekeeper'])){
        $post=$req->getParsedBody();
        $statement=$conn->prepare('INSERT INTO poi_reviews (poi_id,review) VALUES (?,?)');
        $statement->execute([$args['id'],$post['review']]);
    }
    $reviews=$conn->prepare('SELECT * FROM poi_reviews WHERE poi_id=? AND approved=1');
    $reviews->execute([$args['id']]);
    $results=$reviews->fetchAll(PDO::FETCH_ASSOC);
    return $res->withJson($results);
});

// User account pages
$app->get('/accounts/{action}', function (Request $req, Response $res, array $args) use ($view) {
    $res=$view->render($res, '/accounts.phtml', ['value'=>$args['action']]);
    return $res;
});

$app->post('/login', function (Request $req, Response $res, array $args) use ($conn) {
    $post=$req->getParsedBody();
    $statement=$conn->prepare('SELECT * FROM poi_users WHERE username=? AND password=?');
    $statement->execute([$post['username'], $post['password']]);
    $row=$statement->fetch(PDO::FETCH_ASSOC);
    $_SESSION['gatekeeper']=$row['username'];
    if ($row['isadmin'] == 1) {
        $_SESSION['isadmin']=1;
        return $res->withHeader('Location', '/~assign225/admin');
    } else {
        return $res->withHeader('Location', '/~assign225');
    }
});

$app->get('/logout', function (Request $req, Response $res, array $args) {
    session_destroy();
    return $res->withHeader('Location', '/~assign225');
});

$app->post('/password', function (Request $req, Response $res, array $args) use ($conn) {
    $post=$req->getParsedBody();
    $statement=$conn->prepare('UPDATE poi_users SET password=? WHERE username=?');
    $statement->execute([$post['password'], $_SESSION['gatekeeper']]);
    return $res->withHeader('Location', '/~assign225');
});

$app->post('/signup', function (Request $req, Response $res, array $args) use ($conn) {
    $post=$req->getParsedBody();
    $statement=$conn->prepare('INSERT INTO poi_users (username,password) VALUES (?,?)');
    $statement->execute([$post['username'], $post['password']]);
    return $res->withHeader('Location', '/~assign225');
});

// Run the Slim app.
$app->run();
