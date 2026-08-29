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
    require __DIR__ . "/container/themes/" . CONF_VIEW_THEMES . "/default.php";
}
$supportRoutes = __DIR__ . "/container/themes/" . CONF_VIEW_SUPPORT . "/default.php";
if ((CONF_ACCESS_SUPPORT || $developerBypass) && is_file($supportRoutes)) {
    require $supportRoutes;
}

/**
 * STUDIO ROUTES
 */
require __DIR__ . "/container/studio/moves_studio/default.php";

/**
 * APP
 */
$appRoutes = __DIR__ . "/container/studio/" . CONF_VIEW_APP . "/default.php";
if (is_file($appRoutes)) {
    require $appRoutes;
}

/**
 * ERP ROUTES
 */
$erpRoutes = __DIR__ . "/container/studio/" . CONF_VIEW_ERP . "/default.php";
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
    $route->redirect(($isStudio ? "/studio/ops/" : "/ops/") . $route->error());
}

ob_end_flush();
