<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <?= $head; ?>
    <link rel="icon" type="image/png" href="<?= theme("/assets/images/favicon.png"); ?>"/>
    <link rel="stylesheet" href="<?= theme("/assets/style.css"); ?>"/>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
</head>
<body>
<!--LOAD-->
<?= $this->insert("pages/load", $this->data); ?>

<!--HEADER-->


<!--MAIN-->
<main>
    <?= $this->section("content"); ?>
</main>

<!--OPTOUT-->
<?php if ($this->section("optout")): ?>
    <?= $this->section("optout"); ?>
<?php else: ?>
    <?= $this->insert("pages/footer_optout", $this->data); ?>
<?php endif; ?>

<!--FOOTER-->
<?= $this->insert("pages/footer", $this->data); ?>

<!--MODALS-->
<?= $this->insert("pages/modals", $this->data); ?>
<script src="<?= theme("/assets/scripts.js"); ?>"></script>
<script type="module" src="<?= url('/organic/organic.min.js') ?>"></script>
<script src="<?= url('/organic/compat-v1.js') ?>"></script>
<?= $this->section("scripts"); ?>
</body>
</html>

