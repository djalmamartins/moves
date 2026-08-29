<?php $this->layout("_erp"); ?>

<div class="container">
    <div class="page_main_header">
        <header>
            <h2>Cadastro de Usuário</h2>
            <?php $this->insert("widgets/register/sidebar"); ?>
        </header>
    </div>

    <div class="page_main">
        <form class="app_form" action="<?= url("erp/users/register"); ?>" method="post">
            <?php if (!empty($profile->users)) : ?>
                <?php if ($profile->users->type == "pj") : ?>
                    <?php header("location: profile_pj/{$profile->users->id}"); ?>
                <?php endif; ?>
                <input type="hidden" name="action" value="update"/>
                <input type="hidden" name="type" value="pf"/>
                <input type="hidden" name="user_id" value="<?= $profile->users->id; ?>"/>
                <div class="flex">
                    <div class="box_one">
                        <?php if (!empty($profile->users->photo)): ?>
                            <div class="page_main_img">
                                <div class="j_profile_image thumb"
                                     style="background-image: url('<?= image($profile->users->photo, 140, 140); ?>')"></div>
                                <label for='selecao-arquivo'
                                       class="btn btn-small gradient gradient-blue gradient-hover transition icon-picture-o">Alterar</label>
                                <input id='selecao-arquivo' data-image=".j_profile_image" type="file" name="photo"/>
                            </div>
                        <?php else: ?>
                            <div class="page_main_img">
                                <div class="j_profile_image thumb"><?= substr($profile->users->first_name, 0, 1); ?></div>
                                <label for='selecao-arquivo'
                                       class="btn btn-small gradient gradient-blue gradient-hover transition icon-picture-o">Adicionar</label>
                                <input id='selecao-arquivo' data-image=".j_profile_image" type="file" name="photo"/>
                            </div>

                        <?php endif; ?>
                    </div>
                    <div class="box_two">
                        <h3>Informações</h3>
                        <div class="main_modal_form">
                            <div class="group">
                                <label>
                                    <div class="gruop_label"><span class="">*Nome Completo:</span></div>
                                    <input type="text" name="name" placeholder="Primeiro nome"
                                           value="<?= $profile->users->first_name; ?> <?= $profile->users->last_name; ?>"
                                           required/>
                                </label>
                                <label>
                                    <div class="group_label"><span class="">Data de Nascimento:</span></div>
                                    <input type="date" name="datebirth"
                                           value="<?= $profile->users->datebirth; ?>"
                                           placeholder="dd/mm/yyyy"/>
                                </label>
                            </div>
                            <div class="group">
                                <label>
                                    <div class="group_label"><span class="">*CPF</span></div>
                                    <input type="text" class="mask-doc" name="document"
                                           value="<?= $profile->users->document; ?>"
                                           placeholder="CNPJ"/>
                                </label>
                                <label>
                                    <div class="group_label"><span class="">RG</span></div>
                                    <input type="text" class="" name="document_rg"
                                           value="<?= $profile->users->document_rg; ?>"
                                           placeholder="RG"/>
                                </label>
                            </div>
                            <div class="group">

                                <label class="label uploadFile">
                                    <div class="gruop_label"><span>CPF - PDF:</span></div>
                                    <span for='cpf'
                                          class="btn-outline btn-blue-outline transition icon-file-pdf-o filename">PDF - CPF</span>
                                    <input id='cpf' type="file" name="doc_cpf"/>
                                    <?php if (!empty($profile->users->doc_cpf)): ?>
                                        <a href="<?= url("/storage/" . $profile->users->doc_cpf); ?>" target="_blank"
                                           class="icon-file-pdf-o">Documento de CPF</a>
                                    <?php endif; ?>

                                </label>
                                <label class="label uploadFile">
                                    <div class="gruop_label"><span>RG - PDF: </span></div>
                                    <span for='rg' class="btn-outline btn-blue-outline icon-file-pdf-o filename">PDF - RG</span>
                                    <input id='rg' type="file" name="doc_rg"/>
                                    <?php if (!empty($profile->users->doc_rg)): ?>
                                        <a href="<?= url("/storage/" . $profile->users->doc_rg); ?>" target="_blank"
                                           class="icon-file-pdf-o">Documento de identidade</a>
                                    <?php endif; ?>
                                </label>
                            </div>

                            <div class="group">
                                <label>
                                    <div class="group_label"><span class="">*Status</span></div>
                                    <span class="switch-field">
                                        <input type="radio" id="radio-one" name="switch-status"
                                               value="confirmed" <?= ($profile->users->status == "confirmed" ? "checked" : ""); ?>/>
                                        <label for="radio-one">Ativo</label>
                                        <input type="radio" id="radio-two" name="switch-status"
                                               value="registered" <?= ($profile->users->status == "registered" ? "checked" : ""); ?>/>
                                        <label for="radio-two">Inativo</label for="radio-two">
                                    </span>
                                </label>
                                <label>
                                    <div class="group_label"><span class="">*Envio</span></div>
                                    <span class="switch-field">
                                        <input type="radio" id="radio-one-send" name="switch-send"
                                               value="1" <?= ($profile->users->send == "1" ? "checked" : ""); ?>/>
                                        <label for="radio-one-send">Ativo</label>
                                        <input type="radio" id="radio-two-send" name="switch-send"
                                               value="0" <?= ($profile->users->send == "0" ? "checked" : ""); ?>/>
                                        <label for="radio-two-send">Inativo</label for="radio-two-send">
                                    </span>
                                </label>
                                <label>
                                    <div class="group_label"><span class="">Termos:</span></div>
                                    <span class="switch-field">
                                        <input type="radio" id="radio-one-privacy" name="switch-privacy"
                                               value="accept" <?= ($profile->users->privacy == "accept" ? "checked" : ""); ?>
                                               disabled/>
                                        <label for="radio-one-privacy">Aceito</label>
                                        <input type="radio" id="radio-two-privacy" name="switch-privacy"
                                               value="reject" <?= ($profile->users->privacy == "reject" ? "checked" : ""); ?>
                                               disabled/>
                                        <label for="radio-two-privacy">Rejeitado</label for="radio-two-privacy">
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex al-top">
                    <div class="one">
                        <h3>Informações de contato</h3>

                        <div class="main_modal_form">
                            <div class="group">

                                <label>
                                    <div class="group_label"><span class="">* E-mail:</span></div>
                                    <input type="email" name="email" placeholder="Melhor e-mail"
                                           value="<?= $profile->users->email; ?>" required/>
                                </label>

                                <label>
                                    <div class="group_label"><span class="">Telefone Celular:</span></div>
                                    <input type="text" name="phone_cell" class="mask-cell"
                                           placeholder="(00) 00000-0000"
                                           value="<?= $profile->users->phone_cell; ?>"/>
                                </label>

                                <label>
                                    <div class="group_label"><span class="">Telefone Residencial:</span></div>
                                    <input type="text" class="mask-phone" name="phone_residential"
                                           placeholder="(00) 0000-0000"
                                           value="<?= $profile->users->phone_residential; ?>"/>
                                </label>

                                <label>
                                    <div class="group_label"><span class="">Telefone Comercial:</span></div>
                                    <input type="text" class="mask-phone" name="phone_commercial"
                                           placeholder="(00) 0000-0000"
                                           value="<?= $profile->users->phone_commercial; ?>"/>
                                </label>
                            </div>
                            <div class="group">
                                <label>
                                    <div class="group_label"><span class="">Aceita receber SMS</span></div>
                                    <span class="switch-field">
                                        <input type="radio" id="radio-one-sms" name="switch-sms"
                                               value="1" <?= ($profile->users->despatch_sms == "1" ? "checked" : ""); ?>/>
                                        <label for="radio-one-sms">Sim</label>
                                        <input type="radio" id="radio-two-sms" name="switch-sms"
                                               value="0" <?= ($profile->users->despatch_sms == "0" ? "checked" : ""); ?>/>
                                        <label for="radio-two-sms">Não</label for="radio-two-sms">
                                    </span>
                                </label>

                                <label>
                                    <div class="group_label"><span class="">WhatsApp</span></div>
                                    <span class="switch-field">
                                        <input type="radio" id="radio-one-whatsapp" name="switch-whatsapp"
                                               value="1" <?= ($profile->users->despatch_whatsapp == "1" ? "checked" : ""); ?>/>
                                        <label for="radio-one-whatsapp">Sim</label>
                                        <input type="radio" id="radio-two-whatsapp" name="switch-whatsapp"
                                               value="0" <?= ($profile->users->despatch_whatsapp == "0" ? "checked" : ""); ?>/>
                                        <label for="radio-two-whatsapp">Não</label for="radio-two-whatsapp">
                                    </span>
                                </label>

                                <label>
                                    <div class="group_label"><span class="">Telegram</span></div>
                                    <span class="switch-field">
                                        <input type="radio" id="radio-one-telegram" name="switch-telegram"
                                               value="1" <?= ($profile->users->despatch_telegram == "1" ? "checked" : ""); ?>/>
                                        <label for="radio-one-telegram">Sim</label>
                                        <input type="radio" id="radio-two-telegram" name="switch-telegram"
                                               value="0" <?= ($profile->users->despatch_telegram == "0" ? "checked" : ""); ?>/>
                                        <label for="radio-two-telegram">Não</label for="radio-two-telegram">
                                    </span>
                                </label>

                                <label>
                                    <div class="group_label"><span class="">Correspondência</span></div>
                                    <span class="switch-field">
                                        <input type="radio" id="radio-one-letter" name="switch-letter"
                                               value="1" <?= ($profile->users->despatch_letter == "1" ? "checked" : ""); ?>/>
                                        <label for="radio-one-letter">Sim</label>
                                        <input type="radio" id="radio-two-letter" name="switch-letter"
                                               value="0" <?= ($profile->users->despatch_letter == "0" ? "checked" : ""); ?>/>
                                        <label for="radio-two-letter">Não</label for="radio-two-letter">
                                    </span>
                                </label>


                            </div>
                            <div class="group">

                                <label>
                                    <div class="group_label"><span class="">Recados:</span></div>
                                    <input type="text" name="phone_messages" class="mask-cell"
                                           placeholder="(00) 00000-0000"
                                           value="<?= $profile->users->phone_messages; ?>"/>
                                </label>

                                <label>
                                    <div class="group_label"><span class="">* Nome para recados:</span></div>
                                    <input type="text" name="phone_name" placeholder="Nome para recados"
                                           value="<?= $profile->users->phone_name; ?>"/>
                                </label>

                                <label></label>
                                <label></label>

                            </div>

                            <label>
                                <div class="group_label"><span class="">Observação:</span></div>
                                <textarea name="obs" id="" cols="20" rows="6"><?= $profile->users->obs; ?></textarea>
                            </label>
                        </div>

                    </div>
                </div>
                <div class="flex al-top">
                    <div class="one">
                        <h3>Acesso</h3>

                        <div class="main_modal_form">

                            <div class="group">
                                <label>
                                    <div class="gruop_label"><span class="">*Nivel de Acesso:</span></div>
                                    <select name="level" required>
                                        <?php
                                        $level = $profile->users->level;
                                        $select = function ($value) use ($level) {
                                            return ($level == $value ? "selected" : "");
                                        };
                                        ?>

                                        <option <?= $select(1); ?> value="1">&ofcir; Usuário</option>
                                        <option <?= $select(2); ?> value="2">&ofcir; Funcionário</option>
                                        <option <?= $select(3); ?> value="3">&ofcir; Administrador</option>
                                        <?php if ($user->level > 3): ?>
                                            <option <?= $select(5); ?> value="5">&ofcir; Administrador Master</option>
                                        <?php else: ?>
                                            <option <?= $select(5); ?> value="5" disabled>&ofcir; Administrador Master
                                            </option>
                                        <?php endif; ?>
                                    </select>
                                </label>
                                <label></label>
                                <label></label>
                                <label></label>
                            </div>
                            <div class="app_form_footer">
                                <button class="btn gradient gradient-blue gradient-hover transition icon-check-square-o">
                                    Atualizar
                                </button>

                                <a href="" class="remove_link icon-warning"
                                   data-post="<?= url("/erp/users/register/{$profile->users->id}"); ?>"
                                   data-action="delete"
                                   data-confirm="ATENÇÃO: Tem certeza que deseja excluir a administradora e todos os dados relacionados a ele? Essa ação não pode ser feita!"
                                   data-condominium_id="<?= $profile->users->id; ?>">Excluir</a>
                            </div>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <div class="message info icon-info fold-bottom-small border">Ao realizar o cadastro, o usuário receberá
                    um e-mail contendo as instruções necessárias para confirmar a atualização do serviço.
                </div>
                <input type="hidden" name="action" value="create"/>
                <input type="hidden" name="type" value="pf"/>
                <div class="flex">
                    <div class="box_one">

                        <div class="page_main_img">
                            <div class="j_profile_image thumb"></div>
                            <label for='selecao-arquivo'
                                   class="btn btn-small gradient gradient-blue gradient-hover transition icon-picture-o">Adicionar</label>
                            <input id='selecao-arquivo' data-image=".j_profile_image" type="file" name="photo"/>
                        </div>

                    </div>
                    <div class="box_two">
                        <h3>Informações</h3>
                        <div class="main_modal_form">
                            <div class="group">
                                <label>
                                    <div class="gruop_label"><span class="">*Nome Completo:</span></div>
                                    <input type="text" name="name" placeholder="Primeiro nome"

                                           required/>
                                </label>
                                <label>
                                    <div class="group_label"><span class="">Data de Nascimento:</span></div>
                                    <input type="date" name="datebirth"
                                           placeholder="dd/mm/yyyy"/>
                                </label>
                            </div>
                            <div class="group">
                                <label>
                                    <div class="group_label"><span class="">*CPF</span></div>
                                    <input type="text" class="mask-doc" name="document"
                                           placeholder="CPF"/>
                                </label>
                                <label>
                                    <div class="group_label"><span class="">RG</span></div>
                                    <input type="text" class="" name="document_rg"
                                           placeholder="RG"/>
                                </label>
                            </div>
                            <div class="group">

                                <label class="label uploadFile">
                                    <div class="gruop_label"><span>CPF - PDF:</span></div>
                                    <span for='cpf'
                                          class="btn-outline btn-blue-outline transition icon-file-pdf-o filename">PDF - CPF</span>
                                    <input id='cpf' type="file" name="doc_cpf"/>
                                    <?php if (!empty($userEdit->doc_cpf)): ?>
                                        <a href="<?= url("/storage/" . $userEdit->doc_cpf); ?>" target="_blank"
                                           class="icon-file-pdf-o">Documento de CPF</a>
                                    <?php endif; ?>

                                </label>
                                <label class="label uploadFile">
                                    <div class="gruop_label"><span>RG - PDF: </span></div>
                                    <span for='rg' class="btn-outline btn-blue-outline icon-file-pdf-o filename">PDF - RG</span>
                                    <input id='rg' type="file" name="doc_rg"/>
                                    <?php if (!empty($userEdit->doc_rg)): ?>
                                        <a href="<?= url("/storage/" . $userEdit->doc_rg); ?>" target="_blank"
                                           class="icon-file-pdf-o">Documento de identidade</a>
                                    <?php endif; ?>
                                </label>


                            </div>

                            <div class="group">
                                <label>
                                    <div class="group_label"><span class="">*Status</span></div>
                                    <span class="switch-field">
                                        <input type="radio" id="radio-one" name="switch-status"
                                               value="registered" disabled/>
                                        <label for="radio-one">Ativo</label>
                                        <input type="radio" id="radio-two" name="switch-status"
                                               value="registered" checked/>
                                        <label for="radio-two">Inativo</label for="radio-two">
                                    </span>
                                </label>
                                <label>
                                    <div class="group_label"><span class="">*Envio</span></div>
                                    <span class="switch-field">
                                        <input type="radio" id="radio-one-send" name="switch-send"
                                               value="1" disabled/>
                                        <label for="radio-one-send">Ativo</label>
                                        <input type="radio" id="radio-two-send" name="switch-send"
                                               value="0" checked/>
                                        <label for="radio-two-send">Inativo</label for="radio-two-send">
                                    </span>
                                </label>
                                <label>
                                    <div class="group_label"><span class="">Termos:</span></div>
                                    <span class="switch-field">
                                        <input type="radio" id="radio-one-privacy" name="switch-privacy"
                                               value="accept" disabled/>
                                        <label for="radio-one-privacy">Aceito</label>
                                        <input type="radio" id="radio-two-privacy" name="switch-privacy"
                                               value="reject" checked/>
                                        <label for="radio-two-privacy">Rejeitado</label for="radio-two-privacy">
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex al-top">
                    <div class="one">
                        <h3>Informações de contato</h3>

                        <div class="main_modal_form">
                            <div class="group">

                                <label>
                                    <div class="group_label"><span class="">* E-mail:</span></div>
                                    <input type="email" name="email" placeholder="Melhor e-mail" required/>
                                </label>

                                <label>
                                    <div class="group_label"><span class="">Telefone Celular:</span></div>
                                    <input type="text" name="phone_cell" class="mask-cell"
                                           placeholder="(00) 00000-0000"/>
                                </label>

                                <label>
                                    <div class="group_label"><span class="">Telefone Residencial:</span></div>
                                    <input type="text" class="mask-phone" name="phone_residential"
                                           placeholder="(00) 0000-0000"/>
                                </label>

                                <label>
                                    <div class="group_label"><span class="">Telefone Comercial:</span></div>
                                    <input type="text" class="mask-phone" name="phone_commercial"
                                           placeholder="(00) 0000-0000"/>
                                </label>
                            </div>
                            <div class="group">
                                <label>
                                    <div class="group_label"><span class="">Aceita receber SMS</span></div>
                                    <span class="switch-field">
                                        <input type="radio" id="radio-one-sms" name="switch-sms"
                                               value="1" disabled/>
                                        <label for="radio-one-sms">Sim</label>
                                        <input type="radio" id="radio-two-sms" name="switch-sms"
                                               value="0" checked disabled/>
                                        <label for="radio-two-sms">Não</label for="radio-two-sms">
                                    </span>
                                </label>

                                <label>
                                    <div class="group_label"><span class="">WhatsApp</span></div>
                                    <span class="switch-field">
                                        <input type="radio" id="radio-one-whatsapp" name="switch-whatsapp"
                                               value="1" disabled/>
                                        <label for="radio-one-whatsapp">Sim</label>
                                        <input type="radio" id="radio-two-whatsapp" name="switch-whatsapp"
                                               value="0" checked disabled/>
                                        <label for="radio-two-whatsapp">Não</label for="radio-two-whatsapp">
                                    </span>
                                </label>

                                <label>
                                    <div class="group_label"><span class="">Telegram</span></div>
                                    <span class="switch-field">
                                        <input type="radio" id="radio-one-telegram" name="switch-telegram"
                                               value="1" disabled/>
                                        <label for="radio-one-telegram">Sim</label>
                                        <input type="radio" id="radio-two-telegram" name="switch-telegram"
                                               value="0" checked disabled/>
                                        <label for="radio-two-telegram">Não</label for="radio-two-telegram">
                                    </span>
                                </label>

                                <label>
                                    <div class="group_label"><span class="">Correspondência</span></div>
                                    <span class="switch-field">
                                        <input type="radio" id="radio-one-letter" name="switch-letter"
                                               value="1" disabled/>
                                        <label for="radio-one-letter">Sim</label>
                                        <input type="radio" id="radio-two-letter" name="switch-letter"
                                               value="0" checked disabled/>
                                        <label for="radio-two-letter">Não</label for="radio-two-letter">
                                    </span>
                                </label>
                            </div>
                            <div class="group">

                                <label>
                                    <div class="group_label"><span class="">Recados:</span></div>
                                    <input type="text" name="phone_messages" class="mask-cell"
                                           placeholder="(00) 00000-0000"/>
                                </label>

                                <label>
                                    <div class="group_label"><span class="">* Nome para recados:</span></div>
                                    <input type="text" name="phone_name" placeholder="Nome para recados"/>
                                </label>

                                <label></label>
                                <label></label>

                            </div>

                            <label>
                                <div class="group_label"><span class="">Observação:</span></div>
                                <textarea name="obs" id="" cols="20" rows="6"></textarea>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex al-top">
                    <div class="one">
                        <h3>Enderço</h3>

                        <div class="main_modal_form">
                            <div class="group">
                                <label>
                                    <div><span class="">* CEP:</span></div>
                                    <input type="text" class="mask-cep" name="code" id="cep" placeholder="00000-000"
                                           value="<?= ($address->code ?? ""); ?>" required/>
                                </label>
                                <label>
                                    <div><span class="">* Estado:</span></div>
                                    <select name="state" id="uf">
                                        <?php if (!$address): ?>
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
                                            <option <?= ($address->state == "AC" ? "selected" : ""); ?> value="AC">
                                                &ofcir; Acre
                                            </option>
                                            <option <?= ($address->state == "AL" ? "selected" : ""); ?> value="AL">
                                                &ofcir; Alagoas
                                            </option>
                                            <option <?= ($address->state == "AP" ? "selected" : ""); ?> value="AP">
                                                &ofcir; Amapá
                                            </option>
                                            <option <?= ($address->state == "AM" ? "selected" : ""); ?> value="AM">
                                                &ofcir; Amazonas
                                            </option>
                                            <option <?= ($address->state == "BA" ? "selected" : ""); ?> value="BA">
                                                &ofcir; Bahia
                                            </option>
                                            <option <?= ($address->state == "CE" ? "selected" : ""); ?> value="CE">
                                                &ofcir; Ceará
                                            </option>
                                            <option <?= ($address->state == "DF" ? "selected" : ""); ?> value="DF">
                                                &ofcir; Distrito Federal
                                            </option>
                                            <option <?= ($address->state == "ES" ? "selected" : ""); ?> value="ES">
                                                &ofcir; Espírito Santo
                                            </option>
                                            <option <?= ($address->state == "GO" ? "selected" : ""); ?> value="GO">
                                                &ofcir; Goiás
                                            </option>
                                            <option <?= ($address->state == "MA" ? "selected" : ""); ?> value="MA">
                                                &ofcir; Maranhão
                                            </option>
                                            <option <?= ($address->state == "MT" ? "selected" : ""); ?> value="MT">
                                                &ofcir; Mato Grosso
                                            </option>
                                            <option <?= ($address->state == "MS" ? "selected" : ""); ?> value="MS">
                                                &ofcir; Mato Grosso do Sul
                                            </option>
                                            <option <?= ($address->state == "MG" ? "selected" : ""); ?> value="MG">
                                                &ofcir; Minas Gerais
                                            </option>
                                            <option <?= ($address->state == "PA" ? "selected" : ""); ?> value="PA">
                                                &ofcir; Pará
                                            </option>
                                            <option <?= ($address->state == "PB" ? "selected" : ""); ?> value="PB">
                                                &ofcir; Paraíba
                                            </option>
                                            <option <?= ($address->state == "PR" ? "selected" : ""); ?> value="PR">
                                                &ofcir; Paraná
                                            </option>
                                            <option <?= ($address->state == "PE" ? "selected" : ""); ?> value="PE">
                                                &ofcir; Pernambuco
                                            </option>
                                            <option <?= ($address->state == "PI" ? "selected" : ""); ?> value="PI">
                                                &ofcir; Piauí
                                            </option>
                                            <option <?= ($address->state == "RJ" ? "selected" : ""); ?> value="RJ">
                                                &ofcir; Rio de Janeiro
                                            </option>
                                            <option <?= ($address->state == "RN" ? "selected" : ""); ?> value="RN">
                                                &ofcir; Rio Grande do Norte
                                            </option>
                                            <option <?= ($address->state == "RS" ? "selected" : ""); ?> value="RS">
                                                &ofcir; Rio Grande do Sul
                                            </option>
                                            <option <?= ($address->state == "RO" ? "selected" : ""); ?> value="RO">
                                                &ofcir; Rondônia
                                            </option>
                                            <option <?= ($address->state == "PR" ? "selected" : ""); ?> value="RR">
                                                &ofcir; Roraima
                                            </option>
                                            <option <?= ($address->state == "SC" ? "selected" : ""); ?> value="SC">
                                                &ofcir; Santa Catarina
                                            </option>
                                            <option <?= ($address->state == "SP" ? "selected" : ""); ?> value="SP">
                                                &ofcir; São Paulo
                                            </option>
                                            <option <?= ($address->state == "SE" ? "selected" : ""); ?> value="SE">
                                                &ofcir; Sergipe
                                            </option>
                                            <option <?= ($address->state == "TO" ? "selected" : ""); ?> value="TO">
                                                &ofcir; Tocantins
                                            </option>
                                        <?php endif; ?>
                                    </select>
                                </label>
                                <label>
                                    <div><span class="">* Cidade:</span></div>
                                    <input type="text" name="city" id="cidade" placeholder="Cidade"
                                           value="<?= ($address->city ?? ""); ?>" required"/>
                                </label>
                                <label>
                                    <div><span class="">* Bairro:</span></div>
                                    <input type="text" name="district" id="bairro" placeholder="Bairro"
                                           value="<?= ($address->district ?? ""); ?>" required/>
                                </label>
                            </div>
                            <div class="group">
                                <label>
                                    <div><span class="">* Logradouro:</span></div>
                                    <input type="text" name="street" id="rua" placeholder="Avenida, Rua..."
                                           value="<?= ($address->street ?? ""); ?>" required/>
                                </label>
                                <label>
                                    <div><span class="">* Numero:</span></div>
                                    <input type="text" name="number" placeholder="Numero"
                                           value="<?= ($address->number ?? ""); ?>" required/>
                                </label>
                                <label>
                                    <div><span class="">Complemento:</span></div>
                                    <input type="text" name="complement" placeholder="Bloco, Apto..."
                                           value="<?= ($address->complement ?? ""); ?>"/>
                                </label>
                                <label>
                                    <div class="gruop_label"><span class="">Descrição:</span></div>
                                    <input type="text" name="description"
                                           placeholder="Indentifique (Trabalho, Casa e etc...)"
                                           value="<?= ($profile->address->description ?? ""); ?>" />
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
                        </div>
                    </div>
                </div>

                <div class="flex al-top">
                    <div class="one">
                        <h3>Acesso</h3>

                        <div class="main_modal_form">

                            <div class="group">
                                <label>
                                    <div class="gruop_label"><span class="">*Nivel de Acesso:</span></div>
                                    <select name="level" required>
                                        <?php
                                        $level = $userEdit->level;
                                        $select = function ($value) use ($level) {
                                            return ($level == $value ? "selected" : "");
                                        };
                                        ?>

                                        <option <?= $select(1); ?> value="1">&ofcir; Usuário</option>
                                        <option <?= $select(2); ?> value="2">&ofcir; Funcionário</option>
                                        <option <?= $select(3); ?> value="3">&ofcir; Administrador</option>
                                        <?php if ($user->level > 3): ?>
                                            <option <?= $select(5); ?> value="5">&ofcir; Administrador Master</option>
                                        <?php else: ?>
                                            <option <?= $select(5); ?> value="5" disabled>&ofcir; Administrador Master
                                            </option>
                                        <?php endif; ?>
                                    </select>
                                </label>
                                <label></label>
                                <label></label>
                                <label></label>
                            </div>
                            <div class="app_form_footer">
                                <button class="btn gradient gradient-blue gradient-hover transition icon-check-square-o">
                                    Cadastrar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            <?php endif; ?>
        </form>
    </div>
</div>