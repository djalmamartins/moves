<?php

namespace Source\Models\Banking;

use Source\Core\Model;

/**
 * @deprecated Sem tabela de domínio. Use Source\Models\Erp\AppWallet para
 *             carteiras ou Source\Models\Banking\AppBankInter para a API.
 *
 * @author Djalma Martins
 * @package Source\Models\Banking
 */
class Banking extends Model
{
    /**
     * AppCondominium constructor. constructor.
     */
    public function __construct()
    {
        throw new \LogicException(
            'Banking foi removido: use AppWallet para carteiras ou AppBankInter para integração bancária.'
        );
    }
}
