<?php

$environmentFile = dirname(__DIR__, 2) . "/.env";
if (is_file($environmentFile)) {
    $environment = parse_ini_file($environmentFile, false, INI_SCANNER_RAW) ?: [];
    $environmentKeys = [
        "APP_ENV" => "MOVESOS_ENV",
        "DB_HOST" => "MOVESOS_DB_HOST",
        "DB_USER" => "MOVESOS_DB_USER",
        "DB_PASS" => "MOVESOS_DB_PASS",
        "DB_NAME" => "MOVESOS_DB_NAME"
    ];
    foreach ($environmentKeys as $source => $target) {
        if (getenv($target) === false && array_key_exists($source, $environment)) {
            putenv($target . "=" . (string)$environment[$source]);
        }
    }
}

/** MovesOS: configuração de infraestrutura e ambiente. */
$movesEnvironment = getenv("MOVESOS_ENV") ?: "production";

if ($movesEnvironment === "testing") {
    $testDatabase = getenv("MOVESOS_TEST_DB") ?: "movesos_test";
    if (!preg_match("~_test$~", $testDatabase)) throw new RuntimeException("O banco de testes deve terminar com _test.");
    define("CONF_DB_HOST", getenv("MOVESOS_TEST_DB_HOST") ?: "127.0.0.1");
    define("CONF_DB_USER", getenv("MOVESOS_TEST_DB_USER") ?: "root");
    define("CONF_DB_PASS", getenv("MOVESOS_TEST_DB_PASS") ?: "");
    define("CONF_DB_NAME", $testDatabase);
} elseif ($movesEnvironment === "local" || strpos($_SERVER["HTTP_HOST"] ?? "", "localhost") !== false || preg_match("/\.lab(?::\d+)?$/i", $_SERVER["HTTP_HOST"] ?? "")) {
    define("CONF_DB_HOST", getenv("MOVESOS_DB_HOST") ?: "127.0.0.1");
    define("CONF_DB_USER", getenv("MOVESOS_DB_USER") ?: "root");
    define("CONF_DB_PASS", getenv("MOVESOS_DB_PASS") ?: "");
    define("CONF_DB_NAME", getenv("MOVESOS_DB_NAME") ?: "moves_db");
} else {
    $productionDatabase = ["host" => getenv("MOVESOS_DB_HOST") ?: "", "user" => getenv("MOVESOS_DB_USER") ?: "", "pass" => getenv("MOVESOS_DB_PASS") ?: "", "name" => getenv("MOVESOS_DB_NAME") ?: ""];
    if (in_array("", $productionDatabase, true)) throw new RuntimeException("Configure MOVESOS_DB_HOST, MOVESOS_DB_USER, MOVESOS_DB_PASS e MOVESOS_DB_NAME no ambiente de produção.");
    define("CONF_DB_HOST", $productionDatabase["host"]);
    define("CONF_DB_USER", $productionDatabase["user"]);
    define("CONF_DB_PASS", $productionDatabase["pass"]);
    define("CONF_DB_NAME", $productionDatabase["name"]);
}

const CONF_DATE = "d/m/Y";
const CONF_DATE_BR = "d/m/Y H:i:s";
const CONF_DATE_APP = "Y-m-d H:i:s";
const CONF_UPLOAD_DIR = "storage";
const CONF_UPLOAD_IMAGE_DIR = "images";
const CONF_UPLOAD_FILE_DIR = "files";
const CONF_UPLOAD_MEDIA_DIR = "medias";
const CONF_IMAGE_CACHE = CONF_UPLOAD_DIR . "/" . CONF_UPLOAD_IMAGE_DIR . "/cache";
const CONF_IMAGE_SIZE = 2000;
const CONF_IMAGE_QUALITY = ["jpg" => 75, "png" => 5];
const CONF_PASSWD_MIN_LEN = 8;
const CONF_PASSWD_MAX_LEN = 40;
const CONF_PASSWD_ALGO = PASSWORD_DEFAULT;
const CONF_PASSWD_OPTION = ["cost" => 10];
