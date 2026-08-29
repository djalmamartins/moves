<?php $this->layout("_theme", ["title" => "Confirme e ative sua conta"]); ?>


<table role="presentation" cellspacing="0" cellpadding="0" width="100%" align="center" style="background: url('image/backgroudEmail.svg') #FFFFFF center top;
        border-top: 1px solid #C5A131;max-width:600px">
    <tbody>
    <tr>
        <td style=";padding:1em;text-align:center;color:#444;font-family:'Amazon Ember','Helvetica Neue',Roboto,Arial,sans-serif;font-size:14px;line-height:140%;">
            <h2>Seja bem-vindo(a) <?= $first_name; ?>. <br>Vamos confirmar seu cadastro?</h2>
        </td>
    </tr>
    </tbody>
</table>
<table role="presentation" cellspacing="0" cellpadding="0" width="100%" align="center" style="background:#FFFFFF;max-width:600px">
    <tbody>
    <tr>
        <td style="background-color:#fff;color:#444;font-family:'Amazon Ember','Helvetica Neue',Roboto,Arial,sans-serif;font-size:1em;line-height:140%;padding:25px 35px">
            <p>É importante confirmar seu cadastro para ativar o seu acesso ao sistema e as notificações.<br>Assim podemos enviar a você avisos de vencimentos, informações e
                muito mais.</p><br>
            <p><a title='Confirmar Cadastro' href='<?= $confirm_link; ?>'>CLIQUE AQUI PARA CONFIRMAR</a></p><br>
            <br>
            <p>Atenciosamente, Equipe <?= CONF_SITE_NAME; ?>.</p>
        </td>
    </tr>
    </tbody>
</table>