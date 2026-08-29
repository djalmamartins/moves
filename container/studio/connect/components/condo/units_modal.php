<article class="main_modal_box">
    <header class="al-center">
        <h1>Cadastro de Unidades</h1>
    </header>
    <div class="main_modal_form">
        <form class="app_form app_modal_main" action="<?= url("/erp/condo/register"); ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="createUnits"/>
            <input type="hidden" name="sub_of" value="<?= $condo->select->id; ?>"/>
            <header>
                <h2>Adicionar Unidades: <span id="exibir-texto"></span></h2>
            </header>

            <div class="group">
                <label>
                    <div><span class="">Pavimento *:</span></div>
                    <select name="order" required>
                        <option value="lower_floor">Pavimento inferior</option>
                        <option value="ground_floor">Pavimento térreo</option>
                        <option value="upper_floor">Pavimento superior</option>
                    </select>
                </label>
                <label>
                    <div><span class="">Apartamentos por andar:</span></div>
                    <input type="number" name="apart_per_floor" placeholder="Ex: 01, 02, 03, 04..."/>
                </label>
            </div>
            <div class="group">
                <label>
                    <div><span class="">Número de:</span></div>
                    <input type="number" id="start" name="start" placeholder="Ex: 01, 101, 1001 e 1101"/>
                </label>
                <label>
                    <div><span class="">Até:</span></div>
                    <input type="number" id="end" name="end" placeholder="Ex: 04, 404, 4004 e 4404"/>
                </label>
            </div>
            <div class="group">
                <label>
                    <div><span class="">Texto antes do número:</span></div>
                    <input type="text" id="before" name="before" placeholder="Ex: T1,  AP,  Bloco 1 etc..."/>
                </label>
                <label>
                    <div><span class="">Texto após o número:</span></div>
                    <input type="text" id="after" name="after" placeholder="Ex: T1,  AP,  Bloco 1 etc..."/>
                </label>
            </div>

            <small>
                <p class="blue">* <strong>Pavimento inferior</strong> sequencial com 2 digitos. Ex: 01,02,03,...</p>
                <p class="blue">* <strong>Pavimento térreo</strong> sequencial com 3 digitos. Ex: 001,002,003,...</p>
                <p class="blue">* <strong>Pavimento superior</strong> leva em consideração o andar. Ex: 101,102,201,...</p>
            </small>

            <div class="app_form_footer">
                <!--                <button class="btn gradient gradient-blue gradient-hover transition icon-plus" data-modalsubmit="true">-->
                <!--                    Salvar-->
                <!--                </button>-->
                <button class="btn gradient gradient-blue gradient-hover transition icon-plus"
                        data-modalsubmitclose="true">Salvar
                </button>
            </div>
        </form>
    </div>
</article>
