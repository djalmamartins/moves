<?php

namespace Source\Models\Talk;

use Source\Core\Model;

/**
 * @deprecated O módulo Talk nunca recebeu tabela ou fluxo funcional.
 *
 * @author Djalma Martins
 * @package Source\Models\Talk
 */
class Talk extends Model
{
    /**
     * Talk constructor.
     */
    public function __construct()
    {
        throw new \LogicException('Talk foi removido: não existe tabela ou fluxo funcional associado.');
    }
}
