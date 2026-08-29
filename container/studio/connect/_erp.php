<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <?= $head; ?>
    <link rel="icon" type="image/png" href="<?= themeErp("/assets/images/favicon.png"); ?>"/>
    <link rel="stylesheet" href="<?= themeErp("/assets/style.css"); ?>"/>
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"/>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>
<body>
<!--LOAD-->
<?= $this->insert("views/load", $this->data); ?>

<section class="erp">
    <div class="erp_nav">
        <!--NAV-->
        <?= $this->insert("views/nav", $this->data); ?>
    </div>
    <div class="erp_main">
        <!--HEADER-->
        <?= $this->insert("views/header", $this->data); ?>

        <!--MAIN-->
        <main>
            <div class="main">
                <?= $this->section("content"); ?>
            </div>
        </main>

        <!--OPTOUT-->
        <?php if ($this->section("optout")): ?>
            <?= $this->section("optout"); ?>
        <?php else: ?>
            <?= $this->insert("views/footer_optout", $this->data); ?>
        <?php endif; ?>

        <!--FOOTER-->
        <?= $this->insert("views/footer", $this->data); ?>
    </div>
</section>
<!--MODALS-->
<?= $this->insert("views/modals", $this->data); ?>
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
<script src="<?= themeErp("/assets/scripts.js"); ?>"></script>
<script type="module" src="<?= url('/organic/organic.min.js') ?>"></script>
<script src="<?= url('/organic/compat-v1.js') ?>"></script>

<?= $this->section("scripts"); ?>

</body>
</html>
