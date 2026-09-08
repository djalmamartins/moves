# MovesOS — revogação de sessões

Status: proposta de Discovery para revisão. Detalha comandos, gatilhos e garantias
de propagação; não revoga sessões nem altera banco, usuários ou permissões.

## Objetivo e relação com sessões

A [estratégia de sessões revogáveis](revocable-sessions.md) define o recurso e
seus estados. Este documento define quando, por quem e com qual abrangência uma
sessão transita irreversivelmente para `revoked`, incluindo incidentes e mudanças
de segurança além do [logout](logout-strategy.md) voluntário.

Princípios: deny-by-default, alvo/escopo explícito, idempotência, propagação
mensurável, motivo estável, auditoria append-only e nenhuma reativação automática.

## Baseline e lacunas

Hoje `Auth::logout()` remove chaves da sessão PHP atual. Controllers também o
chamam após perda de acesso. `AppSession` consulta registros por usuário, porém
não há comando central comprovado que selecione sessões, grave reason/status,
invalide caches e confirme eficácia em todas as audiences/instâncias.

Não há matriz canônica para mudança de senha/MFA/papel, desativação, incidente,
admin, tenant ou service account. O estado local roubado pode continuar válido se
apenas o navegador original limpar `authUser`.

## Atores e capacidades

| Ator | Escopo máximo |
| --- | --- |
| usuário | sessão atual, uma própria, outras próprias ou todas próprias |
| administrador de tenant | sessões tenant-scoped conforme capacidade |
| segurança platform | sessões selecionadas por incidente aprovado |
| suporte | comando limitado, step-up, motivo e aprovação |
| sistema | gatilho versionado por conta, credencial, policy ou risco |
| service owner | sessões/credenciais do serviço sob sua responsabilidade |

Revogar outra pessoa, conjunto amplo, conta privilegiada ou service account exige
capacidade específica, MFA/step-up e segregação. Usuário ID 1 nunca é alvo de
teste/automação comum; break-glass aprovado é o único processo excepcional.

## Seletores e comandos

Comando canônico contém `command_id`, actor real/efetivo, reason code, selector,
expected version, correlation/causation IDs e modo `dry_run|execute`.

Seletores permitidos são fechados: `current_session`, `session_public_id`,
`actor + audience`, `actor_all`, `tenant_membership`, `policy_version`,
`credential_version` ou conjunto materializado de incidente. Query livre, cookie,
digest e filtro fornecido diretamente pelo cliente são proibidos.

Dry-run retorna contagens suprimidas/IDs autorizados e expira; executar revalida
permissão e conjunto para evitar TOCTOU. Operação ampla exige limite, paginação,
aprovação e plano de abort/reconciliação.

## Estados do comando

```text
proposed → authorized → executing → completed
    ↘ denied     ↘ cancelled  ↘ partial → reconciling → completed
                              ↘ failed  → retry/review
```

Sessão alvo segue `active → revocation_pending → revoked`; terminal permanece
terminal. O comando é idempotente por ID + payload hash. Mesmo ID com payload
diferente é conflito. `partial` não é sucesso: guarda progresso e próxima ação.

Commit marca sessões antes de publicar invalidação/outbox. A resposta diferencia
contagem solicitada, já terminal, revogada e falha apenas para ator autorizado.

## Matriz mínima de gatilhos

| Gatilho | Abrangência inicial proposta |
| --- | --- |
| logout | sessão atual |
| “sair de outros dispositivos” | todas próprias exceto atual |
| reset de senha concluído | todas anteriores, decisão sobre atual explícita |
| troca de senha autenticada | outras; atual gira conforme risco |
| MFA removido/recuperado | todas e novo login/step-up |
| papel crítico removido | sessões afetadas ou policy version invalidada |
| conta desativada/excluída | todas as sessões e tokens |
| suspeita de comprometimento | conjunto do ator/incidente |
| membership de tenant removida | acesso ao tenant, não necessariamente login global |
| secret/service account rotacionado | credenciais/sessões da versão anterior |

A matriz final exige aprovação conjunta das Issues de senha, MFA, RBAC, tenancy e
sessões. Gatilho não confirmado fica ponto aberto, não comportamento inventado.

## Propagação e consistência

O objetivo de eficácia mede tempo entre commit e primeira negação em cada
instância/audience. Valor fica `TBD` após arquitetura e baseline. Para operação
crítica, a borda compara versão/status na fonte confiável; cache TTL sozinho não
garante revogação.

