<?php

namespace Source\Models\Settings;

use Source\Core\Connect;
use Source\Core\Model;
use RuntimeException;

/**
 * ERP | Class Settings
 *
 * @author Djalma Martins
 * @package Source\Models\Settings
 */
class Settings extends Model
{
    /**
     *  Settings constructor.
     */
    public function __construct()
    {
        parent::__construct("settings", ["id"], ["mode"]);
    }

    /**
     * @return Settings|null
     */
    public static function dados(): Settings
    {
        $settings = (new Settings())->find()->fetch();
        if ($settings) {
            return $settings;
        }

        try {
            Connect::getInstance()->exec("INSERT INTO settings
                (id, mode, site_name, site_title, site_desc, site_lang, site_domain_ssl,
                 view_theme, view_support, view_app, view_erp, view_admin, view_mail, view_upkeep,
                 mail_name, mail_address, mail_lang, mail_html, mail_auth, mail_charset,
                 pay_mode, pay_back, timezone_set, access_studio, access_erp, access_app,
                 access_site, access_support)
                VALUES
                (1, 1, 'MOVES', 'MOVES', 'Gestão condominial', 'pt_BR', 'https://localhost/erp',
                 'default', 'support', 'default', 'default', 'default', 'default', 'default',
                 'MOVES', 'no-reply@localhost', 'pt_BR', '1', '0', 'UTF-8',
                 'test', '/', 'America/Sao_Paulo', 0, 0, 0, 1, 1)
                ON DUPLICATE KEY UPDATE id = id");
        } catch (\Throwable $exception) {
            throw new RuntimeException(
                'Configuração do MOVES ausente e o bootstrap seguro não pôde ser executado.',
                0,
                $exception
            );
        }

        $settings = (new Settings())->findById(1);
        if (!$settings) {
            throw new RuntimeException('Configuração do MOVES indisponível após o bootstrap seguro.');
        }

        return $settings;
    }

}
