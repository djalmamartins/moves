<article class="main_modal_box">
    <header class="al-center">
        <h1>Cadastro de Moradores</h1>
    </header>
    <div class="main_modal_form">
        <form class="app_form app_modal_main" action="<?= url("/erp/condo/register"); ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="createOwner"/>
            <input type="hidden" name="sub_of" value="<?= $condo->select->id; ?>"/>
            <input type="hidden" name="units_id" id="inputID"/>

            <header>
                <h2>Unidade nª <strong><span id="idUnits"> </span></strong> </h2>
            </header>

            <label>
                <div><span class="">Morador:</span></div>
                <select name="owner" required>
                    <option value="owner">Proprietário</option>
                    <option value="tenant">Inquilino</option>
                </select>
            </label>
            <label>
                <div><span class="">Usuário:</span></div>
                <input type="text" name="users_id" placeholder="Id ou nome" list="listUser"/>
                <datalist id="listUser">
                    <?php foreach ($users->list as $item): ?>
                        <option value="<?= $item->id; ?> - <?= $item->first_name; ?> <?= $item->last_name; ?>"><?= $item->document; ?></option>
                    <?php endforeach;  ?>
                </datalist>
            </label>
            <section class="action">
                <button class="btn gradient gradient-blue gradient-hover transition icon-plus"
                        data-modalsubmitclose="true">Adicionar
                </button>
            </section>
        </form>
    </div>
</article>
