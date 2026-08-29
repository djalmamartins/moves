<?php $this->layout("_erp"); ?>


<div class="container">

    <div class="page_main_header">
        <header>
            <h2>Informações</h2>
        </header>
    </div>


    <div class="page_main">
        <form class="app_form" action="<?= url("erp/condo/register"); ?>" method="post">

            <?php if(!$condo->edit): ?>
                <input type="hidden" name="action" value="update" disabled />
                <input type="hidden" name="condo_id" value="<?= $condo->select->id; ?>" disabled />
                <div class="flex">
                    <div class="box_one">

                        <?php if (!empty($condo->select->photo)):?>
                            <div class="page_main_img">
                                <div class="j_profile_image thumb"
                                     style="background-image: url('<?= image($condo->select->photo, 320, 320); ?>')"></div>
                                <label for='selecao-arquivo'
                                       class="btn btn-small gradient gradient-blue gradient-hover transition icon-picture-o">Alterar</label>
                                <input id='selecao-arquivo' data-image=".j_profile_image" type="file" name="photo" disabled />
                            </div>
                        <?php else: ?>
                            <div class="page_main_img">
                                <div class="j_profile_image thumb"><?= substr($condo->select->condo_name, 0, 1); ?></div>
                                <label for='selecao-arquivo'
                                       class="btn btn-small gradient gradient-blue gradient-hover transition icon-picture-o">Adicionar</label>
                                <input id='selecao-arquivo' data-image=".j_profile_image" type="file" name="photo" disabled />
                            </div>
                        <?php endif; ?>

                    </div>
                    <div class="box_two">
                        <h3>Informações</h3>
                        <div class="main_modal_form">
                            <div class="group">
                                <label>
                                    <div class="gruop_label"><span class="">*Razão Social:</span></div>
                                    <input type="text" name="corporate_name" placeholder="Razão Social" value="<?= $condo->select->condo_name; ?>"
                                           required disabled />
                                </label>
                                <label>
                                    <div class="group_label"><span class="">*CNPJ</span></div>
                                    <input type="text" class="mask-pj" name="document"
                                           value="<?= $condo->select->document; ?>"
                                           placeholder="CNPJ" disabled />
                                </label>
                            </div>

                            <div class="group">
                                <label>
                                    <div class="group_label"><span class="">Inscrição Estadual</span></div>
                                    <input type="text" class="" name="document_state"
                                           value="<?= $condo->select->document_state; ?>"
                                           placeholder="Inscrição Estadual" disabled />
                                </label>
                                <label>
                                    <div class="group_label"><span class="">Inscrição Municipal</span></div>
                                    <input type="text" class="" name="document_municipal"
                                           value="<?= $condo->select->document_municipal; ?>"
                                           placeholder="Inscrição Municipal" disabled />
                                </label>
                            </div>
                            <div class="group">

                                <label>
                                    <div class="group_label"><span class="">Data da Fundação:</span></div>
                                    <input type="date" name="datebirth"
                                           value="<?= $condo->select->datebirth; ?>"
                                           placeholder="dd/mm/yyyy" disabled />
                                </label>
                                <label>
                                    <div class="group_label"><span class="">*Status</span></div>
                                    <span class="switch-field">
                                    <input type="radio" id="radio-one" name="switch-status"
                                           value="confirmed" <?= ($condo->select->status == "confirmed" ? "checked" : ""); ?> disabled />
                                    <label for="radio-one">Ativo</label>
                                    <input type="radio" id="radio-two" name="switch-status"
                                           value="registered" <?= ($condo->select->status == "registered" ? "checked" : ""); ?> disabled />
                                    <label for="radio-two">Inativo</label for="radio-two">
                                </span>
                                </label>
                                <label>
                                    <div class="group_label"><span class="">*Envio</span></div>
                                    <span class="switch-field">
                                    <input type="radio" id="radio-one-send" name="switch-send"
                                           value="1" <?= ($condo->select->send == "1" ? "checked" : ""); ?> disabled />
                                    <label for="radio-one-send">Ativo</label>
                                    <input type="radio" id="radio-two-send" name="switch-send"
                                           value="0" <?= ($condo->select->send == "0" ? "checked" : ""); ?> disabled />
                                    <label for="radio-two-send">Inativo</label for="radio-two-send">
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
                                    <input type="email" name="email" placeholder="Melhor e-mail" value="<?= $condo->select->email; ?>" required disabled />
                                </label>

                                <label>
                                    <div class="group_label"><span class="">Telefone Celular:</span></div>
                                    <input type="text" name="phone_cell" class="mask-cell"
                                           placeholder="(00) 00000-0000" value="<?= $condo->select->phone_cell; ?>" disabled />
                                </label>

                                <label>
                                    <div class="group_label"><span class="">Telefone Condomínio:</span></div>
                                    <input type="text" class="mask-phone" name="phone_residential"
                                           placeholder="(00) 0000-0000" value="<?= $condo->select->phone_residential; ?>" disabled />
                                </label>

                                <label>
                                    <div class="group_label"><span class="">Telefone Comercial:</span></div>
                                    <input type="text" class="mask-phone" name="phone_commercial"
                                           placeholder="(00) 0000-0000" value="<?= $condo->select->phone_commercial; ?>" disabled />
                                </label>
                            </div>
                            <div class="group">
                                <label>
                                    <div class="group_label"><span class="">Aceita receber SMS</span></div>
                                    <span class="switch-field">
                                    <input type="radio" id="radio-one-sms" name="switch-sms"
                                           value="1" <?= ($condo->select->despatch_sms == "1" ? "checked" : ""); ?> disabled />
                                    <label for="radio-one-sms">Sim</label>
                                    <input type="radio" id="radio-two-sms" name="switch-sms"
                                           value="0" <?= ($condo->select->despatch_sms == "0" ? "checked" : ""); ?> disabled />
                                    <label for="radio-two-sms">Não</label for="radio-two-sms">
                                </span>
                                </label>

                                <label>
                                    <div class="group_label"><span class="">WhatsApp</span></div>
                                    <span class="switch-field">
                                    <input type="radio" id="radio-one-whatsapp" name="switch-whatsapp"
                                           value="1" <?= ($condo->select->despatch_whatsapp == "1" ? "checked" : ""); ?> disabled />
                                    <label for="radio-one-whatsapp">Sim</label>
                                    <input type="radio" id="radio-two-whatsapp" name="switch-whatsapp"
                                           value="0" <?= ($condo->select->despatch_whatsapp == "0" ? "checked" : ""); ?> disabled />
                                    <label for="radio-two-whatsapp">Não</label for="radio-two-whatsapp">
                                </span>
                                </label>

                                <label>
                                    <div class="group_label"><span class="">Telegram</span></div>
                                    <span class="switch-field">
                                    <input type="radio" id="radio-one-telegram" name="switch-telegram"
                                           value="1" <?= ($condo->select->despatch_telegram == "1" ? "checked" : ""); ?> disabled />
                                    <label for="radio-one-telegram">Sim</label>
                                    <input type="radio" id="radio-two-telegram" name="switch-telegram"
                                           value="0" <?= ($condo->select->despatch_telegram == "0" ? "checked" : ""); ?> disabled />
                                    <label for="radio-two-telegram">Não</label for="radio-two-telegram">
                                </span>
                                </label>

                                <label>
                                    <div class="group_label"><span class="">Correspondência</span></div>
                                    <span class="switch-field">
                                    <input type="radio" id="radio-one-letter" name="switch-letter"
                                           value="1" <?= ($condo->select->despatch_letter == "1" ? "checked" : ""); ?> disabled />
                                    <label for="radio-one-letter">Sim</label>
                                    <input type="radio" id="radio-two-letter" name="switch-letter"
                                           value="0" <?= ($condo->select->despatch_letter == "0" ? "checked" : ""); ?> disabled />
                                    <label for="radio-two-letter">Não</label for="radio-two-letter">
                                </span>
                                </label>
                            </div>
                            <div class="group">

                                <label>
                                    <div class="group_label"><span class="">*Contato CNPJ:</span></div>
                                    <input type="text" name="phone_messages" class="mask-cell"
                                           value="<?= $condo->select->phone_messages; ?>"
                                           placeholder="(00) 00000-0000" disabled />
                                </label>

                                <label>
                                    <div class="group_label"><span class="">*Responsável CNPJ:</span></div>
                                    <input type="text" name="phone_name"
                                           value="<?= $condo->select->phone_name; ?>"
                                           placeholder="Nome para recados"  disabled />
                                </label>

                                <label></label>
                                <label></label>

                            </div>

                            <label>
                                <div class="group_label"><span class="">Observação:</span></div>
                                <textarea name="obs" id="" cols="20" rows="6" disabled><?= $condo->select->obs; ?></textarea>
                            </label>
                            <div class="app_form_footer">
                                <a class="nav--btn btn btn-light transition icon-pencil"
                                   href="<?= url("/erp/condo/profile/edit"); ?>">Editar</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <input type="hidden" name="action" value="update"/>
                <input type="hidden" name="condo_id" value="<?= $condo->select->id; ?>"/>
                <div class="flex">
                    <div class="box_one">

                        <?php if (!empty($condo->select->photo)):?>
                            <div class="page_main_img">
                                <div class="j_profile_image thumb"
                                     style="background-image: url('<?= image($condo->select->photo, 320, 320); ?>')"></div>
                                <label for='selecao-arquivo'
                                       class="btn btn-full  gradient gradient-blue gradient-hover transition icon-picture-o">Alterar</label>
                                <input id='selecao-arquivo' data-image=".j_profile_image" type="file" name="photo"/>
                            </div>
                        <?php else: ?>
                            <div class="page_main_img">
                                <div class="j_profile_image thumb"><?= substr($condo->select->condo_name, 0, 1); ?></div>
                                <label for='selecao-arquivo'
                                       class="btn btn-full gradient gradient-blue gradient-hover transition icon-picture-o">Adicionar</label>
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
                                    <input type="text" name="corporate_name" placeholder="Razão Social" value="<?= $condo->select->condo_name; ?>"
                                           required/>
                                </label>
                                <label>
                                    <div class="group_label"><span class="">*CNPJ</span></div>
                                    <input type="text" class="mask-pj" name="document"
                                           value="<?= $condo->select->document; ?>"
                                           placeholder="CNPJ"/>
                                </label>
                            </div>

                            <div class="group">
                                <label>
                                    <div class="group_label"><span class="">Inscrição Estadual</span></div>
                                    <input type="text" class="" name="document_state"
                                           value="<?= $condo->select->document_state; ?>"
                                           placeholder="Inscrição Estadual"/>
                                </label>
                                <label>
                                    <div class="group_label"><span class="">Inscrição Municipal</span></div>
                                    <input type="text" class="" name="document_municipal"
                                           value="<?= $condo->select->document_municipal; ?>"
                                           placeholder="Inscrição Municipal"/>
                                </label>
                            </div>
                            <div class="group">

                                <label>
                                    <div class="group_label"><span class="">Data da Fundação:</span></div>
                                    <input type="date" name="datebirth"
                                           value="<?= $condo->select->datebirth; ?>"
                                           placeholder="dd/mm/yyyy"/>
                                </label>
                                <label>
                                    <div class="group_label"><span class="">*Status</span></div>
                                    <span class="switch-field">
                                    <input type="radio" id="radio-one" name="switch-status"
                                           value="confirmed" <?= ($condo->select->status == "confirmed" ? "checked" : ""); ?>/>
                                    <label for="radio-one">Ativo</label>
                                    <input type="radio" id="radio-two" name="switch-status"
                                           value="registered" <?= ($condo->select->status == "registered" ? "checked" : ""); ?>/>
                                    <label for="radio-two">Inativo</label for="radio-two">
                                </span>
                                </label>
                                <label>
                                    <div class="group_label"><span class="">*Envio</span></div>
                                    <span class="switch-field">
                                    <input type="radio" id="radio-one-send" name="switch-send"
                                           value="1" <?= ($condo->select->send == "1" ? "checked" : ""); ?>/>
                                    <label for="radio-one-send">Ativo</label>
                                    <input type="radio" id="radio-two-send" name="switch-send"
                                           value="0" <?= ($condo->select->send == "0" ? "checked" : ""); ?>/>
                                    <label for="radio-two-send">Inativo</label for="radio-two-send">
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
                                    <input type="email" name="email" placeholder="Melhor e-mail" value="<?= $condo->select->email; ?>" required/>
                                </label>

                                <label>
                                    <div class="group_label"><span class="">Telefone Celular:</span></div>
                                    <input type="text" name="phone_cell" class="mask-cell"
                                           placeholder="(00) 00000-0000" value="<?= $condo->select->phone_cell; ?>"/>
                                </label>

                                <label>
                                    <div class="group_label"><span class="">Telefone Condomínio:</span></div>
                                    <input type="text" class="mask-phone" name="phone_residential"
                                           placeholder="(00) 0000-0000" value="<?= $condo->select->phone_residential; ?>"/>
                                </label>

                                <label>
                                    <div class="group_label"><span class="">Telefone Comercial:</span></div>
                                    <input type="text" class="mask-phone" name="phone_commercial"
                                           placeholder="(00) 0000-0000" value="<?= $condo->select->phone_commercial; ?>"/>
                                </label>
                            </div>
                            <div class="group">
                                <label>
                                    <div class="group_label"><span class="">Aceita receber SMS</span></div>
                                    <span class="switch-field">
                                    <input type="radio" id="radio-one-sms" name="switch-sms"
                                           value="1" <?= ($condo->select->despatch_sms == "1" ? "checked" : ""); ?>/>
                                    <label for="radio-one-sms">Sim</label>
                                    <input type="radio" id="radio-two-sms" name="switch-sms"
                                           value="0" <?= ($condo->select->despatch_sms == "0" ? "checked" : ""); ?>/>
                                    <label for="radio-two-sms">Não</label for="radio-two-sms">
                                </span>
                                </label>

                                <label>
                                    <div class="group_label"><span class="">WhatsApp</span></div>
                                    <span class="switch-field">
                                    <input type="radio" id="radio-one-whatsapp" name="switch-whatsapp"
                                           value="1" <?= ($condo->select->despatch_whatsapp == "1" ? "checked" : ""); ?>/>
                                    <label for="radio-one-whatsapp">Sim</label>
                                    <input type="radio" id="radio-two-whatsapp" name="switch-whatsapp"
                                           value="0" <?= ($condo->select->despatch_whatsapp == "0" ? "checked" : ""); ?>/>
                                    <label for="radio-two-whatsapp">Não</label for="radio-two-whatsapp">
                                </span>
                                </label>

                                <label>
                                    <div class="group_label"><span class="">Telegram</span></div>
                                    <span class="switch-field">
                                    <input type="radio" id="radio-one-telegram" name="switch-telegram"
                                           value="1" <?= ($condo->select->despatch_telegram == "1" ? "checked" : ""); ?>/>
                                    <label for="radio-one-telegram">Sim</label>
                                    <input type="radio" id="radio-two-telegram" name="switch-telegram"
                                           value="0" <?= ($condo->select->despatch_telegram == "0" ? "checked" : ""); ?>/>
                                    <label for="radio-two-telegram">Não</label for="radio-two-telegram">
                                </span>
                                </label>

                                <label>
                                    <div class="group_label"><span class="">Correspondência</span></div>
                                    <span class="switch-field">
                                    <input type="radio" id="radio-one-letter" name="switch-letter"
                                           value="1" <?= ($condo->select->despatch_letter == "1" ? "checked" : ""); ?>/>
                                    <label for="radio-one-letter">Sim</label>
                                    <input type="radio" id="radio-two-letter" name="switch-letter"
                                           value="0" <?= ($condo->select->despatch_letter == "0" ? "checked" : ""); ?>/>
                                    <label for="radio-two-letter">Não</label for="radio-two-letter">
                                </span>
                                </label>
                            </div>
                            <div class="group">

                                <label>
                                    <div class="group_label"><span class="">*Contato CNPJ:</span></div>
                                    <input type="text" name="phone_messages" class="mask-cell"
                                           value="<?= $condo->select->phone_messages; ?>"
                                           placeholder="(00) 00000-0000"/>
                                </label>

                                <label>
                                    <div class="group_label"><span class="">*Responsável CNPJ:</span></div>
                                    <input type="text" name="phone_name"
                                           value="<?= $condo->select->phone_name; ?>"
                                           placeholder="Nome para recados"/>
                                </label>

                                <label></label>
                                <label></label>

                            </div>

                            <label>
                                <div class="group_label"><span class="">Observação:</span></div>
                                <textarea name="obs" id="" cols="20" rows="6"><?= $condo->select->obs; ?></textarea>
                            </label>
                            <div class="app_form_footer">
                                <button class="btn gradient gradient-blue gradient-hover transition icon-check-square-o">
                                    Atualizar
                                </button>

                                <a href="" class="remove_link icon-warning"
                                   data-post="<?= url("/erp/users/register/{$condo->select->id}"); ?>"
                                   data-action="delete"
                                   data-confirm="ATENÇÃO: Tem certeza que deseja excluir a administradora e todos os dados relacionados a ele? Essa ação não pode ser feita!"
                                   data-condominium_id="<?= $condo->select->id; ?>">Excluir</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </form>
    </div>

