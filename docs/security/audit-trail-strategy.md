# MovesOS — trilha de auditoria

Status: proposta de Discovery para revisão. Este documento define evidências de
ações relevantes; não altera `Audit`, tabelas, retenção ou permissões.

## Objetivo e princípios

A trilha deve responder, com integridade: quem (ou o quê) tentou fazer qual ação,
em qual escopo, sobre qual alvo, quando, por qual origem e com qual resultado.

1. auditoria é evidência de segurança/negócio, não log técnico ou histórico de UI;
2. eventos são append-only e não podem ser editados pelo ator auditado;
3. sucesso, negação e falha relevante são registrados;
4. valores secretos e PII desnecessária nunca entram no evento;
5. identidade, tenant e alvo vêm de contexto confiável;
6. acesso, exportação, retenção e descarte da própria trilha são auditados;
7. automação registra service actor, regra/versão e causação.

Esta proposta depende dos [eventos internos](../architecture/internal-events-strategy.md),
do [logging estruturado](../architecture/logging-strategy.md) e do
[threat model](threat-model.md).

## Distinções

| Registro | Pergunta | Pode ser amostrado? | Mutabilidade |
| --- | --- | --- | --- |
| auditoria | quem fez/tentou o quê e com qual resultado? | não | append-only |
| evento de domínio | qual fato de negócio ocorreu? | não no produtor | imutável |
| log técnico | por que a execução falhou/ficou lenta? | conforme nível | lifecycle operacional |
| atividade/timeline | o que o usuário precisa acompanhar? | produto decide | projeção reconstruível |

Um mesmo comando pode produzir evento de domínio e auditoria na mesma unidade de
consistência, depois alimentar timeline. Copiar log técnico integral para auditoria
ou usar timeline como única evidência é proibido.

## Baseline observado

`Source\Support\Audit` grava `system_audit_logs` com usuário, ação, entidade, ID,
descrição, diff sanitizado, severidade, IP, user-agent e URL. `Core\Model` audita
create/update/delete e fluxos explícitos auditam agenda, chamados, módulos,
acessos e operações. Campos sensíveis são redigidos por nome.

Lacunas identificadas:

- contexto não traz tenant, correlation/causation ID ou tipo do ator de forma
  canônica;
- cobertura depende de chamadas manuais e hooks genéricos de model;
- falha de auditoria é logada, mas a política de fail-closed varia sem classificação;
- registros não possuem evidência explícita contra alteração/remoção;
- descrição e entity/action ainda refletem nomes de tabela/CRUD em vez de
  vocabulário estável de negócio;
- acesso, exportação, retenção e ações automáticas não têm catálogo completo.

## Atores e papéis

| Tipo | Exemplos | Identidade exigida |
| --- | --- | --- |
| `user` | morador, operador, administrador | ID público + sessão/autenticação |
| `service` | integração, worker, scheduler | service account + versão/origem |
| `system` | manutenção interna controlada | processo + release + regra |
| `anonymous` | login/reset/ticket público | tentativa/correlation ID, dados minimizados |
| `support` | break-glass/impersonation | operador real + sujeito representado + motivo |

“Sistema” não é identidade suficiente para automação modificadora. Impersonation
preserva ator original, ator efetivo, aprovação e duração.

## Envelope canônico

```json
{
  "audit_id": "01J8M80ABCDEFGHJKMNPQRSTVW",
  "occurred_at": "2026-09-08T07:30:00.123Z",
  "action": "operation.visit.start",
  "outcome": "succeeded",
  "actor": { "type": "user", "id": "01J8M81ABCDEFGHJKMNPQRSTVW" },
  "tenant_id": "01J8M82000000000000000000",
  "target": { "type": "visit", "id": "01J8M83ABCDEFGHJKMNPQRSTVW" },
  "correlation_id": "936da01f-0000-4000-8000-000000000000",
  "causation_id": "01J8M84ABCDEFGHJKMNPQRSTVW",
  "source": { "surface": "operation", "channel": "web" },
  "changes": { "fields": ["status"], "before": { "status": "scheduled" }, "after": { "status": "in_progress" } },
  "reason": null,
  "metadata": {}
}
```

IDs são públicos/opacos; datas em UTC. IP, user-agent, geolocalização e device ID
são opcionais, classificados e retidos apenas com finalidade. `changes` usa
allowlist por ação, limites de tamanho/profundidade e redaction antes da gravação.

## Nomes, resultados e severidade

