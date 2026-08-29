<?php
$route->namespace("Source\Controllers\App\Connect");
$route->group("/app");

$route->get("/", "App:root");

//dash
$route->get("/dash", "Dash:dash");
$route->post("/dash", "Dash:dash");
$route->get("/dash/auth", "Dash:auth");
$route->post("/dash/auth", "Dash:auth");
$route->get("/dash/home", "Dash:home");
$route->post("/dash/home", "Dash:home");

$route->get("/sair", "Dash:logout");
$route->get("/permissions", "Dash:permissions");
$route->post("/accepted", "Dash:accCookie");

//END APP
$route->namespace("Source\Controllers\App\Connect");
