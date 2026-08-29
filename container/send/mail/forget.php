<?php $this->layout("_theme", ["title" => "Recupere sua senha"]); ?>

<table role="presentation" cellspacing="0" cellpadding="0" width="100%" align="center" style="background: url('image/backgroudEmail.svg') #FFFFFF center top;
        border-top: 1px solid #C5A131;max-width:600px">
    <tbody>
    <tr>
        <td style=";padding:1em;text-align:center;color:#444;font-family:'Amazon Ember','Helvetica Neue',Roboto,Arial,sans-serif;font-size:14px;line-height:140%;">
            <h2>Perdeu sua senha <?= $first_name; ?>?</h2>
        </td>
    </tr>
    </tbody>
</table>
<table role="presentation" cellspacing="0" cellpadding="0" width="100%" align="center" style="background:#FFFFFF;max-width:600px">
    <tbody>
    <tr>
        <td style="background-color:#fff;color:#444;font-family:'Amazon Ember','Helvetica Neue',Roboto,Arial,sans-serif;font-size:1em;line-height:140%;padding:25px 35px">
            <p>Você está recebendo este e-mail pois foi solicitado a recuperação de senha.</p><br>
            <div class="center"><a class="btn" title='Recuperar Senha' href='<?= $forget_link; ?>'>CLIQUE AQUI PARA CRIAR UMA NOVA SENHA</a></div>
            <p><b>IMPORTANTE:</b></p>
            <p> Se não foi você que solicitou ignore o e-mail.<br> Seus dados permanecem seguros.</p>
            <br>
            <p>Atenciosamente, Equipe <?= CONF_SITE_NAME; ?>.</p>
        </td>
    </tr>
    </tbody>
</table>