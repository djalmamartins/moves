<?php

$route->namespace("Source\\Public\\Web\\Support");
$route->group("/suporte");
$route->get("/", "Support:home");
$route->get("/buscar", "Support:search");
$route->post("/avaliar/{id}", "Support:vote");
$route->get("/{section}", "Support:category");
$route->get("/{section}/{article}", "Support:article");
