<?php $this->layout("layouts/erp"); ?>
<article class="main">
    <div class="container">
        <section class="main_page">
            <header>
                <div class="nav--title">
                    <h2>Informações do usuário: <?= $userEdit->fullName(); ?></h2>
                </div>

                <a class="nav--btn btn gradient gradient-blue gradient-hover transition icon-reply-all"
                   onclick="history.go(-1);">Voltar
                </a>
            </header>
            <?php $this->insert("components/users/sidebar"); ?>
            <form class="app_form" action="<?= url("erp/users/profile/{$userEdit->id}"); ?>" method="post">
                <input type="hidden" name="action" value="update"/>
                <article class="flex">
                    <div class="box_one">
                        <div class="thumb-photo">
                            <?php if (!empty($userEdit->photo)): ?>
                                <div class="j_profile_image thumb" style="background-image: url('<?= image($userEdit->photo, 600, 600); ?>')"></div>
                            <?php else: ?>
                                <div class="j_profile_image thumb"></div>
                            <?php endif; ?>
                        </div>
                        <span class="legend">Foto: (600px600px)</span>
                        <label for='selecao-arquivo'  class="btn gradient gradient-blue gradient-hover transition icon-picture-o">Alterar foto</label>
                        <input id='selecao-arquivo' data-image=".j_profile_image" type="file" name="photo"/>
                        <span class="al-center">Personalize sua conta com uma foto. Sua foto de perfil aparecerá em aplicativos e dispositivos.</span>
                    </div>
                    <div class="box_two">
                        <div class="main_modal_form">
                            <label>
                                <div class="gruop_label"><span class="">* Nome Completo:</span></div>
                                <input type="text" name="name" placeholder="Primeiro nome" value="<?= $userEdit->first_name; ?> <?= $userEdit->last_name; ?>" required/>
                            </label>
                            <div class="gruop">
                                <label>
                                    <div class="gruop_label"><span class="">Data de Nascimento:</span></div>
                                    <input type="date" name="datebirth" value="<?= $userEdit->datebirth; ?>" placeholder="dd/mm/yyyy"/>
                                </label>
                                <label>
                                    <div class="gruop_label"><span class="">* E-mail:</span></div>
                                    <input type="email" name="email" placeholder="Melhor e-mail" value="<?= $userEdit->email; ?>"  required/>
                                </label>
                            </div>

                            <div class="gruop">
                                <label>
                                    <div class="gruop_label"><span class="">* CPF</span></div>
                                    <input type="text" class="mask-doc" name="document" value="<?= $userEdit->document; ?>" placeholder="CPF do usuário" required/>
                                </label>
                                <label>
                                    <div class="gruop_label"><span class="">Identidade (RG):</span></div>
                                    <input type="text" name="document_rg" value="<?= $userEdit->document_rg; ?>" placeholder="RG do usuário"/>
                                </label>
                            </div>

                            <div class="gruop">
                                <label>
                                    <div class="gruop_label"><span class="">* Celular:</span></div>
                                    <input type="text" name="phone_cell" class="mask-cell" placeholder="(00) 00000-0000" value="<?= $userEdit->phone_cell; ?>" required/>
                                </label>
                                <label>
                                    <div class="gruop_label"><span class="">Telefone:</span></div>
                                    <input type="text" class="mask-phone" name="phone" placeholder="(00) 0000-0000" value="<?= $userEdit->phone; ?>"/>
                                </label>
                            </div>
                            <div class="gruop">
                                <label>
                                    <div class="gruop_label"><span class="">Forma de Contato:</span></div>
                                    <select name="despatch">
                                        <?php
                                        $despatch = $userEdit->despatch;
                                        $select = function ($value) use ($despatch) {
                                            return ($despatch == $value ? "selected" : "");
                                        };
                                        ?>
                                        <option <?= $select("all"); ?> value="all">Todos</option>
                                        <option <?= $select("e-mail"); ?> value="e-mail">E-mail</option>
                                        <option <?= $select("whatsapp"); ?> value="whatsapp">Whatsapp</option>
                                        <option <?= $select("telegram"); ?> value="telegram">Telegram</option>
                                        <option <?= $select("other"); ?> value="other">Correspondência</option>
                                    </select>
                                </label>
                                <label>
                                    <div class="gruop_label"><span class="">* Nivel de Acesso:</span></div>
                                    <select name="level" required>
                                        <?php
                                        $level = $userEdit->level;
                                        $select = function ($value) use ($level) {
                                            return ($level == $value ? "selected" : "");
                                        };
                                        ?>

                                        <option <?= $select(1); ?> value="1">Usuário</option>
                                        <option <?= $select(2); ?> value="2">Administrador</option>
                                        <?php if($user->level > 2):?>
                                            <option <?= $select(5); ?> value="5" >Administrador Master</option>
                                        <?php else:?>
                                            <option <?= $select(5); ?> value="5" disabled>Administrador Master</option>
                                        <?php endif;?>
                                    </select>
                                </label>

                            </div>
                            <div class="gruop">
                                <label class="label uploadFile">
                                    <div class="gruop_label"><span>CPF - PDF:</span></div>
                                    <span for='cpf'  class="btn-outline btn-blue-outline transition icon-file-pdf-o filename">PDF - CPF</span>
                                    <input id='cpf' type="file" name="doc_cpf"/>
                                    <?php if(!empty($userEdit->doc_cpf)): ?>
                                        <a href="<?= url("/storage/" . $userEdit->doc_cpf); ?>" target="_blank"  class="icon-file-pdf-o">Documento de CPF</a>
                                    <?php endif; ?>

                                </label>
                                <label class="label uploadFile">
                                    <div class="gruop_label"><span>RG - PDF: </span></div>
                                    <span for='rg'  class="btn-outline btn-blue-outline icon-file-pdf-o filename">PDF - RG</span>
                                    <input id='rg' type="file" name="doc_rg"/>
                                    <?php if(!empty($userEdit->doc_rg)): ?>
                                        <a href="<?= url("/storage/" . $userEdit->doc_rg); ?>" target="_blank" class="icon-file-pdf-o">Documento de identidade</a>
                                    <?php endif; ?>
                                </label>
                            </div>
                            <div class="app_form_footer">
                                <button class="btn btn-blue icon-check-square-o">Atualizar</button>

                                <a href="" class="remove_link icon-warning"
                                   data-post="<?= url("/erp/users/profile/{$userEdit->id}"); ?>"
                                   data-action="delete"
                                   data-confirm="ATENÇÃO: Tem certeza que deseja excluir o usuário e todos os dados relacionados a ele? Essa ação não pode ser feita!"
                                   data-user_id="<?= $userEdit->id; ?>">Excluir Usuário</a>
                            </div>
                        </div>
                    </div>
                </article>
            </form>
        </section>
    </div>
</article>
