<?php

session_start();
require_once("connect.php");
require_once("utils.php");
require_once("../vendor/autoload.php");

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

$loader = new FilesystemLoader(__DIR__ . '/../templates');
$twig = new Environment($loader);

$pathInfo = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
if (empty($pathInfo)) {
    $pathInfo = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : '';
}

// echo $pathInfo;

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if ($pathInfo == '/movies.html.twig') {

        if (isset($_GET['title'])) {
            $user_input = $_GET['title'];
            $filter = 'title';
        } else if (isset($_GET['synopsis'])) {
            $user_input = $_GET['synopsis'];
            $filter = 'synopsis';
        } else if (isset($_GET['duration'])) {
            $user_input = $_GET['duration'];
            $filter = 'duration';
        } else if (isset($_GET['released_year'])) {
            $user_input = $_GET['released_year'];
            $filter = 'released_year';
        } else {
            $user_input = NULL;
            $filter = NULL;
        }

        $movies = get_movies($user_input, $filter);

        echo $twig->render('movies.html.twig', ['movies' => $movies]);
    } else if ($pathInfo == '/actors.html.twig') {

        if (isset($_GET['last_name'])) {
            $user_input = $_GET['last_name'];
            $filter = 'last_name';
        } else if (isset($_GET['name'])) {
            $user_input = $_GET['name'];
            $filter = 'name';
        } else {
            $user_input = NULL;
            $filter = 'NULL';
        }

        $actors = get_actors($user_input, $filter);
        echo $twig->render('actors.html.twig', ['actors' => $actors]);
    } else if ($pathInfo == '/directors.html.twig') {

        if (isset($_GET['last_name'])) {
            $user_input = $_GET['last_name'];
            $filter = 'last_name';
        } else if (isset($_GET['name'])) {
            $user_input = $_GET['name'];
            $filter = 'name';
        } else {
            $user_input = NULL;
            $filter = 'NULL';
        }

        $directors = get_directors($user_input, $filter);
        echo $twig->render('directors.html.twig', ['directors' => $directors]);
    } else if ($pathInfo == '/genres.html.twig') {

        if (isset($_GET['name'])) {
            $user_input = $_GET['name'];
            $filter = 'name';
        } else {
            $user_input = NULL;
            $filter = 'NULL';
        }

        $genres = get_genres($user_input, $filter);
        echo $twig->render('genres.html.twig', ['genres' => $genres]);
    } else if ($pathInfo == '/' or $pathInfo == '') {

        if (isset($_SESSION['id'])) {
            $user = true;
            echo $twig->render('index.html.twig', ['user' => $user]);
        } else {

            echo $twig->render('index.html.twig');
        }
    } else if ($pathInfo == "/accesso.html.twig") {

        echo $twig->render('accesso.html.twig');
    } else if ($pathInfo == "/registrazione.html.twig") {

        echo $twig->render('registrazione.html.twig');
    } else if ($pathInfo == "/suggerimenti.html.twig") {

        $matrix = build_matrix();
        $most_similar_user = find_most_similar_user($matrix, $_SESSION['id']);

        // id del film consigliato
        $recommended_movie_id = recommend_film($matrix, $_SESSION['id'], $most_similar_user);

        $query_film_suggerito = "SELECT * FROM movie WHERE id = " . $recommended_movie_id;
        $result_film_suggerito = execute_query($query_film_suggerito);
        $recommended_movie = mysqli_fetch_assoc($result_film_suggerito);

        echo $twig->render('suggerimenti.html.twig', ['recommended_movie' => $recommended_movie]);

        /*http_response_code(200);
        header("Content-Type: application/json");
        echo json_encode([
            "status" => 200,
            "message" => "OK",
            "payload" => $recommended_movie
        ]);*/
    } else {

        http_response_code(404);
        header("Content-Type: application/json");
        echo json_encode([
            "status" => 404,
            "message" => "Page not found",
            "payload" => []
        ]);
    }
} else {

    http_response_code(405);
    header("Content-Type: application/json");
    echo json_encode([
        "status" => 405,
        "message" => "Method not allowed",
        "payload" => []
    ]);
}

exit;
