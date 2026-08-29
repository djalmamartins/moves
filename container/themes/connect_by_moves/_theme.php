<!DOCTYPE html>
<html lang="pt-br">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <?= $head; ?>
    <link rel="icon" href="<?= site_favicon_url(); ?>"/>
    <link rel="stylesheet" href="<?= theme("/assets/style.css"); ?>"/>
    <link rel="stylesheet" href="<?= theme("/assets/responsive.css"); ?>"/>
    <link rel="stylesheet" href="<?= theme("/assets/pages.css"); ?>"/>
    <link rel="stylesheet" href="<?= theme("/assets/motion.css"); ?>"/>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
</head>
<body>
<!--LOAD-->
<?= $this->insert("views/load", $this->data); ?>

<!--HEADER-->
<?= $this->insert("views/header", $this->data); ?>

<!--MAIN-->
<main>
    <?= $this->section("content"); ?>
</main>

<!--OPTOUT-->
<?php if ($this->section("optout")): ?>
    <?= $this->section("optout"); ?>
<?php else: ?>
    <?= $this->insert("views/footer_optout", $this->data); ?>
<?php endif; ?>

<!--FOOTER-->
<?= $this->insert("views/footer", $this->data); ?>

<!--MODALS-->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-53658515-18"></script>
<?= $this->insert("views/modals", $this->data); ?>
<script src="<?= theme("/assets/scripts.js"); ?>"></script>
<script src="<?= theme("/assets/js/navigation.js"); ?>"></script>
<script src="<?= theme("/assets/js/counters.js"); ?>"></script>
<script src="<?= theme("/assets/js/testimonials.js"); ?>"></script>
<script src="<?= theme("/assets/js/motion.js"); ?>"></script>
<script type="module" src="<?= url('/organic/organic.min.js') ?>"></script>
<script src="<?= url('/organic/compat-v1.js') ?>"></script>
<?= $this->section("scripts"); ?>
</body>
</html>

