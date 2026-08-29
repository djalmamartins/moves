<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= $head ?>
    <link rel="stylesheet" href="<?= url('/organic/organic.min.css') ?>">
    <link rel="stylesheet" href="<?= url('/organic/compat-v1.css') ?>">
    <link rel="stylesheet" href="<?= themeStudio('/assets/css/admin.css', 'default') ?>">
    <link rel="icon" href="<?= theme('/assets/images/favicon.png') ?>">
</head>
<body class="studio-login-body">
<a class="org-skip-link" href="#main-content">Ir para o conteúdo</a>
<div class="ajax_load"><div class="ajax_load_box"><div class="ajax_load_box_circle"></div><p class="ajax_load_box_title">Entrando...</p></div></div>
<main id="main-content" tabindex="-1"><?= $this->section('content') ?></main>
<script src="<?= url('/container/shared/assets/vendor/scripts/jquery.min.js') ?>"></script>
<script src="<?= url('/container/shared/assets/vendor/scripts/jquery-ui.js') ?>"></script>
<script src="<?= url('/organic/organic.global.min.js') ?>"></script>
<script src="<?= url('/organic/compat-v1.js') ?>"></script>
<script src="<?= themeStudio('/assets/js/login.js', 'default') ?>"></script>
<?= $this->section('scripts') ?>
</body>
</html>
