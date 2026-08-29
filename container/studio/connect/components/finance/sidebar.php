<header class="app_header">
    <div class="app_header_finance">

        <?php if ($app == 'finance/home'): ?>
            <span><ion-icon name="stats-chart-outline"></ion-icon>  <?= hoje(); ?> </span>
        <?php elseif ($app == 'finance/income'): ?>
            <span><ion-icon name="stats-chart-outline"></ion-icon>  <strong>Gestão de cobranças</strong> </span>
        <?php elseif ($app == 'finance/expense'): ?>
            <span><ion-icon name="stats-chart-outline"></ion-icon>  Gestão de despesa </span>
        <?php endif; ?>

    </div>
    <ul class="app_header_widget">
        <li class="radius wallet income transition icon-plus-circle" data-modalopen=".app_modal_condo">Receita</li>
        <li class="radius wallet expense transition icon-minus-circle" data-modalopen=".app_modal_condo">Despesa</li>
        <li class="radius wallet transition icon-filter"> <?= (session()->has("walletfilter") ? (new \Source\Models\Erp\AppWallet())->findById(session()->walletfilter)->wallet : "Saldo Geral"); ?>
            <ul>
                <?php if (session()->has("walletfilter")): ?>
                    <li class="radius icon-briefcase" data-walletfilter="<?= url("/app/dash"); ?>"
                        data-wallet="all">Saldo Geral
                    </li>
                <?php endif; ?>

                <?php
                $userId = user()->id;
                $wallets = (new \Source\Models\Erp\AppWallet())
                    ->find("condominium_id = :condo", "condo={$condo->select->id}")
                    ->order("wallet")
                    ->fetch(true);

                foreach ($wallets as $walletIt):
                    if (!session()->has("walletfilter") || $walletIt->id != session()->walletfilter):
                        ?>
                        <li class="radius icon-suitcase" data-walletfilter="<?= url("/app/dash"); ?>"
                            data-wallet="<?= $walletIt->id; ?>"><?= $walletIt->wallet; ?></li>
                    <?php
                    endif;
                endforeach;
                ?>
            </ul>
        </li>
    </ul>
</header>

<div class="page_main">
    <div class="main-filtro">
        <div class="main-status">
            <article class="status">
                <div class="status--charge">
                    <div class="value--info icon-circle green"><span>Recebido</span></div>
                    <div class="value"><?= str_price(($count->income ?? 0)); ?></div>
                </div>
            </article>

            <article class="status">
                <div class="status--charge">
                    <div class="value--info icon-circle blue"><span>À receber</span></div>
                    <div class="value"><?= str_price(($count->cReceive ?? 0)); ?></div>
                </div>
            </article>

            <article class="status">
                <div class="status--charge">
                    <div class="value--info icon-circle red"><span>Despesa</span></div>
                    <div class="value"><?= str_price(($count->ctExpense ?? 0)); ?></div>
                </div>
            </article>

            <article class="status">
                <div class="status--charge">
                    <div class="value--info icon-circle orange"><span>À pagar</span></div>
                    <div class="value"><?= str_price(($count->cfExpense ?? 0)); ?></div>
                </div>
            </article>

            <article class="status">
                <div class="status--charge">
                    <div class="value--info icon-circle green-hover"><span> Caixa</span></div>
                    <div class="value"><?= str_price(($count->cash ?? 0)); ?></div>
                </div>
            </article>

            <article class="status">
                <div class="status--charge">
                    <div class="value--info icon-circle yellow"><span> Inadimplência</span></div>
                    <div class="value"><?= str_price(($wallet->owing ?? 0)); ?></div>
                </div>
            </article>


        </div>
        <div class="main-geral">
            <article class="status">
                <div class="status--charge">
                    <div class="value--info icon-money <?= (!empty($wallet->wallet) && $wallet->wallet >= 0 ? "green" : "red"); ?>"><span class="<?= (!empty($wallet->wallet) && $wallet->wallet >= 0 ? "green" : "red"); ?>"> Saldo Geral</span></div>
                    <div class="value <?= (!empty($wallet->wallet) && $wallet->wallet >= 0 ? "green" : "red"); ?>"><?= str_price(($wallet->wallet ?? 0)); ?></div>
                </div>
            </article>
        </div>
    </div>
</div>