Evento de invalidação carrega session/actor public ID, nova versão, scope e
causation, nunca segredo. Consumidor é idempotente. Instância atrasada falha
fechado quando não comprova versão. Jobs revalidam actor e RBAC antes de efeitos;
revogação não cancela transação já commitada por suposição.

## Falhas e reconciliação

- falha antes do commit não declara revogação;
- falha de outbox/invalidação após commit mantém sessão revogada e gera alerta;
- lotes registram cursor/progresso e retomam sem duplicar eventos;
- alvo ausente/terminal é resultado idempotente, não vazamento;
- store indisponível impede operação autenticada sensível;
- reconciliação compara fonte de verdade, caches e consumidores por versão.

Canal de alerta não depende exclusivamente da sessão/serviço afetado. Rollback de
deploy não reativa sessões; restauração de banco exige invalidar gerações antigas.

## Segurança, auditoria e multi-tenancy

Auditar proposta/aprovação/execução, alvos agregados, reason, actor, scope,
outcome, policy version, incident/ticket e correlação. Cookie, digest, token, IP
bruto sem finalidade e PII não entram. Consulta, dry-run, cancelamento e replay
administrativos também são auditados.

Selector tenant-scoped resolve membership no servidor e não alcança sessão global
ou outro tenant. Remover acesso a condomínio invalida aquele scope mesmo quando a
sessão permanece ativa para outro. Contagem ampla usa supressão para não revelar
usuários/tenants. Impersonation registra operador e sujeito.

## Experiência e notificações

Usuário vê sessões próprias e confirmação da abrangência. Revogação da atual
redireciona ao login; outras mantêm a atual após step-up. Encerramento por segurança
mostra mensagem neutra no próximo request. Notificação usa canal verificado,
deduplica por comando e não oferece link que reative a sessão.

Operação ampla mostra progresso e falhas ao operador autorizado, sem prometer
conclusão antes da reconciliação. Cancelar impede novos lotes, não desfaz sessões
já revogadas.

## Métricas e alertas

- comandos/alvos por reason, audience, outcome e policy version;
- tempo de commit, propagação e primeira negação;
- consumidores atrasados, cache divergente e uso pós-revogação;
- lotes parciais, retries, reconciliação e dead-letter;
- revogação privilegiada, break-glass e falsos positivos;
- sessão/tenant/actor nunca como label irrestrita.

Alertas possuem owner/runbook, severidade, deduplicação e recuperação verificável.

## Estratégia de testes

1. seletores resolvem somente alvos autorizados e materializados;
2. mesmo command ID/payload é idempotente; payload diferente conflita;
3. sessões terminais não reativam nem duplicam evento;
4. matriz de senha, MFA, papel, conta e tenant aplica abrangência correta;
5. commit seguido de falha de invalidação ainda nega pela fonte confiável;
6. caches/instâncias/audiences convergem dentro do objetivo;
7. lote parcial retoma pelo cursor sem pular/duplicar alvos;
8. cancelamento não desfaz sessões já revogadas;
9. job/requisição concorrente revalida antes de efeito sensível;
10. falha do store/outbox gera alerta e reconciliação segura;
11. cross-tenant e alvo inexistente não vazam informação;
12. segredo/PII não aparecem em evento, métrica, UI ou auditoria;
13. restore/rollback não reativa geração antiga;
14. integração usa ID 2 e nunca revoga/altera ID 1.

## Migração e gate

1. inventariar gatilhos, stores, caches e audiences;
2. aprovar matriz e catálogo de reason codes;
3. criar comando central/dry-run em shadow mode;
4. implementar sessão individual e medir propagação;
5. adicionar actor/audience e lotes com reconciliação;
6. integrar senha, MFA, RBAC, tenant e incidentes;
7. remover revogações locais após equivalência observada.

| Decisão aberta | Risco | Gate |
| --- | --- | --- |
| objetivo de propagação | janela de acesso | benchmark/arquitetura |
| sessão atual após troca | takeover ou UX | threat/UX review |
| lotes | impacto de banco | limites/load test |
| policy generation | invalidação excessiva | PoC |
| restore | reativação | runbook testado |

Exceção exige owner, selector exato, motivo, aprovação e expiração. Antes de
implementar, aprovar matriz, SLO de eficácia, consistência, UX e Issues pequenas.
Esta Discovery não revoga qualquer sessão real.
