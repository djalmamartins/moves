<?php

declare(strict_types=1);

// O host não local impede que os builders legados dos demais aplicativos
// sejam executados ao carregar o autoload. Apenas o Studio recebe a flag.
$_SERVER["HTTP_HOST"] = "assets.moves.invalid";
putenv("MOVESOS_BUILD_ASSETS=1");

require dirname(__DIR__, 2) . "/vendor/autoload.php";

$assets = moves_container_path("studio", "default") . "/assets";
$targets = ["studio.min.css", "studio.min.js"];

foreach ($targets as $target) {
    $path = $assets . "/" . $target;
    if (!is_file($path) || filesize($path) === 0) {
        fwrite(STDERR, "Falha ao gerar {$target}." . PHP_EOL);
        exit(1);
    }

    fwrite(STDOUT, sprintf("Gerado %s (%d bytes)%s", $target, filesize($path), PHP_EOL));
}
