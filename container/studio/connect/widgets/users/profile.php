<?php $this->layout("_erp"); ?>


<div class="container">
    <?php if (!empty($profile->users)) : ?>
        <div class="page_main_header">
            <header>
                <h2><?= sprintf("%04s", $profile->users->id); ?> - <?= $profile->users->fullName(); ?></h2>
                <?php $this->insert("widgets/users/sidebar"); ?>
            </header>
        </div>
        <div class="page_main">

            <?php if (!$profile->edit): ?>
            <?php if ($profile->users->type == "pj") : ?>
            <form class="app_form" action="<?= url("erp/users/register"); ?>" method="post">
                <input type="hidden" name="action" value="update" disabled/>
                <input type="hidden" name="type" value="pj" disabled/>
                <input type="hidden" name="user_id" value="<?= $profile->users->id; ?>" disabled/>
                <div class="flex">
                    <div class="box_one">
                        <?php if (!empty($profile->users->photo)): ?>
                            <div class="page_main_img">
                                <div class="j_profile_image thumb"
                                     style="background-image: url('<?= image($profile->users->photo, 320, 320); ?>')"></div>
                                <label for='selecao-arquivo'
                                       class="btn btn-small gradient gradient-blue gradient-hover transition icon-picture-o">Alterar</label>
                                <input id='selecao-arquivo' data-image=".j_profile_image" type="file" name="photo" disabled/>
                            </div>
                        <?php else: ?>
                            <div class="page_main_img">
                                <div class="j_profile_image thumb"><?= substr($profile->users->first_name, 0, 1); ?></div>
                                <label for='selecao-arquivo'
                                       class="btn btn-small gradient gradient-blue gradient-hover transition icon-picture-o">Adicionar</label>
                                <input id='selecao-arquivo' data-image=".j_profile_image" type="file" name="photo" disabled/>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="box_two">
                        <h3>Informações</h3>
                        <div class="main_modal_form">
                            <div class="group">
                                <label>
                                    <div class="gruop_label"><span class="">*Razão Social:</span></div>
                                    <input type="text" name="corporate_name" placeholder="Razão Social"
                                           value="<?= $profile->users->corporate_name; ?> " required disabled/>
                                </label>
                                <label>
                                    <div class="gruop_label"><span class="">*Nome Fantasia:</span></div>
                                    <input type="text" name="fantasy_name" placeholder="Nome Fantasia"
                                           value="<?= $profile->users->fantasy_name; ?>"
                                           required disabled/>
                                </label>
                            </div>
                            <div class="group">
                                <label>
                                    <div class="gruop_label"><span class="">*Nome Responsável:</span></div>
                                    <input type="text" name="name" placeholder="Nome Responsável"
                                           value="<?= $profile->users->first_name; ?> <?= $profile->users->last_name; ?>"
                                           required disabled/>
                                </label>
                                <label>
                                    <div class="group_label"><span class="">Data de Nascimento:</span></div>
                                    <input type="date" name="datebirth"
                                           value="<?= $profile->users->datebirth; ?>"
                                           placeholder="dd/mm/yyyy" disabled/>
                                </label>
                            </div>
                            <div class="group">
                                <label>
                                    <div class="group_label"><span class="">*CNPJ</span></div>
                                    <input type="text" class="mask-pj" name="document"
                                           value="<?= $profile->users->document; ?>"
                                           placeholder="CNPJ" disabled/>
                                </label>
                                <label>
                                    <div class="group_label"><span class="">Inscrição Estadual</span></div>
                                    <input type="text" class="" name="document_state"
                                           value="<?= $profile->users->document_state; ?>"
                                           placeholder="Inscrição Estadual" disabled/>
                                </label>
                                <label>
                                    <div class="group_label"><span class="">Inscrição Municipal</span></div>
                                    <input type="text" class="" name="document_municipal"
                                           value="<?= $profile->users->document_municipal; ?>"
                                           placeholder="Inscrição Municipal" disabled/>
                                </label>
                            </div>

                            <div class="group">
                                <label>
                                    <div class="group_label"><span class="">*Status</span></div>
                                    <span class="switch-field">
                                        <input type="radio" id="radio-one" name="switch-status"
                                               value="confirmed" <?= ($profile->users->status == "confirmed" ? "checked" : ""); ?> disabled/>
                                        <label for="radio-one">Ativo</label>
                                        <input type="radio" id="radio-two" name="switch-status"
                                               value="registered" <?= ($profile->users->status == "registered" ? "checked" : ""); ?> disabled/>
                                        <label for="radio-two">Inativo</label for="radio-two">
                                    </span>
                                </label>
                                <label>
                                    <div class="group_label"><span class="">*Envio</span></div>
                                    <span class="switch-field">
                                        <input type="radio" id="radio-one-send" name="switch-send"
                                               value="1" <?= ($profile->users->send == "1" ? "checked" : ""); ?> disabled/>
                                        <label for="radio-one-send">Ativo</label>
                                        <input type="radio" id="radio-two-send" name="switch-send"
                                               value="0" <?= ($profile->users->send == "0" ? "checked" : ""); ?> disabled/>
                                        <label for="radio-two-send">Inativo</label for="radio-two-send">
                                    </span>
                                </label>
                                <label>
                                    <div class="group_label"><span class="">Termos:</span></div>
                                    <span class="switch-field">
                                        <input type="radio" id="radio-one-privacy" name="switch-privacy"
                                               value="accept" <?= ($profile->users->privacy == "accept" ? "checked" : ""); ?> disabled/>
                                        <label for="radio-one-privacy">Aceito</label>
                                        <input type="radio" id="radio-two-privacy" name="switch-privacy"
                                               value="reject" <?= ($profile->users->privacy == "reject" ? "checked" : ""); ?> disabled/>
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
                                           value="<?= $profile->users->email; ?>" required disabled/>
                                </label>

                                <label>
                                    <div class="group_label"><span class="">Telefone Celular:</span></div>
                                    <input type="text" name="phone_cell" class="mask-cell"
                                           placeholder="(00) 00000-0000"
                                           value="<?= $profile->users->phone_cell; ?>" disabled/>
                                </label>

                                <label>
                                    <div class="group_label"><span class="">Telefone Responsável:</span></div>
                                    <input type="text" class="mask-cell" name="phone_residential"
                                           placeholder="(00) 00000-0000"
                                           value="<?= $profile->users->phone_residential; ?>" disabled/>
                                </label>

                                <label>
                                    <div class="group_label"><span class="">Telefone Comercial:</span></div>
                                    <input type="text" class="mask-phone" name="phone_commercial"
                                           placeholder="(00) 0000-0000"
                                           value="<?= $profile->users->phone_commercial; ?>" disabled/>
                                </label>
                            </div>
                            <div class="group">
                                <label>
                                    <div class="group_label"><span class="">Aceita receber SMS</span></div>
                                    <span class="switch-field">
                                        <input type="radio" id="radio-one-sms" name="switch-sms"
                                               value="1" <?= ($profile->users->despatch_sms == "1" ? "checked" : ""); ?> disabled/>
                                        <label for="radio-one-sms">Sim</label>
                                        <input type="radio" id="radio-two-sms" name="switch-sms"
                                               value="0" <?= ($profile->users->despatch_sms == "0" ? "checked" : ""); ?> disabled/>
                                        <label for="radio-two-sms">Não</label for="radio-two-sms">
                                    </span>
                                </label>

                                <label>
                                    <div class="group_label"><span class="">WhatsApp</span></div>
                                    <span class="switch-field">
                                        <input type="radio" id="radio-one-whatsapp" name="switch-whatsapp"
                                               value="1" <?= ($profile->users->despatch_whatsapp == "1" ? "checked" : ""); ?> disabled/>
                                        <label for="radio-one-whatsapp">Sim</label>
                                        <input type="radio" id="radio-two-whatsapp" name="switch-whatsapp"
                                               value="0" <?= ($profile->users->despatch_whatsapp == "0" ? "checked" : ""); ?> disabled/>
                                        <label for="radio-two-whatsapp">Não</label for="radio-two-whatsapp">
                                    </span>
                                </label>

                                <label>
                                    <div class="group_label"><span class="">Telegram</span></div>
                                    <span class="switch-field">
                                        <input type="radio" id="radio-one-telegram" name="switch-telegram"
                                               value="1" <?= ($profile->users->despatch_telegram == "1" ? "checked" : ""); ?> disabled/>
                                        <label for="radio-one-telegram">Sim</label>
                                        <input type="radio" id="radio-two-telegram" name="switch-telegram"
                                               value="0" <?= ($profile->users->despatch_telegram == "0" ? "checked" : ""); ?> disabled/>
                                        <label for="radio-two-telegram">Não</label for="radio-two-telegram">
                                    </span>
                                </label>

                                <label>
                                    <div class="group_label"><span class="">Correspondência</span></div>
                                    <span class="switch-field">
                                        <input type="radio" id="radio-one-letter" name="switch-letter"
                                               value="1" <?= ($profile->users->despatch_letter == "1" ? "checked" : ""); ?> disabled/>
                                        <label for="radio-one-letter">Sim</label>
                                        <input type="radio" id="radio-two-letter" name="switch-letter"
                                               value="0" <?= ($profile->users->despatch_letter == "0" ? "checked" : ""); ?> disabled/>
                                        <label for="radio-two-letter">Não</label for="radio-two-letter">
                                    </span>
                                </label>
                            </div>
                            <div class="group">

                                <label>
                                    <div class="group_label"><span class="">Recados:</span></div>
                                    <input type="text" name="phone_messages" class="mask-cell"
                                           placeholder="(00) 00000-0000"
                                           value="<?= $profile->users->phone_messages; ?>" disabled/>
                                </label>

                                <label>
                                    <div class="group_label"><span class="">* Nome para recados:</span></div>
                                    <input type="text" name="phone_name" placeholder="Nome para recados"
                                           value="<?= $profile->users->phone_name; ?>" disabled/>
                                </label>

                                <label></label>
                                <label></label>

                            </div>

                            <label>
                                <div class="group_label"><span class="">Observação:</span></div>
                                <textarea name="obs" id="" cols="20"
                                          rows="6"><?= $profile->users->obs; ?></textarea>
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
                                            <option <?= $select(5); ?> value="5">&ofcir; Administrador Master
                                            </option>
                                        <?php else: ?>
                                            <option <?= $select(5); ?> value="5" disabled>&ofcir; Administrador
                                                Master
                                            </option>
                                        <?php endif; ?>
                                    </select>
                                </label>
                                <label></label>
                                <label></label>
                                <label></label>
                            </div>
                            <div class="app_form_footer">
                                <a class="nav--btn btn btn-light transition icon-pencil"
                                   href="<?= url("/erp/users/profile/{$profile->users->id}/edit"); ?>">Editar</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                    <form class="app_form" action="<?= url("erp/users/register"); ?>" method="post">
                        <input type="hidden" name="action" value="update" disabled/>
                        <input type="hidden" name="type" value="pf" disabled/>
                        <input type="hidden" name="user_id" value="<?= $profile->users->id; ?>" disabled/>
                        <div class="flex">
                            <div class="box_one">
                                <?php if (!empty($profile->users->photo)): ?>
                                    <div class="page_main_img">
                                        <div class="j_profile_image thumb"
                                             style="background-image: url('<?= image($profile->users->photo, 320, 320); ?>')"></div>
                                        <label for='selecao-arquivo'
                                               class="btn btn-small gradient gradient-blue gradient-hover transition icon-picture-o">Alterar</label>
                                        <input id='selecao-arquivo' data-image=".j_profile_image" type="file"
                                               name="photo" disabled/>
                                    </div>
                                <?php else: ?>
                                    <div class="page_main_img">
                                        <div class="j_profile_image thumb"><?= substr($profile->users->first_name, 0, 1); ?></div>
                                        <label for='selecao-arquivo'
                                               class="btn btn-small gradient gradient-blue gradient-hover transition icon-picture-o">Adicionar</label>
                                        <input id='selecao-arquivo' data-image=".j_profile_image" type="file"
                                               name="photo" disabled/>
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
                                                   required disabled/>
                                        </label>
                                        <label>
                                            <div class="group_label"><span class="">Data de Nascimento:</span></div>
                                            <input type="date" name="datebirth"
                                                   value="<?= $profile->users->datebirth; ?>"
                                                   placeholder="dd/mm/yyyy" disabled/>
                                        </label>
                                    </div>
                                    <div class="group">
                                        <label>
                                            <div class="group_label"><span class="">*CPF</span></div>
                                            <input type="text" class="mask-doc" name="document"
                                                   value="<?= $profile->users->document; ?>"
                                                   placeholder="CNPJ" disabled disabled/>
                                        </label>
                                        <label>
                                            <div class="group_label"><span class="">RG</span></div>
                                            <input type="text" class="" name="document_rg"
                                                   value="<?= $profile->users->document_rg; ?>"
                                                   placeholder="RG" disabled disabled/>
                                        </label>
                                    </div>
                                    <div class="group">

                                        <label class="label uploadFile">
                                            <div class="gruop_label"><span>CPF - PDF:</span></div>
                                            <span for='cpf'
                                                  class="btn-outline btn-blue-outline transition icon-file-pdf-o filename">PDF - CPF</span>
                                            <input id='cpf' type="file" name="doc_cpf" disabled/>
                                            <?php if (!empty($profile->users->doc_cpf)): ?>
                                                <a href="<?= url("/storage/" . $profile->users->doc_cpf); ?>"
                                                   target="_blank"
                                                   class="icon-file-pdf-o">Documento de CPF</a>
                                            <?php endif; ?>

                                        </label>
                                        <label class="label uploadFile">
                                            <div class="gruop_label"><span>RG - PDF: </span></div>
                                            <span for='rg'
                                                  class="btn-outline btn-blue-outline icon-file-pdf-o filename">PDF - RG</span>
                                            <input id='rg' type="file" name="doc_rg" disabled/>
                                            <?php if (!empty($profile->users->doc_rg)): ?>
                                                <a href="<?= url("/storage/" . $profile->users->doc_rg); ?>"
                                                   target="_blank"
                                                   class="icon-file-pdf-o">Documento de identidade</a>
                                            <?php endif; ?>
                                        </label>
                                    </div>

                                    <div class="group">
                                        <label>
                                            <div class="group_label"><span class="">*Status</span></div>
                                            <span class="switch-field">
                                        <input type="radio" id="radio-one" name="switch-status"
                                               value="confirmed" <?= ($profile->users->status == "confirmed" ? "checked" : ""); ?> disabled disabled/>
                                        <label for="radio-one">Ativo</label>
                                        <input type="radio" id="radio-two" name="switch-status"
                                               value="registered" <?= ($profile->users->status == "registered" ? "checked" : ""); ?> disabled disabled/>
                                        <label for="radio-two">Inativo</label for="radio-two">
                                    </span>
                                        </label>
                                        <label>
                                            <div class="group_label"><span class="">*Envio</span></div>
                                            <span class="switch-field">
                                        <input type="radio" id="radio-one-send" name="switch-send"
                                               value="1" <?= ($profile->users->send == "1" ? "checked" : ""); ?> disabled disabled/>
                                        <label for="radio-one-send">Ativo</label>
                                        <input type="radio" id="radio-two-send" name="switch-send"
                                               value="0" <?= ($profile->users->send == "0" ? "checked" : ""); ?> disabled/>
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
                                                   value="<?= $profile->users->email; ?>" required disabled/>
                                        </label>

                                        <label>
                                            <div class="group_label"><span class="">Telefone Celular:</span></div>
                                            <input type="text" name="phone_cell" class="mask-cell"
                                                   placeholder="(00) 00000-0000"
                                                   value="<?= $profile->users->phone_cell; ?>" disabled/>
                                        </label>

                                        <label>
                                            <div class="group_label"><span class="">Telefone Residencial:</span></div>
                                            <input type="text" class="mask-phone" name="phone_residential"
                                                   placeholder="(00) 0000-0000"
                                                   value="<?= $profile->users->phone_residential; ?>" disabled/>
                                        </label>

                                        <label>
                                            <div class="group_label"><span class="">Telefone Comercial:</span></div>
                                            <input type="text" class="mask-phone" name="phone_commercial"
                                                   placeholder="(00) 0000-0000"
                                                   value="<?= $profile->users->phone_commercial; ?>" disabled/>
                                        </label>
                                    </div>
                                    <div class="group">
                                        <label>
                                            <div class="group_label"><span class="">Aceita receber SMS</span></div>
                                            <span class="switch-field">
                                        <input type="radio" id="radio-one-sms" name="switch-sms"
                                               value="1" <?= ($profile->users->despatch_sms == "1" ? "checked" : ""); ?> disabled/>
                                        <label for="radio-one-sms">Sim</label>
                                        <input type="radio" id="radio-two-sms" name="switch-sms"
                                               value="0" <?= ($profile->users->despatch_sms == "0" ? "checked" : ""); ?> disabled/>
                                        <label for="radio-two-sms">Não</label for="radio-two-sms">
                                    </span>
                                        </label>

                                        <label>
                                            <div class="group_label"><span class="">WhatsApp</span></div>
                                            <span class="switch-field">
                                        <input type="radio" id="radio-one-whatsapp" name="switch-whatsapp"
                                               value="1" <?= ($profile->users->despatch_whatsapp == "1" ? "checked" : ""); ?> disabled/>
                                        <label for="radio-one-whatsapp">Sim</label>
                                        <input type="radio" id="radio-two-whatsapp" name="switch-whatsapp"
                                               value="0" <?= ($profile->users->despatch_whatsapp == "0" ? "checked" : ""); ?> disabled/>
                                        <label for="radio-two-whatsapp">Não</label for="radio-two-whatsapp">
                                    </span>
                                        </label>

                                        <label>
                                            <div class="group_label"><span class="">Telegram</span></div>
                                            <span class="switch-field">
                                        <input type="radio" id="radio-one-telegram" name="switch-telegram"
                                               value="1" <?= ($profile->users->despatch_telegram == "1" ? "checked" : ""); ?> disabled/>
                                        <label for="radio-one-telegram">Sim</label>
                                        <input type="radio" id="radio-two-telegram" name="switch-telegram"
                                               value="0" <?= ($profile->users->despatch_telegram == "0" ? "checked" : ""); ?> disabled/>
                                        <label for="radio-two-telegram">Não</label for="radio-two-telegram">
                                    </span>
                                        </label>

                                        <label>
                                            <div class="group_label"><span class="">Correspondência</span></div>
                                            <span class="switch-field">
                                        <input type="radio" id="radio-one-letter" name="switch-letter"
                                               value="1" <?= ($profile->users->despatch_letter == "1" ? "checked" : ""); ?> disabled/>
                                        <label for="radio-one-letter">Sim</label>
                                        <input type="radio" id="radio-two-letter" name="switch-letter"
                                               value="0" <?= ($profile->users->despatch_letter == "0" ? "checked" : ""); ?> disabled/>
                                        <label for="radio-two-letter">Não</label for="radio-two-letter">
                                    </span>
                                        </label>


                                    </div>
                                    <div class="group">

                                        <label>
                                            <div class="group_label"><span class="">Recados:</span></div>
                                            <input type="text" name="phone_messages" class="mask-cell"
                                                   placeholder="(00) 00000-0000"
                                                   value="<?= $profile->users->phone_messages; ?>" disabled/>
                                        </label>

                                        <label>
                                            <div class="group_label"><span class="">* Nome para recados:</span></div>
                                            <input type="text" name="phone_name" placeholder="Nome para recados"
                                                   value="<?= $profile->users->phone_name; ?>" disabled/>
                                        </label>

                                        <label></label>
                                        <label></label>

                                    </div>

                                    <label>
                                        <div class="group_label"><span class="">Observação:</span></div>
                                        <textarea name="obs" id="" cols="20" disabled
                                                  rows="6"><?= $profile->users->obs; ?></textarea>
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
                                            <select name="level" required disabled>
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
                                                    <option <?= $select(5); ?> value="5">&ofcir; Administrador Master
                                                    </option>
                                                <?php else: ?>
                                                    <option <?= $select(5); ?> value="5" disabled>&ofcir; Administrador
                                                        Master
                                                    </option>
                                                <?php endif; ?>
                                            </select>
                                        </label>
                                        <label></label>
                                        <label></label>
                                        <label></label>
                                    </div>
                                    <div class="app_form_footer">
                                        <a class="nav--btn btn btn-light transition icon-pencil"
                                           href="<?= url("/erp/users/profile/{$profile->users->id}/edit"); ?>">Editar</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>

                <?php else: ?>

                <?php if ($profile->users->type == "pj") : ?>
                <form class="app_form" action="<?= url("erp/users/register"); ?>" method="post">
                    <input type="hidden" name="action" value="update"/>
                    <input type="hidden" name="type" value="pj"/>
                    <input type="hidden" name="user_id" value="<?= $profile->users->id; ?>"/>
                    <div class="flex">
                        <div class="box_one">
                            <?php if (!empty($profile->users->photo)): ?>
                                <div class="page_main_img">
                                    <div class="j_profile_image thumb"
                                         style="background-image: url('<?= image($profile->users->photo, 320, 320); ?>')"></div>
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
                                        <div class="gruop_label"><span class="">*Razão Social:</span></div>
                                        <input type="text" name="corporate_name" placeholder="Razão Social"
                                               value="<?= $profile->users->corporate_name; ?> " required/>
                                    </label>
                                    <label>
                                        <div class="gruop_label"><span class="">*Nome Fantasia:</span></div>
                                        <input type="text" name="fantasy_name" placeholder="Nome Fantasia"
                                               value="<?= $profile->users->fantasy_name; ?>"
                                               required/>
                                    </label>
                                </div>
                                <div class="group">
                                    <label>
                                        <div class="gruop_label"><span class="">*Nome Responsável:</span></div>
                                        <input type="text" name="name" placeholder="Nome Responsável"
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
                                        <div class="group_label"><span class="">*CNPJ</span></div>
                                        <input type="text" class="mask-pj" name="document"
                                               value="<?= $profile->users->document; ?>"
                                               placeholder="CNPJ"/>
                                    </label>
                                    <label>
                                        <div class="group_label"><span class="">Inscrição Estadual</span></div>
                                        <input type="text" class="" name="document_state"
                                               value="<?= $profile->users->document_state; ?>"
                                               placeholder="Inscrição Estadual"/>
                                    </label>
                                    <label>
                                        <div class="group_label"><span class="">Inscrição Municipal</span></div>
                                        <input type="text" class="" name="document_municipal"
                                               value="<?= $profile->users->document_municipal; ?>"
                                               placeholder="Inscrição Municipal"/>
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
                                               value="accept" <?= ($profile->users->privacy == "accept" ? "checked" : ""); ?> disabled/>
                                        <label for="radio-one-privacy">Aceito</label>
                                        <input type="radio" id="radio-two-privacy" name="switch-privacy"
                                               value="reject" <?= ($profile->users->privacy == "reject" ? "checked" : ""); ?> disabled/>
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
                                        <div class="group_label"><span class="">Telefone Responsável:</span></div>
                                        <input type="text" class="mask-cell" name="phone_residential"
                                               placeholder="(00) 00000-0000"
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
                                    <textarea name="obs" id="" cols="20"
                                              rows="6"><?= $profile->users->obs; ?></textarea>
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
                                                <option <?= $select(5); ?> value="5">&ofcir; Administrador Master
                                                </option>
                                            <?php else: ?>
                                                <option <?= $select(5); ?> value="5" disabled>&ofcir; Administrador
                                                    Master
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
                        <form class="app_form" action="<?= url("erp/users/register"); ?>" method="post">
                            <input type="hidden" name="action" value="update"/>
                            <input type="hidden" name="type" value="pf"/>
                            <input type="hidden" name="user_id" value="<?= $profile->users->id; ?>"/>
                            <div class="flex">
                                <div class="box_one">
                                    <?php if (!empty($profile->users->photo)): ?>
                                        <div class="page_main_img">
                                            <div class="j_profile_image thumb"
                                                 style="background-image: url('<?= image($profile->users->photo, 320, 320); ?>')"></div>
                                            <label for='selecao-arquivo'
                                                   class="btn btn-small gradient gradient-blue gradient-hover transition icon-picture-o">Alterar</label>
                                            <input id='selecao-arquivo' data-image=".j_profile_image" type="file"
                                                   name="photo"/>
                                        </div>
                                    <?php else: ?>
                                        <div class="page_main_img">
                                            <div class="j_profile_image thumb"><?= substr($profile->users->first_name, 0, 1); ?></div>
                                            <label for='selecao-arquivo'
                                                   class="btn btn-small gradient gradient-blue gradient-hover transition icon-picture-o">Adicionar</label>
                                            <input id='selecao-arquivo' data-image=".j_profile_image" type="file"
                                                   name="photo"/>
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
                                                    <a href="<?= url("/storage/" . $profile->users->doc_cpf); ?>"
                                                       target="_blank"
                                                       class="icon-file-pdf-o">Documento de CPF</a>
                                                <?php endif; ?>

                                            </label>
                                            <label class="label uploadFile">
                                                <div class="gruop_label"><span>RG - PDF: </span></div>
                                                <span for='rg'
                                                      class="btn-outline btn-blue-outline icon-file-pdf-o filename">PDF - RG</span>
                                                <input id='rg' type="file" name="doc_rg"/>
                                                <?php if (!empty($profile->users->doc_rg)): ?>
                                                    <a href="<?= url("/storage/" . $profile->users->doc_rg); ?>"
                                                       target="_blank"
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
                                            <textarea name="obs" id="" cols="20"
                                                      rows="6"><?= $profile->users->obs; ?></textarea>
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
                                                        <option <?= $select(5); ?> value="5">&ofcir; Administrador Master
                                                        </option>
                                                    <?php else: ?>
                                                        <option <?= $select(5); ?> value="5" disabled>&ofcir; Administrador
                                                            Master
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
                        </form>
                    <?php endif; ?>

                <?php endif; ?>


        </div>
    <?php else: ?>
        <br>
        <div class="message info icon-info border">Ainda não existe usuário cadastrado.</div>
    <?php endif; ?>
</div>




