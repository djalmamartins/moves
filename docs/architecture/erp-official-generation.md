# Geração oficial do ERP

Decisão da ERP-001: `Source\Controllers\Erp\Connect` é a única geração oficial
do ERP. O arquivo de rotas `container/apps/erp/default/default.php` já aponta
exclusivamente para esse namespace e as views existentes em
`container/apps/erp/default/components` correspondem aos fluxos Connect.

## Árvore oficial

| Superfície | Controller oficial | Rotas principais |
|---|---|---|
| Entrada, autenticação e troca de condomínio | `Connect\Erp` | `/erp`, `/erp/dash`, `/erp/dash/{condominium_id}` |
| Dashboard e sessão | `Connect\Dash` | `/erp/dash/home`, `/erp/logoff`, `/erp/plug`, `/erp/search` |
| Implantação/cadastro | `Connect\Register` | `/erp/register/*` |
| Condomínio selecionado | `Connect\Condo` | `/erp/condo/*` |
| Carteira de condomínios | `Connect\Condominium` | implementação disponível, ainda sem rota pública oficial |
| Usuários | `Connect\Users` | `/erp/users/*` |
| Financeiro | `Connect\Finance` | `/erp/finance/*` |

O contrato público é o arquivo de rotas acima. Controllers ou métodos que não
estejam registrados nele não são considerados funcionalidades disponíveis.

## Árvore congelada

`Source\Controllers\Erp\V1` fica congelada como referência temporária para
migração. Ela não pode receber rotas, funcionalidades ou correções isoladas.
Código útil deve ser portado para `Erp\Connect`, coberto por teste e então
removido da árvore V1 em tarefa própria. Nenhuma exclusão foi feita nesta decisão
para evitar perda de regras legadas ainda não catalogadas.

## Equivalências

| V1 | Connect | Tratamento |
|---|---|---|
| `Erp` | `Erp` | Connect oficial |
| `Dash` | `Dash` | Connect oficial |
| `Users` | `Users` | Connect oficial; migrar apenas lacunas comprovadas |
| `Condominium` | `Condominium` | Connect oficial para carteira |
| `Manager` | `Condo` | fluxo do condomínio selecionado |
| `Corporations` | `Register` | revisar regras antes de portar; sem rota V1 |
| — | `Finance` | exclusivo da geração oficial |

## Lacunas observadas

As rotas `/erp/permissions`, `/erp/users/profile_register*` e
`/erp/users/profile_edit/*` referenciam métodos ausentes nos controllers Connect.
Elas devem ser removidas ou implementadas nas tarefas funcionais seguintes; não
justificam reativar V1.
