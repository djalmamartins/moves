<?php $this->layout("_erp"); ?>


<?php if (!$condo->select): ?>
    <?= $this->insert("views/welcome-condo", $this->data); ?>
<?php else: ?>
    <div class="container">

    <div class="page_main_header">
        <header>
            <h2>Unidade: <?= $condo->unit->units; ?></h2>
            <div>
                <a class="nav--btn btn btn-blue-outline transition icon-angle-left"
                   href="<?= url("/erp/condo/units"); ?>">Voltar</a>
                <a class="nav--btn btn gradient gradient-blue gradient-hover transition icon-plus-circle"
                   data-modalopen=".app_modal_register_owner" data-units="<?= $condo->unit->units; ?>"
                   data-units_id="<?= $condo->unit->id; ?>">Adicionar</a>
            </div>
        </header>
    </div>
    <?php if (!$condo->owner): ?>
        <br>
        <div class="message info icon-info border">Ainda não existe morador cadastrado.</div>
    <?php else: ?>
        <?php foreach ($condo->owner as $item): ?>

            <?php $name = (new \Source\Models\User())->find("id = :id", "id={$item->users_id}")->fetch(); ?>

            <div class="list">
                <div class="list-units"><h2 class="icon-building"><?= $condo->unit->units; ?></h2></div>

                <div class="list-base">
                    <div class="list-base-charge">
                        <div class="list-base-name"><?= $name->first_name; ?> <?= $name->last_name; ?></div>
                        <div class="list-base-doc"><?= ($item->owner == "owner" ? "Proprietário" : "Inquilino"); ?></div>
                    </div>
                </div>

                <div class="list-base">
                    <div class="list-base-phone mask-cell icon-whatsapp"><?= $name->phone_cell; ?></div>
                </div>

                <div class="list-base">
                    <div class="list-base-status">
                        <div class="list-base-charge">
                            <div class="list-base-name <?= ($item->status == "confirmed" ? "green" : "red"); ?>"><?= ($item->status == "confirmed" ? "Ativo" : "Inativo"); ?></div>
                            <div class="list-base-doc mask-date"><?= date_br_fmt($item->created_at); ?></div>
                        </div>
                    </div>
                </div>
                <div class="list-units">
                    <span class="remove_link icon-user-times red"
                       data-post="<?= url("/erp/condo/register/{$item->id}"); ?>"
                       data-action="edit"
                       data-confirm="ATENÇÃO: Tem certeza que deseja excluir o morador desta unidade? Essa ação não pode ser feita!"
                       data-id="<?= $item->id; ?>"
                       data-units="<?= $item->units_id; ?>">
                    </span>
                </div>
            <div class="list-action">
                <a class="link link--arrowed"
                   href="https://www.localhost/connect/erp/users/profile/<?= $item->users_id; ?>">
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
    <?php endif; ?>


    </div>
<?php endif; ?>


<div class="container">
    <div class="page_main_header">
        <header>
            <h2>Boletos</h2>
        </header>
    </div>
    <?php for ($i = 1; $i <= 4; $i++): ?>
        <div class="list">
            <div class="list-units"><h2 class="icon-barcode">102</h2></div>
            <div class="list-base">
                <div class="list-base-charge">
                    <div class="list-base-name">Vencimento em 10/0<?= $i ?>/2023</div>
                    <div class="list-base-doc">Atrasado há 25 dias</div>
                </div>
            </div>
            <div class="list-base">
                <div class="list-base-status">
                    <div class="list-base-charge">
                        <div class="list-base-name green">Ativo</div>
                        <div class="list-base-doc mask-date">21/11/2023</div>
                    </div>
                </div>
            </div>
            <div class="list-base">
                <div class="list-base-phone">R$ 170,00</div>
            </div>
            <div class="list-action">
                <a class="link link--arrowed" href="https://www.localhost/connect/erp/users/profile/27">
                    <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" width="28" height="28"
                         viewBox="0 0 32 32">
                        <g fill="none" stroke="var(--erp-primary)" stroke-width="1.5" stroke-linejoin="round"
                           stroke-miterlimit="10">
                            <circle class="arrow-icon--circle" cx="16" cy="16" r="15.12"></circle>
                            <path class="arrow-icon--arrow" d="M16.14 9.93L22.21 16l-6.07 6.07M8.23 16h13.98"></path>
                        </g>
                    </svg>
                </a>
            </div>
        </div>
    <?php endfor; ?>
</div>