# Observabilidade, auditoria e incidentes

## Responsabilidades

- `app_log`: eventos técnicos e incidentes. Agrupa reincidências pelo fingerprint, registra request/incident ID, contexto sanitizado, stack trace e estado de tratamento.
- `system_audit_logs`: trilha imutável de ações de negócio, com ator, entidade, campos alterados e origem. Não substitui logs técnicos.
- `report_access`: métricas agregadas de acesso; não deve receber detalhes de erro ou alterações de negócio.
- `report_online`: presença efêmera para estimativa de sessões ativas.
- `storage/logs/movesos-*.log`: contingência quando o banco ou o próprio logger falha.

## Busca e tratamento

Incidentes são pesquisáveis no Studio por código, mensagem, código técnico ou URL e filtráveis por nível, estado e canal. Um incidente crítico novo notifica os desenvolvedores; ocorrências equivalentes em cinco minutos incrementam o contador do mesmo fingerprint. O fluxo de tratamento é `open` → `resolved` ou `ignored`; incidentes podem ser reabertos. Incidentes abertos nunca são excluídos automaticamente.

## Retenção

| Fonte | Retenção automatizada | Regra |
|---|---:|---|
| Incidentes resolvidos/ignorados | 90 dias | usa `last_seen_at` |
| Incidentes abertos | sem expiração automática | remoção exige tratativa explícita |
| Auditoria de negócio | 5 anos | usa `created_at` |
| Relatórios de acesso | 13 meses | usa `created_at` |
| Presença online | 1 dia | usa `updated_at` |
| Arquivos de contingência | operação manual | preservar enquanto houver incidente aberto relacionado |

`composer observability:retention` executa somente uma simulação e informa quantos registros são elegíveis. A exclusão exige `composer observability:retention -- --apply`, roda em transação e deve ser agendada apenas após backup e definição do responsável operacional.

## Resposta a incidentes

1. Localizar o incident ID e confirmar canal, nível, primeira/última ocorrência e impacto.
2. Correlacionar pelo request ID, sem copiar credenciais ou dados pessoais para tickets.
3. Conter o impacto e registrar a decisão no ticket técnico.
4. Corrigir, validar e marcar como resolvido; usar ignorado somente para evento conhecido e aceito.
5. Reabrir em caso de recorrência e revisar alertas e prazo de retenção quando houver obrigação legal.
