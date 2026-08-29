<article class="main_modal_box">
    <header class="al-center">
        <h1>Cadastro de Usuários</h1>
    </header>
    <div class="main_modal_form">
        <form class="app_form app_modal_main" action="<?= url("/erp/register"); ?>" method="post"
              enctype="multipart/form-data">
            <input type="hidden" name="action" value="create"/>
            <header>
                <h2>Informações</h2>
            </header>
            <label>
                <div><span class="">* Nome Completo:</span></div>
                <input type="text" name="name" placeholder="Primeiro nome" required/>
            </label>
            <div class="gruop">
                <label>
                    <div><span class="">Data de Nascimento:</span></div>
                    <input type="date" name="datebirth" placeholder="dd/mm/yyyy"/>
                </label>
                <label>
                    <div><span class="">* E-mail:</span></div>
                    <input type="email" name="email" placeholder="Melhor e-mail" required/>
                </label>
            </div>

            <div class="gruop">
                <label>
                    <div><span class="">* CPF</span></div>
                    <input type="text" class="mask-doc" name="document" placeholder="CPF do usuário" required/>
                </label>
                <label>
                    <div><span class="">Identidade (RG):</span></div>
                    <input type="text" name="document_rg" placeholder="RG do usuário"/>
                </label>
            </div>

            <div class="gruop">
                <label class="label uploadFile">
                    <div class="gruop_label"><span>CPF - PDF:</span></div>
                    <span for='cpf'  class="btn-full btn-outline btn-blue-outline transition icon-upload filename">Upload CPF</span>
                    <input id='cpf' type="file" name="doc_cpf"/>
                    <?php if(!empty($userEdit->doc_cpf)): ?>
                        <a href="<?= url("/storage/" . $userEdit->doc_cpf); ?>" target="_blank"  class="icon-file-pdf-o">Documento de CPF</a>
                    <?php endif; ?>

                </label>
                <label class="label uploadFile">
                    <div class="gruop_label"><span>RG - PDF: </span></div>
                    <span for='rg'  class="btn-full btn-outline btn-blue-outline icon-upload filename">Upload RG</span>
                    <input id='rg' type="file" name="doc_rg"/>
                    <?php if(!empty($userEdit->doc_rg)): ?>
                        <a href="<?= url("/storage/" . $userEdit->doc_rg); ?>" target="_blank" class="icon-file-pdf-o">Documento de identidade</a>
                    <?php endif; ?>
                </label>
            </div>
            <div class="gruop">
                <label>
                    <div><span class="">* Celular:</span></div>
                    <input type="text" name="phone_cell" class="mask-cell" placeholder="(00) 00000-0000" required/>
                </label>
                <label>
                    <div><span class="">Telefone:</span></div>
                    <input type="text" class="mask-phone" name="phone" placeholder="(00) 0000-0000"/>
                </label>
            </div>
            <div class="gruop">
                <label>
                    <div><span class="">Forma de Contato:</span></div>
                    <select name="despatch">
                        <option value="all">Todos</option>
                        <option value="e-mail">E-mail</option>
                        <option value="whatsapp">Whatsapp</option>
                        <option value="telegram">Telegram</option>
                        <option value="other">Correspondência</option>
                    </select>
                </label>
                <label>
                    <div><span class="">* Nivel de Acesso:</span></div>
                    <select name="level" required>
                        <option value="1">Usuário</option>
                        <option value="2">Administrador</option>
                    </select>
                </label>
            </div>

            <header>
                <h2>Condomínio</h2>
            </header>

            <div class="gruop">
                <label>
                    <div><span class="">Condomínio:</span></div>
                    <select name="cond">
                        <option value="all">Todos</option>
                    </select>
                </label>
                <label>
                    <div><span class="">Unidade</span></div>
                    <select name="enter" required>
                        <option value="1">Inquilino</option>
                    </select>
                </label>
            </div>
            <header>
                <h2>Endereço</h2>
            </header>
            <div class="gruop">
                <label>
                    <div><span class="">* CEP:</span></div>
                    <input type="text" class="mask-cep" name="code" id="cep" placeholder="00000-000" required/>
                </label>
                <label>
                    <div><span class="">* Estado:</span></div>
                    <select name="state" id="uf">
                        <option value="">Selecione</option>
                        <option value="AC">&ofcir; Acre</option>
                        <option value="AL">&ofcir; Alagoas</option>
                        <option value="AP">&ofcir; Amapá</option>
                        <option value="AM">&ofcir; Amazonas</option>
                        <option value="BA">&ofcir; Bahia</option>
                        <option value="CE">&ofcir; Ceará</option>
                        <option value="DF">&ofcir; Distrito Federal</option>
                        <option value="ES">&ofcir; Espírito Santo</option>
                        <option value="GO">&ofcir; Goiás</option>
                        <option value="MA">&ofcir; Maranhão</option>
                        <option value="MT">&ofcir; Mato Grosso</option>
                        <option value="MS">&ofcir; Mato Grosso do Sul</option>
                        <option value="MG">&ofcir; Minas Gerais</option>
                        <option value="PA">&ofcir; Pará</option>
                        <option value="PR">&ofcir; Paraná</option>
                        <option value="PE">&ofcir; Pernambuco</option>
                        <option value="PI">&ofcir; Piauí</option>
                        <option value="RJ">&ofcir; Rio de Janeiro</option>
                        <option value="RN">&ofcir; Rio Grande do Norte</option>
                        <option value="RS">&ofcir; Rio Grande do Sul</option>
                        <option value="RO">&ofcir; Rondônia</option>
                        <option value="RR">&ofcir; Roraima</option>
                        <option value="SC">&ofcir; Santa Catarina</option>
                        <option value="SP">&ofcir; São Paulo</option>
                        <option value="SE">&ofcir; Sergipe</option>
                        <option value="TO">&ofcir; Tocantins</option>
                    </select>
                </label>
            </div>

            <div class="gruop">
                <label>
                    <div><span class="">* Cidade:</span></div>
                    <input type="text" name="city" id="cidade" placeholder="Cidade" required"/>
                </label>
                <label>
                    <div><span class="">* Bairro:</span></div>
                    <input type="text" name="district" id="bairro" placeholder="Bairro" required/>
                </label>
            </div>

            <label>
                <div><span class="">* Logradouro:</span></div>
                <input type="text" name="street" id="rua" placeholder="Avenida, Rua..." required/>
            </label>

            <div class="gruop">
                <label>
                    <div><span class="">* Numero:</span></div>
                    <input type="text" name="number" placeholder="Numero" required/>
                </label>
                <label>
                    <div><span class="">Complemento:</span></div>
                    <input type="text" name="complement" placeholder="Bloco, Apto..."/>
                </label>
            </div>
            <section class="action">
                <!--                <button class="btn gradient gradient-blue gradient-hover transition icon-plus" data-modalsubmit="true">-->
                <!--                    Salvar-->
                <!--                </button>-->
                <button class="btn gradient gradient-blue gradient-hover transition icon-plus"
                        data-modalsubmitclose="true">Salvar
                </button>
            </section>
        </form>
    </div>
</article>