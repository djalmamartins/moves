<?php $this->layout("layouts/erp"); ?>

<?php if (!$condo->select): ?>
    <?= $this->insert("pages/welcome-condo", $this->data); ?>
<?php else: ?>
    <div class="container">

        <div class="page_main_header">
            <header>
                <h2>Endereço</h2>
            </header>
        </div>
        <div class="page_main">
            <form class="app_form" action="<?= url("erp/condo/register"); ?>" method="post">
                <?php if (!$condo->edit): ?>
                    <input type="hidden" name="action" value="updateAddress"/>
                    <input type="hidden" name="id" value="<?= $condo->address->id; ?>" />
                    <input type="hidden" name="condo_id" value="<?= $condo->select->id; ?>" />
                    <div class="main_modal_form">
                        <div class="group">
                            <label>
                                <div><span class="">* CEP:</span></div>
                                <input type="text" class="mask-cep" name="code" id="cep" placeholder="00000-000"
                                       value="<?= ($condo->address->code ?? ""); ?>" required   disabled/>
                            </label>
                            <label>
                                <div><span class="">* Estado:</span></div>
                                <select name="state" id="uf" disabled>
                                    <?php if (!$condo->address): ?>
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
                                        <option <?= ($condo->address->state == "AC" ? "selected" : ""); ?> value="AC">
                                            &ofcir; Acre
                                        </option>
                                        <option <?= ($condo->address->state == "AL" ? "selected" : ""); ?> value="AL">
                                            &ofcir; Alagoas
                                        </option>
                                        <option <?= ($condo->address->state == "AP" ? "selected" : ""); ?> value="AP">
                                            &ofcir; Amapá
                                        </option>
                                        <option <?= ($condo->address->state == "AM" ? "selected" : ""); ?> value="AM">
                                            &ofcir; Amazonas
                                        </option>
                                        <option <?= ($condo->address->state == "BA" ? "selected" : ""); ?> value="BA">
                                            &ofcir; Bahia
                                        </option>
                                        <option <?= ($condo->address->state == "CE" ? "selected" : ""); ?> value="CE">
                                            &ofcir; Ceará
                                        </option>
                                        <option <?= ($condo->address->state == "DF" ? "selected" : ""); ?> value="DF">
                                            &ofcir; Distrito Federal
                                        </option>
                                        <option <?= ($condo->address->state == "ES" ? "selected" : ""); ?> value="ES">
                                            &ofcir; Espírito Santo
                                        </option>
                                        <option <?= ($condo->address->state == "GO" ? "selected" : ""); ?> value="GO">
                                            &ofcir; Goiás
                                        </option>
                                        <option <?= ($condo->address->state == "MA" ? "selected" : ""); ?> value="MA">
                                            &ofcir; Maranhão
                                        </option>
                                        <option <?= ($condo->address->state == "MT" ? "selected" : ""); ?> value="MT">
                                            &ofcir; Mato Grosso
                                        </option>
                                        <option <?= ($condo->address->state == "MS" ? "selected" : ""); ?> value="MS">
                                            &ofcir; Mato Grosso do Sul
                                        </option>
                                        <option <?= ($condo->address->state == "MG" ? "selected" : ""); ?> value="MG">
                                            &ofcir; Minas Gerais
                                        </option>
                                        <option <?= ($condo->address->state == "PA" ? "selected" : ""); ?> value="PA">
                                            &ofcir; Pará
                                        </option>
                                        <option <?= ($condo->address->state == "PB" ? "selected" : ""); ?> value="PB">
                                            &ofcir; Paraíba
                                        </option>
                                        <option <?= ($condo->address->state == "PR" ? "selected" : ""); ?> value="PR">
                                            &ofcir; Paraná
                                        </option>
                                        <option <?= ($condo->address->state == "PE" ? "selected" : ""); ?> value="PE">
                                            &ofcir; Pernambuco
                                        </option>
                                        <option <?= ($condo->address->state == "PI" ? "selected" : ""); ?> value="PI">
                                            &ofcir; Piauí
                                        </option>
                                        <option <?= ($condo->address->state == "RJ" ? "selected" : ""); ?> value="RJ">
                                            &ofcir; Rio de Janeiro
                                        </option>
                                        <option <?= ($condo->address->state == "RN" ? "selected" : ""); ?> value="RN">
                                            &ofcir; Rio Grande do Norte
                                        </option>
                                        <option <?= ($condo->address->state == "RS" ? "selected" : ""); ?> value="RS">
                                            &ofcir; Rio Grande do Sul
                                        </option>
                                        <option <?= ($condo->address->state == "RO" ? "selected" : ""); ?> value="RO">
                                            &ofcir; Rondônia
                                        </option>
                                        <option <?= ($condo->address->state == "PR" ? "selected" : ""); ?> value="RR">
                                            &ofcir; Roraima
                                        </option>
                                        <option <?= ($condo->address->state == "SC" ? "selected" : ""); ?> value="SC">
                                            &ofcir; Santa Catarina
                                        </option>
                                        <option <?= ($condo->address->state == "SP" ? "selected" : ""); ?> value="SP">
                                            &ofcir; São Paulo
                                        </option>
                                        <option <?= ($condo->address->state == "SE" ? "selected" : ""); ?> value="SE">
                                            &ofcir; Sergipe
                                        </option>
                                        <option <?= ($condo->address->state == "TO" ? "selected" : ""); ?> value="TO">
                                            &ofcir; Tocantins
                                        </option>
                                    <?php endif; ?>
                                </select>
                            </label>
                            <label>
                                <div><span class="">* Cidade:</span></div>
                                <input type="text" name="city" id="cidade" placeholder="Cidade"
                                       value="<?= ($condo->address->city ?? ""); ?>" required"   disabled/>
                            </label>
                            <label>
                                <div><span class="">* Bairro:</span></div>
                                <input type="text" name="district" id="bairro" placeholder="Bairro"
                                       value="<?= ($condo->address->district ?? ""); ?>" required   disabled/>
                            </label>
                        </div>
                        <div class="group">
                            <label>
                                <div><span class="">* Logradouro:</span></div>
                                <input type="text" name="street" id="rua" placeholder="Avenida, Rua..."
                                       value="<?= ($condo->address->street ?? ""); ?>" required   disabled/>
                            </label>
                            <label>
                                <div><span class="">* Numero:</span></div>
                                <input type="text" name="number" placeholder="Numero"
                                       value="<?= ($condo->address->number ?? ""); ?>" required   disabled/>
                            </label>
                            <label>
                                <div><span class="">Complemento:</span></div>
                                <input type="text" name="complement" placeholder="Bloco, Apto..."
                                       value="<?= ($condo->address->complement ?? ""); ?>"   disabled/>
                            </label>
                            <label>
                                <div class="gruop_label"><span class="">Descrição:</span></div>
                                <input type="text" name="description" placeholder="Indentifique (Trabalho, Casa e etc...)" value="<?=($condo->address->description ?? ""); ?>"  disabled/>
                            </label>
                        </div>
                        <div class="group">
                            <label>
                                <div class="gruop_label"><span class="">* Envio de correspondência:</span></div>
                                <select name="status" required disabled>
                                    <?php
                                    $status = $condo->address->status;
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
                               href="<?= url("/erp/condo/address/edit"); ?>">Editar</a>
                        </div>
                    </div>

                <?php else: ?>
                    <input type="hidden" name="action" value="updateAddress"/>
                    <input type="hidden" name="id" value="<?= $condo->address->id; ?>"/>
                    <input type="hidden" name="condo_id" value="<?= $condo->select->id; ?>"/>
                <div class="main_modal_form">
                    <div class="group">
                        <label>
                            <div><span class="">* CEP:</span></div>
                            <input type="text" class="mask-cep" name="code" id="cep" placeholder="00000-000"
                                   value="<?= ($condo->address->code ?? ""); ?>" required  />
                        </label>
                        <label>
                            <div><span class="">* Estado:</span></div>
                            <select name="state" id="uf" >
                                <?php if (!$condo->address): ?>
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
                                    <option <?= ($condo->address->state == "AC" ? "selected" : ""); ?> value="AC">
                                        &ofcir; Acre
                                    </option>
                                    <option <?= ($condo->address->state == "AL" ? "selected" : ""); ?> value="AL">
                                        &ofcir; Alagoas
                                    </option>
                                    <option <?= ($condo->address->state == "AP" ? "selected" : ""); ?> value="AP">
                                        &ofcir; Amapá
                                    </option>
                                    <option <?= ($condo->address->state == "AM" ? "selected" : ""); ?> value="AM">
                                        &ofcir; Amazonas
                                    </option>
                                    <option <?= ($condo->address->state == "BA" ? "selected" : ""); ?> value="BA">
                                        &ofcir; Bahia
                                    </option>
                                    <option <?= ($condo->address->state == "CE" ? "selected" : ""); ?> value="CE">
                                        &ofcir; Ceará
                                    </option>
                                    <option <?= ($condo->address->state == "DF" ? "selected" : ""); ?> value="DF">
                                        &ofcir; Distrito Federal
                                    </option>
                                    <option <?= ($condo->address->state == "ES" ? "selected" : ""); ?> value="ES">
                                        &ofcir; Espírito Santo
                                    </option>
                                    <option <?= ($condo->address->state == "GO" ? "selected" : ""); ?> value="GO">
                                        &ofcir; Goiás
                                    </option>
                                    <option <?= ($condo->address->state == "MA" ? "selected" : ""); ?> value="MA">
                                        &ofcir; Maranhão
                                    </option>
                                    <option <?= ($condo->address->state == "MT" ? "selected" : ""); ?> value="MT">
                                        &ofcir; Mato Grosso
                                    </option>
                                    <option <?= ($condo->address->state == "MS" ? "selected" : ""); ?> value="MS">
                                        &ofcir; Mato Grosso do Sul
                                    </option>
                                    <option <?= ($condo->address->state == "MG" ? "selected" : ""); ?> value="MG">
                                        &ofcir; Minas Gerais
                                    </option>
                                    <option <?= ($condo->address->state == "PA" ? "selected" : ""); ?> value="PA">
                                        &ofcir; Pará
                                    </option>
                                    <option <?= ($condo->address->state == "PB" ? "selected" : ""); ?> value="PB">
                                        &ofcir; Paraíba
                                    </option>
                                    <option <?= ($condo->address->state == "PR" ? "selected" : ""); ?> value="PR">
                                        &ofcir; Paraná
                                    </option>
                                    <option <?= ($condo->address->state == "PE" ? "selected" : ""); ?> value="PE">
                                        &ofcir; Pernambuco
                                    </option>
                                    <option <?= ($condo->address->state == "PI" ? "selected" : ""); ?> value="PI">
                                        &ofcir; Piauí
                                    </option>
                                    <option <?= ($condo->address->state == "RJ" ? "selected" : ""); ?> value="RJ">
                                        &ofcir; Rio de Janeiro
                                    </option>
                                    <option <?= ($condo->address->state == "RN" ? "selected" : ""); ?> value="RN">
                                        &ofcir; Rio Grande do Norte
                                    </option>
                                    <option <?= ($condo->address->state == "RS" ? "selected" : ""); ?> value="RS">
                                        &ofcir; Rio Grande do Sul
                                    </option>
                                    <option <?= ($condo->address->state == "RO" ? "selected" : ""); ?> value="RO">
                                        &ofcir; Rondônia
                                    </option>
                                    <option <?= ($condo->address->state == "PR" ? "selected" : ""); ?> value="RR">
                                        &ofcir; Roraima
                                    </option>
                                    <option <?= ($condo->address->state == "SC" ? "selected" : ""); ?> value="SC">
                                        &ofcir; Santa Catarina
                                    </option>
                                    <option <?= ($condo->address->state == "SP" ? "selected" : ""); ?> value="SP">
                                        &ofcir; São Paulo
                                    </option>
                                    <option <?= ($condo->address->state == "SE" ? "selected" : ""); ?> value="SE">
                                        &ofcir; Sergipe
                                    </option>
                                    <option <?= ($condo->address->state == "TO" ? "selected" : ""); ?> value="TO">
                                        &ofcir; Tocantins
                                    </option>
                                <?php endif; ?>
                            </select>
                        </label>
                        <label>
                            <div><span class="">* Cidade:</span></div>
                            <input type="text" name="city" id="cidade" placeholder="Cidade"
                                   value="<?= ($condo->address->city ?? ""); ?>" required"  />
                        </label>
                        <label>
                            <div><span class="">* Bairro:</span></div>
                            <input type="text" name="district" id="bairro" placeholder="Bairro"
                                   value="<?= ($condo->address->district ?? ""); ?>" required  />
                        </label>
                    </div>
                    <div class="group">
                        <label>
                            <div><span class="">* Logradouro:</span></div>
                            <input type="text" name="street" id="rua" placeholder="Avenida, Rua..."
                                   value="<?= ($condo->address->street ?? ""); ?>" required  />
                        </label>
                        <label>
                            <div><span class="">* Numero:</span></div>
                            <input type="text" name="number" placeholder="Numero"
                                   value="<?= ($condo->address->number ?? ""); ?>" required  />
                        </label>
                        <label>
                            <div><span class="">Complemento:</span></div>
                            <input type="text" name="complement" placeholder="Bloco, Apto..."
                                   value="<?= ($condo->address->complement ?? ""); ?>"  />
                        </label>
                        <label>
                            <div class="gruop_label"><span class="">Descrição:</span></div>
                            <input type="text" name="description" placeholder="Indentifique (Trabalho, Casa e etc...)" value="<?=($condo->address->description ?? ""); ?>" />
                        </label>
                    </div>
                    <div class="group">
                        <label>
                            <div class="gruop_label"><span class="">* Envio de correspondência:</span></div>
                            <select name="status" required>
                                <?php
                                $status = $condo->address->status;
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
                        <button class="btn gradient gradient-blue gradient-hover transition icon-pencil-square-o">Atualizar</button>
                        <a class="nav--btn btn btn-blue-outline transition icon-angle-left"
                           href="<?= url("/erp/condo/address"); ?>">Voltar</a>
                    </div>
                </div>
                <?php endif; ?>
            </form>
        </div>


    </div>
<?php endif; ?>