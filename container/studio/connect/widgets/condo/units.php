<?php $this->layout("_erp"); ?>

<?php if (!$condo->select): ?>
    <?= $this->insert("views/welcome-condo", $this->data); ?>
<?php else: ?>
    <div class="container">
        <div class="page_main_header">
            <header>
                <h2>Unidades</h2>
                <div>
                    <a class="nav--btn btn gradient gradient-blue gradient-hover transition icon-plus-circle"
                       data-modalopen=".app_modal_register_units">Adicionar</a>
                </div>
            </header>
        </div>

            <?php if (!$condo->units): ?>
                <div class="message info icon-info border">Ainda não existem unidades cadastradas.</div>
            <?php else: ?>

        <section class="flex">
             <?php foreach ($condo->units as $item): ?>
            <div class="four">
                <div class="list-unit">
                    <a class="link link--arrowed" href="<?= url("erp/condo/owner/{$item->id}"); ?>">
                        <strong><?= $item->units; ?></strong>
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
             <?php endforeach; ?>
        </section>

        <?php endif; ?>
    </div>

<?php endif; ?>



