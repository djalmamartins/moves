<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= $head ?>
    <link rel="stylesheet" href="<?= url('/organic/organic.min.css') ?>">
    <link rel="stylesheet" href="<?= url('/organic/compat-v1.css') ?>">
    <link rel="stylesheet" href="<?= themeStudio('/assets/css/admin.css', 'moves_studio') ?>">
    <link rel="icon" href="<?= theme('/assets/images/favicon.png') ?>">
</head>
<body class="studio-login-body">
<div class="ajax_load"><div class="ajax_load_box"><div class="ajax_load_box_circle"></div><p class="ajax_load_box_title">Entrando...</p></div></div>
<?= $this->section('content') ?>
<script src="<?= url('/organic/scripts/jquery.min.js') ?>"></script>
<script src="<?= url('/organic/scripts/jquery-ui.js') ?>"></script>
<script type="module" src="<?= url('/organic/organic.min.js') ?>"></script>
<script src="<?= url('/organic/compat-v1.js') ?>"></script>
<script src="<?= themeStudio('/assets/js/login.js', 'moves_studio') ?>"></script>
<?= $this->section('scripts') ?>
</body>
</html>
