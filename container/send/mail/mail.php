<?php $this->layout("layouts/theme", ["title" => $subject]); ?>


    <table role="presentation" cellspacing="0" cellpadding="0" width="100%" align="center" style="background: url('image/backgroudEmail.svg') #FFFFFF center top;
        border-top: 1px solid #C5A131;max-width:600px">
        <tbody>
        <tr>
            <td style=";padding:1em;text-align:center;color:#444;font-family:'Amazon Ember','Helvetica Neue',Roboto,Arial,sans-serif;font-size:14px;line-height:140%;">
                <h2><?= $subject; ?></h2>
            </td>
        </tr>
        </tbody>
    </table>
    <table role="presentation" cellspacing="0" cellpadding="0" width="100%" align="center" style="background:#FFFFFF;max-width:600px">
        <tbody>
        <tr>
            <td style="background-color:#fff;color:#444;font-family:'Amazon Ember','Helvetica Neue',Roboto,Arial,sans-serif;font-size:1em;line-height:140%;padding:25px 35px">
                <?= $message; ?>
                <br>
                <p>Atenciosamente, Equipe <?= CONF_SITE_NAME; ?>.</p>
            </td>
        </tr>
        </tbody>
    </table>