Ações usam `<contexto>.<recurso>.<verbo>` com contrato estável, por exemplo
`identity.role.assign`, `settings.secret.rotate` e `operation.visit.complete`.
CRUD genérico/tabela é adapter legado, não vocabulário novo.

Resultados:

- `succeeded`: mudança confirmada;
- `denied`: identidade válida sem capacidade/escopo;
- `rejected`: entrada, estado ou política inválida;
- `failed`: tentativa começou e falhou tecnicamente;
- `cancelled`: ator/sistema cancelou conforme regra;
- `unknown`: efeito externo não pôde ser confirmado e requer reconciliação.

Severidade reflete risco da ação/resultado. Falha normal de validação não vira
alerta crítico; tentativa de elevar privilégio ou remover usuário protegido pode.

## Catálogo mínimo de ações auditáveis

### Identidade e acesso

Login/logout relevante, reset solicitado/concluído, sessão revogada, MFA futuro,
papel/capacidade/override criado ou removido, troca de tenant, impersonation,
negações anormais e tentativa sobre usuário protegido (incluindo ID 1).

### Configuração e plataforma

Configuração de segurança/módulo alterada, segredo criado/rotacionado/revogado
(apenas referência), deploy/migration/baseline, backup/restore, feature flag crítica,
integração/webhook e acesso break-glass.

### Dados e operação

Create/update/delete/restore de entidade relevante, transição de estado, aprovação,
exportação em massa, download/acesso a documento sensível, visita/evidência,
chamado/SLA, ação financeira e mudança de retenção/legal hold.

### Administração da auditoria

Consulta privilegiada, exportação, mudança de política, replay, retenção, hold,
verificação de integridade e tentativa de alterar/apagar registros.

Cada domínio publica catálogo com ação, gatilho, resultado, campos permitidos,
criticidade, retenção, owner e teste obrigatório.

## Estados e consistência

```text
captured → committed → sealed → retained → expired → disposed
               ↘ failed → reconciled
                         ↘ legal_hold → released
```

- `captured`: evento construído e sanitizado;
- `committed`: persistido junto à ação ou outbox;
- `sealed`: incluído em mecanismo de integridade verificável;
- `retained`: disponível conforme política/acesso;
- `legal_hold`: descarte suspenso por ordem autorizada;
- `disposed`: eliminado por job controlado, com evidência agregada.

Ação crítica de controle (papel, segredo, restore, break-glass) falha fechada se a
auditoria transacional não puder ser registrada. Ação comum de negócio usa outbox
atômica; falha posterior do sink não reverte o commit, mas bloqueia backlog/alerta
conforme SLO. A matriz exata é definida por ação, nunca por `catch` genérico.

## Integridade e não repúdio operacional

- usuário da aplicação pode inserir, não atualizar/apagar registros selados;
- serviço de consulta possui acesso read-only e filtros obrigatórios;
- lotes encadeiam hashes/assinaturas ou são enviados a storage imutável;
- verificação periódica detecta lacuna, reordenação e alteração;
- relógio, release e origem são autenticados/monitorados;
- backup preserva integridade e restauração reexecuta verificação;
- quebra de cadeia é incidente, não correção silenciosa.

O mecanismo físico será escolhido em ADR após medir volume e requisitos legais;
hash local sozinho não protege contra administrador do mesmo banco.

## Segurança, privacidade e multi-tenancy

Tenant vem da identidade/contexto autorizado e acompanha gravação e consulta.
Eventos globais usam escopo `platform`. Busca, contagem e exportação filtram tenant
na query; nenhum ID conhecido permite atravessar escopo.

Proibidos: senha/hash, token, cookie, Authorization, CSRF, secret, chave, payload
de arquivo, conteúdo integral de mensagem, dados bancários completos e PII sem
finalidade. Antes/depois guardam campos allowlisted e valores minimizados; em
ações sensíveis, apenas nomes de campos e referência/version hash.

Descrição é texto gerado pelo servidor e escapado na UI. Campos fornecidos pelo
usuário não controlam action/type ou HTML. Exportação tem capacidade própria,
motivo, limite, expiração e auditoria.

## Consulta e experiência operacional

Filtros: período limitado, tenant/escopo, action, outcome, actor, target,
correlation ID e severidade. Ordenação é estável; paginação por cursor. Resultado
mostra resumo seguro e drill-down conforme capacidade. Busca livre não executa
SQL nem retorna valores redigidos.

Toda investigação possui incident/case ID opcional. Anotações do analista são
registros separados e append-only; não editam o evento original. Acesso massivo
ou fora do padrão gera alerta.

