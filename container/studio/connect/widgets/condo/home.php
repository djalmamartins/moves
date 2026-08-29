<?php $this->layout("_erp"); ?>

<?php if (!$condo->select): ?>
    <?php $this->insert("/views/welcome-condo"); ?>
<?php else: ?>

    <div class="container">

        <div class="page_main_header">
            <header>
                <h2><?= $condo->select->condo_name; ?></h2>
            </header>
        </div>

        <section class="dash">
            <div id="hall" class="welcome">
                <p><strong><?= $condo->select->condo_name; ?></strong></p><br>
                <p>CNPJ: <strong class="mask-pj"><?= $condo->select->document; ?></strong></p>
                <p>Síndico:<strong> <?= $condo->select->phone_name; ?></strong></p>
                <p>Celular: <strong class="mask-cell"><?= $condo->select->phone_messages; ?></strong></p>


                <p>
                <a class="link link--arrowed" href="<?= url("/erp/condo/profile"); ?>">
                        <strong>Editar</strong>
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
                </p>
            </div>
            <div id="item-2">

                <p><a class="link link--arrowed" href="/">
                        <strong>Gestão de cobranças</strong>
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
                </p>
                <span>R$ 452.869,12</span>
                <p>91% dos boletos pagos</p>


            </div>
            <div id="item-3">
                <p>
                    <a class="link link--arrowed" href="/">
                        <strong>Gestão de despesas</strong>
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
                </p>
                <span>6 contas</span>
                <p>precisam da sua atenção</p>

            </div>
            <div id="item-4">
                <p>
                    <a class="link link--arrowed" href="/">
                        <strong>Prestação de contas</strong>
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
                </p>
                <span>Julho 2023</span>
                <p>em aberto</p>

            </div>
            <div id="default">
                <p>
                    <a class="link link--arrowed" href="/">
                        <strong>Inadimplência</strong>
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
                </p>
            </div>

            <div id="ticket">
                <p>
                    <a class="link link--arrowed" href="/">
                        <strong>Tickets</strong>
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
                </p>
            </div>
            <div id="units">
                <p>
                    <a class="link link--arrowed" href="<?= url("/erp/condo/units"); ?>">
                        <strong>Unidades</strong>
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
                </p>
            </div>
        </section>

        <section class="solve_easy">


            <div id="item-1">
                <p>
                    <a class="link link--arrowed" href="/">
                        <strong>Suas informações</strong>
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
                </p>
            </div>

            <div id="item-2">
                <p>
                    <a class="link link--arrowed" href="/">
                        <strong>Alteração de titularidade</strong>
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
                </p>
            </div>

            <div id="item-3">
                <p>
                    <a class="link link--arrowed" href="/">
                        <strong>Certidão Negativa de Débito</strong>
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
                </p>
            </div>

            <div id="item-4">
                <p>
                    <a class="link link--arrowed" href="/">
                        <strong>Boletos</strong>
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
                </p>
            </div>

            <div id="item-5">
                <p>
                    <a class="link link--arrowed" href="/">
                        <strong>Preciso de ajuda</strong>
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
                </p>
            </div>

        </section>
    </div>

<?php endif; ?>
