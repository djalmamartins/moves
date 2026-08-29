<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?= CONF_SITE_NAME; ?></title>
    <style>
        body {
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
            font-family: Helvetica, sans-serif;
            background-color: #F5F2E9!important;
            margin: 0 auto;
            text-align: center!important;
        }

        a {
            color: #337ab7;
            text-decoration: none;
        }

        a:hover,
        a:focus {
            color: #23527c;
            text-decoration: underline;
        }

        a:focus {
            outline: 5px auto -webkit-focus-ring-color;
            outline-offset: -2px;
        }

        .btn {
            background: #337ab7!important;
            color: #FFFFFF!important;
            padding: 1em 2em!important;
            text-decoration: none!important;
            margin: 2em auto!important;
        }

        .btn:hover,
        .btn:focus {
            background: #23527c!important;
            color: #FFFFFF!important;
            text-decoration: none!important;
        }
    </style>
</head>
<body>



<table role="presentation" cellspacing="0" cellpadding="0" width="100%" align="center" style="background:#F5F2E9;max-width:600px">
    <tbody>
    <tr>
        <td style="background:#F5F2E9;padding:20px 0 10px 0;text-align:center">
            <img src="<?= themeMail("image/logo.svg"); ?>" alt="<?= CONF_SITE_NAME; ?>" width="200" height="66" border="0">
        </td>
    </tr>
    </tbody>
</table>
<?= $this->section("content"); ?>

<table role="presentation" cellspacing="0" cellpadding="0" width="100%" align="center" style="background-color:#F1F1F1;max-width:600px">
    <tbody>
    <tr>
        <td style="background-color:#F1F1F1;color:#444;font-family:'Amazon Ember','Helvetica Neue',Roboto,Arial,sans-serif;font-size:0.8em;line-height:140%;padding:25px 35px">
            <p><strong>Estamos sempre aqui para te ajudar</strong></p>
            <p>Caso queira visualizar o seu contrato de serviço, <a href="<?= CONF_URL_SSL; ?>/termos">clique
                    aqui</a>.<br>Ah, e sempre que tiver qualquer dificuldade
                <a href="mailto:<?= CONF_MAIL_SUPPORT; ?>">entre em contato</a> com a gente.</p>
            <p><strong>Nossos horários de atendimento via chat são:</strong></p>
            <p>De segunda à sexta: das 8h30min às 18h<br></p>
        </td>
    </tr>
    </tbody>
</table>
<table role="presentation" cellspacing="0" cellpadding="0" width="100%" align="center" style="background:#F5F2E9;max-width:600px">
    <tbody>
    <tr>
        <td style="background:#F5F2E9;padding:20px 0 10px 0;text-align:center">
            <img src="<?= themeMail("image/logo-mono.svg"); ?>" alt="<?= CONF_SITE_NAME; ?>" width="140" height="46" border="0">
        </td>
    </tr>
    </tbody>
</table>
<table role="presentation" cellspacing="0" cellpadding="0" width="100%" align="center" style="background:#F5F2E9;max-width:600px">
    <tbody>
    <tr>
        <td style="background:#F5F2E9;padding:0;text-align:center">
            <ul style="width: 100%;padding:0;margin:0 auto;list-style:none;text-align:center;">
                <li style="display: inline-block;"><a href="<?= CONF_SOCIAL_FACEBOOK_PAGE; ?>"><img
                                src="<?= themeMail("image/facebook.svg"); ?>"
                                alt="<?= CONF_SITE_NAME; ?> " width="40" height="40"></a></li>
                <li style="display: inline-block;"><a href="<?= CONF_SOCIAL_INSTAGRAM_PAGE; ?>"><img
                                src="<?= themeMail("image/instagram.svg"); ?>"
                                alt="<?= CONF_SITE_NAME; ?>" width="40" height="40"></a></li>
                <li style="display: inline-block;"><a href="<?= CONF_SOCIAL_INSTAGRAM_PAGE; ?>"><img
                                src="<?= themeMail("image/linkedin.svg"); ?>"
                                alt="<?= CONF_SITE_NAME; ?>" width="40" height="40"></li>
            </ul>
        </td>
    </tr>
    </tbody>
</table>
<table role="presentation" cellspacing="0" cellpadding="0" width="100%" align="center" style="background:#F5F2E9;max-width:600px">
    <tbody>
    <tr>
        <td style="color:#444;font-family:'Amazon Ember','Helvetica Neue',Roboto,Arial,sans-serif;font-size:0.7em;line-height:140%;padding:25px 35px">
            <p>Você está recebendo este e-mail pois utiliza algum serviço nosso.<br>
                Caso não queira mais receber você pode <a href="">cancelar sua inscrição</a> a qualquer
                momento.
            </p>
            <p><?= CONF_SITE_NAME; ?>, <?= CONF_SITE_ADDR_STREET; ?>
                , <?= CONF_SITE_ADDR_NUMBER; ?> <?= CONF_SITE_ADDR_COMPLEMENT; ?>,
                <?= CONF_SITE_ADDR_CITY; ?> - <?= CONF_SITE_ADDR_STATE; ?>
                , <?= CONF_SITE_ADDR_ZIPCODE; ?></p>
            <p><a href="<?= CONF_URL_SSL; ?>"><?= CONF_URL_SSL; ?></a></p>
        </td>
    </tr>
    </tbody>
</table>

</body>
</html>
