<?php
ob_start();
require __DIR__ . "/vendor/autoload.php";

/**
 * BOOTSTRAP
 */

use Source\Core\Router;
use Source\Core\Session;
use Source\Models\Auth;
use Source\Support\Access;
use Source\Support\AppLogger;

$logger = AppLogger::bootstrap();
$session = new Session();
$route = new Router(url(), ":");


/**
 * WEB ROUTES
 */
$moduleRole = Access::role(Auth::user());
$developerBypass = ($moduleRole->slug ?? null) === "developer";
if (CONF_ACCESS_SITE || $developerBypass) {
    require moves_container_path('web', CONF_VIEW_THEMES) . "/default.php";
} else {
    $route->namespace("Source\\Controllers\\Web\\Connect");
    $route->group("");
    $route->get("/", "Web:unavailable");
    $route->group("/ops");
    $route->get("/{errcode}", "Web:error");
}
$supportRoutes = moves_container_path('web', CONF_VIEW_SUPPORT) . "/default.php";
if ((CONF_ACCESS_SUPPORT || $developerBypass) && is_file($supportRoutes)) {
    require $supportRoutes;
}

/**
 * STUDIO ROUTES
 */
require moves_container_path('studio', 'default') . "/default.php";

/**
 * APP
 */
$appRoutes = moves_container_path('residents', CONF_VIEW_APP) . "/default.php";
if (is_file($appRoutes)) {
    require $appRoutes;
}

/**
 * ERP ROUTES
 */
$erpRoutes = moves_container_path('erp', CONF_VIEW_ERP) . "/default.php";
if (is_file($erpRoutes)) {
    require $erpRoutes;
}

/**
 * ROUTE
 */
$route->dispatch();

/**
 * ERROR REDIRECT
 */
if ($route->error()) {
    AppLogger::log((int)$route->error() >= 500 ? 'error' : 'notice', 'Rota finalizada com erro HTTP', ['event_type' => 'http_error', 'code' => 'HTTP_' . $route->error(), 'http_status' => (int)$route->error(), 'status' => (int)$route->error() >= 500 ? 'open' : 'resolved'], 'http');
    $requestPath = parse_url($_SERVER["REQUEST_URI"] ?? "", PHP_URL_PATH);
    $isStudio = (bool)preg_match("~/studio(?:/|$)~", $requestPath);
    $isSystemArea = (bool)preg_match("~/(?:studio|app|erp|suporte)(?:/|$)~", $requestPath);
    $publicError = !CONF_ACCESS_SITE && !$developerBypass && !$isSystemArea ? "indisponivel" : $route->error();
    $route->redirect(($isStudio ? "/studio/ops/" : "/ops/") . $publicError);
}

ob_end_flush();