## Retenção, exportação e descarte

Política varia por categoria, obrigação legal e finalidade, com owner e revisão.
Prazo começa no evento, salvo hold. Jobs de retenção são idempotentes, em lotes,
observáveis e separados da requisição. Descarte produz contagens, política/versão,
período, aprovador e hash do lote, sem recriar PII descartada.

Exportações são criptografadas, access-controlled, expiram e não viram backup
informal. Solicitação LGPD concilia direito do titular, minimização e obrigação de
manter evidência, usando pseudonimização quando juridicamente adequada.

## Automações e integrações

Automação registra service actor, regra e versão, agendamento, input reference,
decisão, efeito, correlation/causation ID e resultado. Retry preserva audit ID do
fato/tentativa conforme contrato e não duplica sucesso. Webhook/integrador registra
sistema, credential reference, assinatura validada e resultado, nunca segredo.

## Estratégia de testes

1. catálogo emite evento para sucesso, negação e falha relevante;
2. ação crítica não confirma sem auditoria transacional;
3. ação comum + outbox não perde evidência após falha do sink;
4. schema, nomes, outcomes, tipos e UTC são validados;
5. actor/tenant/target vêm do contexto, não do payload;
6. dois tenants não consultam, contam ou exportam eventos entre si;
7. canary secrets/PII em campos conhecidos, neutros e objetos profundos são redigidos;
8. automação, impersonation e break-glass preservam ator real/efetivo e motivo;
9. cadeia/assinatura detecta alteração, remoção e reordenação;
10. consulta/exportação exige capacidade, limite, motivo e gera auditoria;
11. retenção respeita categoria, hold, lote e idempotência;
12. clock/retry/concorrência não duplicam nem apagam eventos;
13. adapter legado preserva comportamento enquanto a cobertura é migrada;
14. falha da auditoria produz logging/alerta sem recursão.

Testes usam canaries e banco/sink isolados; não acessam a trilha de produção.

## Migração incremental

1. inventariar `Audit::record`, hooks de Model, escritas diretas e gaps por ação;
2. criar catálogo e mapear nomes CRUD/tabela para ações de domínio;
3. introduzir envelope/adapter sem remover `system_audit_logs`;
4. adicionar tenant/correlação/ator e outbox em migrations compatíveis;
5. pilotar uma ação crítica e uma ação comum com políticas distintas;
6. separar consulta da escrita e habilitar verificação de integridade;
7. migrar domínios e comparar cobertura/duplicatas;
8. ativar retenção/exportação governadas;
9. remover adapter genérico apenas após telemetria sem uso.

Cada etapa preserva leitura anterior e rollback antes do contract. Histórico não
é reescrito para aparentar campos que nunca foram capturados.

## Exceções e incidentes

Bypass de auditoria só existe para recuperação de desastre documentada, com duas
pessoas quando aplicável, janela curta e reconciliação posterior. Falha ou quebra
de integridade bloqueia ações críticas, abre incidente e preserva evidências. Não
se “corrige” apagando ou atualizando o evento original.

## Riscos e decisões reversíveis

| Tema | Decisão atual | Risco | Revisão |
| --- | --- | --- | --- |
| persistência | append-only + outbox | volume | particionamento/retention |
| integridade | seal verificável | mecanismo não escolhido | ADR após requisitos |
| changes | allowlist/minimização | diagnóstico limitado | catálogo por ação |
| falha | política por criticidade | indisponibilidade | matriz + SLO |
| consulta | serviço/capacidade própria | complexidade | piloto no painel |
| legado | adapter de CRUD | eventos duplicados | métricas/dedup |

## Pontos abertos

- inventariar ações e classificar criticidade/retention por domínio;
- definir mecanismo físico de integridade e storage secundário;
- incluir tenant/correlation/actor types no schema futuro;
- definir política legal de IP, user-agent e PII;
- mapear ações automáticas, impersonation e break-glass;
- escolher SLO para atraso/falha da auditoria;
- projetar capacidades de consulta/exportação/hold;
- converter lacunas críticas em Issues executáveis.

## Gate para sair do Discovery

- catálogo mínimo e matriz fail-closed/outbox aprovados;
- segurança, privacidade, jurídico e domínios revisaram campos/retenção;
- integridade, consulta e recuperação possuem prova planejada;
- cobertura atual versus necessária está inventariada;
- nenhuma ação é declarada auditada apenas por estar logada.
