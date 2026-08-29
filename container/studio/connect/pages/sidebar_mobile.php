<?php

use Source\Models\Auth;

?>
<ul class="app_nav_mobile">
    <li>><a href="<?= url("/app/"); ?>" title="Suas informações" class="icon-home">Dashboard</a></h2></li>
    <li><a href="<?= url("/app/perfil/home"); ?>" title="Suas informações" class="icon-user">Suas Informações</a></h2>
        <ul>
            <li><a href="<?= url("/app/perfil/home"); ?>" title="Editar perfil">Gerenciar perfil</a></li>
        </ul>
    </li>

    <li><a href="<?= url("/app/clube/home"); ?>" title="Associações" class="icon-star">Associações</a>
        <ul>
            <li><a title="Cadastrar Clube" data-modalopen=".app_modal_clube">Cadastrar Clube</a></li>
            <li><a href="<?= url("/app/clube/home"); ?>" title="Listagem" >Listagem</a></li>
        </ul>
    </li>

    <li><a href="<?= url("/app/pombal/home"); ?>" title="Pombal" class="icon-twitter">Pombal</a>
        <ul>
            <li><a title="Cadastrar pombal" data-modalopen=".app_modal_pombal">Cadastrar pombal</a></li>
            <li><a href="<?= url("/app/pombal/home"); ?>" title="Listagem">Listagem</a></li>
        </ul>
    </li>

    <li><a href="<?= url("/app/certificado/home"); ?>" title="Certificado" class="icon-tag">Certificado</a>
        <ul>
            <li><a href="<?= url("/app/certificado/home"); ?>" title="Lista de certificados">Lista de certificados</a></li>
        </ul>
    </li>

    <?php $user = Auth::user(); if ($user->level >= 5) { ?>
        <li><a href="<?= url("/studio"); ?>" title="Studio" class="icon-flask">Studio</a></li>
    <?php } else { ?>

    <?php } ?>

    <li><a href="<?= url("/"); ?>" title="Voltar ao Site" class="icon-sign-in">Site</a></li>

    <li><a href="<?= url("/app/sair"); ?>" title="Sair" class="icon-sign-out">Sair</a></li>
</ul>
