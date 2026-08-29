<?php $this->layout("layouts/erp"); ?>


<div class="container">

    <div class="page_main_header">

            <header>
                <h2>Endereço - <?= $profile->users->fullName(); ?></h2>
                <?php $this->insert("components/users/sidebar"); ?>
            </header>

    </div>

    <?php if (!$profile->address): ?>
        <div class="message info icon-info main_top">Ainda não existem um endereço cadastrado.
            <a class="link link--arrowed" data-modalopen=".app_modal_register_user"> Adicionar
                <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" width="28" height="28"
                     viewBox="0 0 32 32">
                    <g fill="none" stroke="var(--info)" stroke-width="1.5" stroke-linejoin="round"
                       stroke-miterlimit="10">
                        <circle class="arrow-icon--circle" cx="16" cy="16" r="15.12"></circle>
                        <path class="arrow-icon--arrow"
                              d="M16.14 9.93L22.21 16l-6.07 6.07M8.23 16h13.98"></path>
                    </g>
                </svg>
            </a>
        </div>
    <?php else: ?>

        <div class="page_main">
            <form class="app_form" action="<?= url("erp/users/register"); ?>" method="post">
                <?php if (!$profile->edit): ?>
                    <input type="hidden" name="action" value="updateAddress"/>
                    <input type="hidden" name="users_id" value="<?= $profile->users->id; ?>"/>
                    <div class="main_modal_form">
                        <div class="group">
                            <label>
                                <div><span class="">* CEP:</span></div>
                                <input type="text" class="mask-cep" name="code" id="cep" placeholder="00000-000"
                                       value="<?= ($profile->address->code ?? ""); ?>" required disabled/>
                            </label>
                            <label>
                                <div><span class="">* Estado:</span></div>
                                <select name="state" id="uf" disabled>
                                    <?php if (!$profile->address): ?>
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
                                    <?php else: ?>
                                        <option <?= ($profile->address->state == "AC" ? "selected" : ""); ?> value="AC">
                                            &ofcir; Acre
                                        </option>
                                        <option <?= ($profile->address->state == "AL" ? "selected" : ""); ?> value="AL">
                                            &ofcir; Alagoas
                                        </option>
                                        <option <?= ($profile->address->state == "AP" ? "selected" : ""); ?> value="AP">
                                            &ofcir; Amapá
                                        </option>
                                        <option <?= ($profile->address->state == "AM" ? "selected" : ""); ?> value="AM">
                                            &ofcir; Amazonas
                                        </option>
                                        <option <?= ($profile->address->state == "BA" ? "selected" : ""); ?> value="BA">
                                            &ofcir; Bahia
                                        </option>
                                        <option <?= ($profile->address->state == "CE" ? "selected" : ""); ?> value="CE">
                                            &ofcir; Ceará
                                        </option>
                                        <option <?= ($profile->address->state == "DF" ? "selected" : ""); ?> value="DF">
                                            &ofcir; Distrito Federal
                                        </option>
                                        <option <?= ($profile->address->state == "ES" ? "selected" : ""); ?> value="ES">
                                            &ofcir; Espírito Santo
                                        </option>
                                        <option <?= ($profile->address->state == "GO" ? "selected" : ""); ?> value="GO">
                                            &ofcir; Goiás
                                        </option>
                                        <option <?= ($profile->address->state == "MA" ? "selected" : ""); ?> value="MA">
                                            &ofcir; Maranhão
                                        </option>
                                        <option <?= ($profile->address->state == "MT" ? "selected" : ""); ?> value="MT">
                                            &ofcir; Mato Grosso
                                        </option>
                                        <option <?= ($profile->address->state == "MS" ? "selected" : ""); ?> value="MS">
                                            &ofcir; Mato Grosso do Sul
                                        </option>
                                        <option <?= ($profile->address->state == "MG" ? "selected" : ""); ?> value="MG">
                                            &ofcir; Minas Gerais
                                        </option>
                                        <option <?= ($profile->address->state == "PA" ? "selected" : ""); ?> value="PA">
                                            &ofcir; Pará
                                        </option>
                                        <option <?= ($profile->address->state == "PB" ? "selected" : ""); ?> value="PB">
                                            &ofcir; Paraíba
                                        </option>
                                        <option <?= ($profile->address->state == "PR" ? "selected" : ""); ?> value="PR">
                                            &ofcir; Paraná
                                        </option>
                                        <option <?= ($profile->address->state == "PE" ? "selected" : ""); ?> value="PE">
                                            &ofcir; Pernambuco
                                        </option>
                                        <option <?= ($profile->address->state == "PI" ? "selected" : ""); ?> value="PI">
                                            &ofcir; Piauí
                                        </option>
                                        <option <?= ($profile->address->state == "RJ" ? "selected" : ""); ?> value="RJ">
                                            &ofcir; Rio de Janeiro
                                        </option>
                                        <option <?= ($profile->address->state == "RN" ? "selected" : ""); ?> value="RN">
                                            &ofcir; Rio Grande do Norte
                                        </option>
                                        <option <?= ($profile->address->state == "RS" ? "selected" : ""); ?> value="RS">
                                            &ofcir; Rio Grande do Sul
                                        </option>
                                        <option <?= ($profile->address->state == "RO" ? "selected" : ""); ?> value="RO">
                                            &ofcir; Rondônia
                                        </option>
                                        <option <?= ($profile->address->state == "PR" ? "selected" : ""); ?> value="RR">
                                            &ofcir; Roraima
                                        </option>
                                        <option <?= ($profile->address->state == "SC" ? "selected" : ""); ?> value="SC">
                                            &ofcir; Santa Catarina
                                        </option>
                                        <option <?= ($profile->address->state == "SP" ? "selected" : ""); ?> value="SP">
                                            &ofcir; São Paulo
                                        </option>
                                        <option <?= ($profile->address->state == "SE" ? "selected" : ""); ?> value="SE">
                                            &ofcir; Sergipe
                                        </option>
                                        <option <?= ($profile->address->state == "TO" ? "selected" : ""); ?> value="TO">
                                            &ofcir; Tocantins
                                        </option>
                                    <?php endif; ?>
                                </select>
                            </label>
                            <label>
                                <div><span class="">* Cidade:</span></div>
                                <input type="text" name="city" id="cidade" placeholder="Cidade"
                                       value="<?= ($profile->address->city ?? ""); ?>" required" disabled/>
                            </label>
                            <label>
                                <div><span class="">* Bairro:</span></div>
                                <input type="text" name="district" id="bairro" placeholder="Bairro"
                                       value="<?= ($profile->address->district ?? ""); ?>" required disabled/>
                            </label>
                        </div>
                        <div class="group">
                            <label>
                                <div><span class="">* Logradouro:</span></div>
                                <input type="text" name="street" id="rua" placeholder="Avenida, Rua..."
                                       value="<?= ($profile->address->street ?? ""); ?>" required disabled/>
                            </label>
                            <label>
                                <div><span class="">* Numero:</span></div>
                                <input type="text" name="number" placeholder="Numero"
                                       value="<?= ($profile->address->number ?? ""); ?>" required disabled/>
                            </label>
                            <label>
                                <div><span class="">Complemento:</span></div>
                                <input type="text" name="complement" placeholder="Bloco, Apto..."
                                       value="<?= ($profile->address->complement ?? ""); ?>" disabled/>
                            </label>
                            <label>
                                <div class="gruop_label"><span class="">Descrição:</span></div>
                                <input type="text" name="description"
                                       placeholder="Indentifique (Trabalho, Casa e etc...)"
                                       value="<?= ($profile->address->description ?? ""); ?>" disabled/>
                            </label>
                        </div>
                        <div class="group">
                            <label>
                                <div class="gruop_label"><span class="">* Envio de correspondência:</span></div>
                                <select name="status" required disabled>
                                    <?php
                                    $status = $profile->address->status;
                                    $select = function ($value) use ($status) {
                                        return ($status == $value ? "selected" : "");
                                    };
                                    ?>
                                    <option <?= $select("main"); ?> value="main">Principal</option>
                                    <option <?= $select("leading"); ?> value="leading">Endereço</option>
                                </select>
                            </label>
                            <label></label>
                            <label></label>
                            <label></label>
                        </div>
                        <div class="app_form_footer">
                            <a class="nav--btn btn btn-light transition icon-pencil"
                               href="<?= url("/erp/users/address/{$profile->users->id}/edit"); ?>">Editar</a>
                        </div>
                    </div>

                <?php else: ?>
                    <input type="hidden" name="action" value="updateAddress"/>
                    <input type="hidden" name="id" value="<?= $profile->address->id; ?>"/>
                    <input type="hidden" name="users_id" value="<?= $profile->users->id; ?>"/>
                    <div class="main_modal_form">
                        <div class="group">
                            <label>
                                <div><span class="">* CEP:</span></div>
                                <input type="text" class="mask-cep" name="code" id="cep" placeholder="00000-000"
                                       value="<?= ($profile->address->code ?? ""); ?>" required/>
                            </label>
                            <label>
                                <div><span class="">* Estado:</span></div>
                                <select name="state" id="uf">
                                    <?php if (!$profile->address): ?>
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
                                    <?php else: ?>
                                        <option <?= ($profile->address->state == "AC" ? "selected" : ""); ?> value="AC">
                                            &ofcir; Acre
                                        </option>
                                        <option <?= ($profile->address->state == "AL" ? "selected" : ""); ?> value="AL">
                                            &ofcir; Alagoas
                                        </option>
                                        <option <?= ($profile->address->state == "AP" ? "selected" : ""); ?> value="AP">
                                            &ofcir; Amapá
                                        </option>
                                        <option <?= ($profile->address->state == "AM" ? "selected" : ""); ?> value="AM">
                                            &ofcir; Amazonas
                                        </option>
                                        <option <?= ($profile->address->state == "BA" ? "selected" : ""); ?> value="BA">
                                            &ofcir; Bahia
                                        </option>
                                        <option <?= ($profile->address->state == "CE" ? "selected" : ""); ?> value="CE">
                                            &ofcir; Ceará
                                        </option>
                                        <option <?= ($profile->address->state == "DF" ? "selected" : ""); ?> value="DF">
                                            &ofcir; Distrito Federal
                                        </option>
                                        <option <?= ($profile->address->state == "ES" ? "selected" : ""); ?> value="ES">
                                            &ofcir; Espírito Santo
                                        </option>
                                        <option <?= ($profile->address->state == "GO" ? "selected" : ""); ?> value="GO">
                                            &ofcir; Goiás
                                        </option>
                                        <option <?= ($profile->address->state == "MA" ? "selected" : ""); ?> value="MA">
                                            &ofcir; Maranhão
                                        </option>
                                        <option <?= ($profile->address->state == "MT" ? "selected" : ""); ?> value="MT">
                                            &ofcir; Mato Grosso
                                        </option>
                                        <option <?= ($profile->address->state == "MS" ? "selected" : ""); ?> value="MS">
                                            &ofcir; Mato Grosso do Sul
                                        </option>
                                        <option <?= ($profile->address->state == "MG" ? "selected" : ""); ?> value="MG">
                                            &ofcir; Minas Gerais
                                        </option>
                                        <option <?= ($profile->address->state == "PA" ? "selected" : ""); ?> value="PA">
                                            &ofcir; Pará
                                        </option>
                                        <option <?= ($profile->address->state == "PB" ? "selected" : ""); ?> value="PB">
                                            &ofcir; Paraíba
                                        </option>
                                        <option <?= ($profile->address->state == "PR" ? "selected" : ""); ?> value="PR">
                                            &ofcir; Paraná
                                        </option>
                                        <option <?= ($profile->address->state == "PE" ? "selected" : ""); ?> value="PE">
                                            &ofcir; Pernambuco
                                        </option>
                                        <option <?= ($profile->address->state == "PI" ? "selected" : ""); ?> value="PI">
                                            &ofcir; Piauí
                                        </option>
                                        <option <?= ($profile->address->state == "RJ" ? "selected" : ""); ?> value="RJ">
                                            &ofcir; Rio de Janeiro
                                        </option>
                                        <option <?= ($profile->address->state == "RN" ? "selected" : ""); ?> value="RN">
                                            &ofcir; Rio Grande do Norte
                                        </option>
                                        <option <?= ($profile->address->state == "RS" ? "selected" : ""); ?> value="RS">
                                            &ofcir; Rio Grande do Sul
                                        </option>
                                        <option <?= ($profile->address->state == "RO" ? "selected" : ""); ?> value="RO">
                                            &ofcir; Rondônia
                                        </option>
                                        <option <?= ($profile->address->state == "PR" ? "selected" : ""); ?> value="RR">
                                            &ofcir; Roraima
                                        </option>
                                        <option <?= ($profile->address->state == "SC" ? "selected" : ""); ?> value="SC">
                                            &ofcir; Santa Catarina
                                        </option>
                                        <option <?= ($profile->address->state == "SP" ? "selected" : ""); ?> value="SP">
                                            &ofcir; São Paulo
                                        </option>
                                        <option <?= ($profile->address->state == "SE" ? "selected" : ""); ?> value="SE">
                                            &ofcir; Sergipe
                                        </option>
                                        <option <?= ($profile->address->state == "TO" ? "selected" : ""); ?> value="TO">
                                            &ofcir; Tocantins
                                        </option>
                                    <?php endif; ?>
                                </select>
                            </label>
                            <label>
                                <div><span class="">* Cidade:</span></div>
                                <input type="text" name="city" id="cidade" placeholder="Cidade"
                                       value="<?= ($profile->address->city ?? ""); ?>" required" />
                            </label>
                            <label>
                                <div><span class="">* Bairro:</span></div>
                                <input type="text" name="district" id="bairro" placeholder="Bairro"
                                       value="<?= ($profile->address->district ?? ""); ?>" required/>
                            </label>
                        </div>
                        <div class="group">
                            <label>
                                <div><span class="">* Logradouro:</span></div>
                                <input type="text" name="street" id="rua" placeholder="Avenida, Rua..."
                                       value="<?= ($profile->address->street ?? ""); ?>" required/>
                            </label>
                            <label>
                                <div><span class="">* Numero:</span></div>
                                <input type="text" name="number" placeholder="Numero"
                                       value="<?= ($profile->address->number ?? ""); ?>" required/>
                            </label>
                            <label>
                                <div><span class="">Complemento:</span></div>
                                <input type="text" name="complement" placeholder="Bloco, Apto..."
                                       value="<?= ($profile->address->complement ?? ""); ?>"/>
                            </label>
                            <label>
                                <div class="gruop_label"><span class="">Descrição:</span></div>
                                <input type="text" name="description"
                                       placeholder="Indentifique (Trabalho, Casa e etc...)"
                                       value="<?= ($profile->address->description ?? ""); ?>"/>
                            </label>
                        </div>
                        <div class="group">
                            <label>
                                <div class="gruop_label"><span class="">* Envio de correspondência:</span></div>
                                <select name="status" required>
                                    <?php
                                    $status = $profile->address->status;
                                    $select = function ($value) use ($status) {
                                        return ($status == $value ? "selected" : "");
                                    };
                                    ?>
                                    <option <?= $select("main"); ?> value="main">Principal</option>
                                    <option <?= $select("leading"); ?> value="leading">Endereço</option>
                                </select>
                            </label>
                            <label></label>
                            <label></label>
                            <label></label>
                        </div>
                        <div class="app_form_footer">
                            <button class="btn gradient gradient-blue gradient-hover transition icon-pencil-square-o">
                                Atualizar
                            </button>
                            <a class="nav--btn btn btn-blue-outline transition icon-angle-left"
                               href="<?= url("/erp/condo/address"); ?>">Voltar</a>
                        </div>
                    </div>
                <?php endif; ?>
            </form>
        </div>


    <?php endif; ?>

</div>
