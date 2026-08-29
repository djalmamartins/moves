<?php $this->layout("layouts/erp"); ?>

<div class="container">
    <div class="page_main_nav">
        <a onclick="history.go(-1);" class="link icon-angle-left">Voltar</a>
    </div>
    <div class="page_main">
        <header>
            <h1>Faturas: <?= $profile->users->fullName(); ?></h1>
        </header>
        <?php $this->insert("components/users/sidebar"); ?>
    </div>


    <?php for ($i = 1; $i <= 10; $i++) : ?>
        <div class="list">
            <div class="list-units"><h2 class="icon-barcode"></h2></div>
            <div class="list-base">
                <div class="list-base-charge">
                    <div class="list-base-name">Djalma</div>
                    <div class="list-base-doc mask-doc">07205714648</div>
                </div>
            </div>
            <div class="list-base">
                <div class="list-base-phone"><h3>R$ 170,00</h3></div>
            </div>
            <div class="list-base">
                <div class="list-base-status">
                    <div class="list-base-charge">
                        <div class="list-base-name red">Em Aberto</div>
                        <div class="list-base-doc mask-date">00/00/0000</div>
                    </div>
                </div>
            </div>
            <div class="list-action">
                <a class="link link--arrowed" href="<?= url("/erp/users/profile/"); ?>">
                    <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" width="28" height="28"
                         viewBox="0 0 32 32">
                        <g fill="none" stroke="var(--erp-primary)" stroke-width="1.5" stroke-linejoin="round"
                           stroke-miterlimit="10">
                            <circle class="arrow-icon--circle" cx="16" cy="16" r="15.12"></circle>
                            <path class="arrow-icon--arrow"
                                  d="M16.14 9.93L22.21 16l-6.07 6.07M8.23 16h13.98"></path>
                        </g>
                    </svg>
                </a>
            </div>
        </div>
    <?php endfor; ?>

</div>
