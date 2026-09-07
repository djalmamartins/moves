<?php

declare(strict_types=1);

// Limites dos bundles iniciais por superfície. A CI impede crescimento silencioso.
return [
    'web' => ['css' => ['max_bytes' => 60_000], 'js' => ['max_bytes' => 210_000]],
    'erp' => ['css' => ['max_bytes' => 170_000], 'js' => ['max_bytes' => 480_000]],
    'residents' => ['css' => ['max_bytes' => 150_000], 'js' => ['max_bytes' => 270_000]],
    'studio' => ['css' => ['max_bytes' => 300_000], 'js' => ['max_bytes' => 180_000]],
];
