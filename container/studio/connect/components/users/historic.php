<?php

$this->layout("layouts/erp"); ?>
<article class="main">
    <div class="container">
        <section class="main_page">
            <header>
                <div class="nav--title">
                    <h2>Informações do usuário: <?= $userEdit->fullName(); ?></h2>
                </div>

                <a class="nav--btn btn gradient gradient-blue gradient-hover transition icon-reply-all"
                   onclick="history.go(-1);">Voltar
                </a>
            </header>
            <?php $this->insert("components/users/sidebar"); ?>

        </section>
    </div>
</article>
