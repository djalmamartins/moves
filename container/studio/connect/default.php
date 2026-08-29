<?php
$route->namespace("Source\Public\Erp\Connect");

$route->group("/erp");

$route->get("/", "Erp:root");

//dash
$route->get("/dash", "Erp:dash");
$route->post("/dash", "Erp:dash");

$route->get("/dash/{condominium_id}", "Erp:dash");
$route->post("/dash/{condominium_id}", "Erp:dash");


$route->get("/dash/home", "Dash:home");
$route->post("/dash/home", "Dash:home");

//get
$route->get("/logoff", "Dash:logoff");
$route->get("/permissions", "Dash:permissions");

//post
$route->post("/plug", "Dash:plug");
$route->post("/search", "Dash:search");

//register
$route->get("/register/home", "Register:home");
$route->post("/register/home", "Register:home");

$route->get("/register/condo", "Register:condo");
$route->post("/register/condo", "Register:condo");

$route->get("/register/users", "Register:users");
$route->post("/register/users", "Register:users");

$route->get("/register/users-pj", "Register:usersPj");
$route->post("/register/users-pj", "Register:usersPj");


//condominium
$route->get("/condo/base", "Condo:base");
$route->post("/condo/base", "Condo:base");

$route->get("/condo/home", "Condo:home");
$route->post("/condo/home", "Condo:home");

$route->get("/condo/register", "Condo:register");
$route->post("/condo/register", "Condo:register");

$route->get("/condo/register/{id}", "Condo:register");
$route->post("/condo/register/{id}", "Condo:register");

$route->get("/condo/profile", "Condo:profile");
$route->post("/condo/profile", "Condo:profile");
$route->get("/condo/profile/{edit}", "Condo:profile");
$route->post("/condo/profile/{edit}", "Condo:profile");

$route->get("/condo/address", "Condo:address");
$route->post("/condo/address", "Condo:address");

$route->get("/condo/address/{edit}", "Condo:address");
$route->post("/condo/address/{edit}", "Condo:address");


$route->get("/condo/units", "Condo:units");
$route->post("/condo/units", "Condo:units");

$route->get("/condo/owner/{id}", "Condo:owner");
$route->post("/condo/owner/{id}", "Condo:owner");


//users
$route->get("/users/home", "Users:home");
$route->post("/users/home", "Users:home");
$route->get("/users/home/{search}/{page}", "Users:home");
$route->post("/users/home/{search}/{page}", "Users:home");

//users profile update
$route->get("/users/profile/{user_id}", "Users:profile");
$route->post("/users/profile/{user_id}", "Users:profile");
$route->get("/users/profile/{user_id}/{edit}", "Users:profile");
$route->post("/users/profile/{user_id}/{edit}", "Users:profile");

//users profile update
$route->get("/users/profile/profile_pj/{user_id}", "Users:profilePj");
$route->post("/users/profile/profile_pj/{user_id}", "Users:profilePj");
$route->get("/users/profile/profile_pj/{user_id}/{edit}", "Users:profilePj");
$route->post("/users/profile/profile_pj/{user_id}/{edit}", "Users:profilePj");


$route->get("/users/profile_register", "Users:profileRegister");
$route->post("/users/profile_register", "Users:profileRegister");
$route->get("/users/profile_register/{user_id}", "Users:profileRegister");
$route->post("/users/profile_register/{user_id}", "Users:profileRegister");

$route->get("/users/profile_edit/{user_id}", "Users:profileEdit");
$route->post("/users/profile_edit/{user_id}", "Users:profileEdit");

$route->get("/users/address/{user_id}", "Users:address");
$route->post("/users/address/{user_id}", "Users:address");
$route->get("/users/address/{user_id}/{edit}", "Users:address");
$route->post("/users/address/{user_id}/{edit}", "Users:address");

$route->get("/users/invoices/{user_id}", "Users:invoices");
$route->post("/users/invoices/{user_id}", "Users:invoices");

$route->post("/users/register", "Users:register");
$route->post("/users/register/{user_id}", "Users:register");

$route->post("/users/forget", "Users:forget");

//finance
$route->get("/finance/dash", "Finance:dash");
$route->get("/finance/home", "Finance:home");

$route->get("/finance/income", "Finance:income");
$route->post("/finance/income", "Finance:income");


//post
$route->post("/plug", "Dash:plug");
$route->post("/register", "Users:register");



//END APP
$route->namespace("Source\Public\Erp\Connect");
