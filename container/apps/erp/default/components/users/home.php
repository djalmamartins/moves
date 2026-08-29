<?php $this->layout("layouts/erp"); ?>

<div class="container">
    <div class="page_main_header">
        <header>
            <h2>Usuários</h2>
            <div>
                <a class="nav--btn btn gradient gradient-blue gradient-hover transition icon-plus-circle"
                   href="<?= url("/erp/register/users")?>">Adicionar
                </a>
            </div>
        </header>

    </div>

    <form action="<?php url('/erp/users/home'); ?>" class="search_form_ al-top al-bottom">
        <input type="search" id="search" name="s">
        <label for="search"></label>
    </form>



    <?php if (!$users): ?>

    <div class="message info icon-info">Ainda não existem usuários cadastrados.
        <a class="link link--arrowed" href="<?= url("/erp/users/profile")?>"> Adicionar Usuário
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
    <?php else: ?>

    <?php foreach ($users as $item): ?>
        <div class="list">
            <div class="list-units"><h2><?= sprintf("%04s",$item->id); ?></h2></div>
            <div class="list-base">
                <div class="list-base-charge">
                    <?php if($item->type == "pf"): ?>
                    <div class="list-base-name"><?= $item->fullname(); ?></div>
                    <div class="list-base-doc mask-doc"><?= $item->document; ?></div>
                    <?php else: ?>
                        <div class="list-base-name"><?= $item->corporate_name; ?></div>
                        <div class="list-base-doc mask-pj"><?= $item->document; ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="list-base">
                <div class="list-base-phone icon-whatsapp mask-cell"><?= $item->phone_cell; ?></div>
            </div>
            <div class="list-base">
                <div class="list-base-status">
                    <div class="list-base-charge">
                        <div class="list-base-name <?= ($item->status == 'confirmed' ? 'green' : 'red'); ?>"><?= ($item->status == 'confirmed' ? 'Ativo' : 'Inativo'); ?></div>
                        <div class="list-base-doc mask-date"><?= date_br_fmt($item->created_at); ?></div>
                    </div>
                </div>
            </div>
            <div class="list-action">
                <a class="link link--arrowed" href="<?= url("/erp/users/profile/{$item->id}"); ?>">
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
        <?= $paginator; ?>
    <?php endif; ?>
</div>
