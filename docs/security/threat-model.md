# MovesOS — threat model inicial

Status: proposta de Discovery para revisão. Data da revisão inicial: 2026-09-08.
Este documento identifica ameaças e prioridades; não declara controles como
implementados nem altera código, infraestrutura ou dados.

## Escopo e método

O modelo cobre as sete superfícies auditadas — Site, Suporte público, Studio,
Operation, Help Desk, ERP e Moradores — além de API, banco, filesystem/uploads,
sessões, e-mail, jobs, dependências e pipeline de deploy.

Usamos STRIDE para não omitir classes de ameaça e uma escala qualitativa:

- **Crítico:** comprometimento amplo de tenants, credenciais ou operação;
- **Alto:** acesso/modificação relevante, persistência ou indisponibilidade séria;
- **Médio:** exploração limitada, com pré-condições ou impacto recuperável;
- **Baixo:** defesa em profundidade ou impacto operacional pequeno.

Risco residual só será aceito por owner de negócio e segurança, com prazo de
revisão. Evidência futura pode mudar prioridade e tratamento.

## Ativos protegidos

1. identidades, senhas, sessões, tokens de reset e service accounts;
2. separação entre tenants, condomínios, unidades, moradores e carteiras;
3. PII, documentos, evidências, anexos, comentários e geolocalização;
4. estados operacionais de visitas, demandas, chamados e aprovações;
5. conteúdo público/editorial e configurações de marca/domínio;
6. dados financeiros, contratos e documentos de validade;
7. logs, auditoria, backups, migrations e artefatos de deploy;
8. disponibilidade e integridade das superfícies e automações.

## Atores

- visitante anônimo e usuário autenticado legítimo;
- morador, representante do condomínio, operador e administrador do tenant;
- operador da plataforma e suporte, sem acesso implícito global;
- service account, job, integração e fornecedor externo;
- atacante externo, usuário malicioso, tenant adversário e insider;
- processo comprometido, dependência vulnerável ou credencial vazada.

## Fronteiras de confiança

```text
Internet/dispositivo
  → proxy/servidor HTTP
    → Router + sessão/API
      → Web | Support | Studio | Operation | Help Desk | ERP | Residents
        → domínio/serviços → banco tenant-scoped
                         ↘ filesystem/uploads
                         ↘ filas/e-mail/webhooks
Deploy/CI → artefato → aplicação → migrations
Administradores → configuração/ACL → todas as decisões autorizadas
```

Cada seta cruza uma fronteira: dados precisam de validação, identidade precisa
ser autenticada e a ação autorizada. UI compartilhada não transfere permissão.
Studio e Operation são superfícies distintas; herança ou reuse de layout não
autoriza rotas ou dados da outra.

## Fluxos críticos

| Fluxo | Entrada | Decisão de confiança | Saída/efeito |
| --- | --- | --- | --- |
| Login/reset | credencial/token | identidade, expiração, rate limit | sessão/token renovado |
| Troca de tenant | tenant solicitado | membership + capacidade | contexto tenant ativo |
| CRUD interno | formulário/JSON | CSRF, validação, authz e estado | escrita + auditoria |
| Visita/evidência | ação, foto, local | operador, agenda, MIME e tenant | estado/arquivo/evento |
| Conteúdo editorial | HTML/mídia | permissão + sanitização | conteúdo público |
| Suporte/chamado | mensagem/anexo | identidade/canal + escopo | ticket/notificação |
| Job/webhook/e-mail | evento/payload | origem, assinatura, idempotência | entrega/efeito externo |
| Migration/deploy | artefato/SQL | commit, aprovador, alvo e lock | schema/código novo |

## Registro inicial de ameaças

| ID | STRIDE | Ameaça e cenário | Impacto | Risco | Controle/alvo |
| --- | --- | --- | --- | --- | --- |
| TM-01 | Spoofing | reset previsível, reutilizável ou sem expiração toma conta | identidade/PII | Crítico | `random_bytes`, hash, uso único, TTL, rate limit e revogação |
| TM-02 | Elevation | ACL legada falha aberta para nível alto | acesso entre funções/módulos | Crítico | política fail-closed, capacidade explícita e testes negativos |
| TM-03 | Elevation/Disclosure | ID/tenant do cliente resolve recurso de outro tenant | vazamento/alteração transversal | Crítico | contexto confiável + filtro `tenant_id` na consulta |
| TM-04 | Tampering | Operation herda rotas/permissões Studio | ação fora do papel operacional | Alto | controllers e matrizes de rotas/capacidades separados |
| TM-05 | Tampering | POST autenticado sem CSRF consistente | escrita em nome da vítima | Alto | token por sessão, SameSite e teste de todas as mutações |
| TM-06 | Information disclosure | HTML editorial não sanitizado executa script | sessão/PII/conteúdo | Alto | sanitizador por allowlist + CSP + escaping contextual |
| TM-07 | Tampering/Disclosure | upload poliglota, path traversal ou acesso direto | malware/PII/RCE | Crítico | MIME real, nome opaco, storage fora do webroot, scan e authz |
| TM-08 | Spoofing | cookie/sessão roubado permanece válido | tomada de sessão | Alto | Secure/HttpOnly/SameSite, rotação, idle/absolute TTL, revogação |
| TM-09 | Repudiation | ação privilegiada sem auditoria íntegra | investigação impossível | Alto | evento imutável com ator, tenant, alvo e correlação |
| TM-10 | Disclosure | logs/erros guardam token, SQL, documentos ou PII | exposição massiva | Alto | redaction, minimização, acesso e retenção |
| TM-11 | DoS | login, busca, upload, PDF ou relatórios sem limites | indisponibilidade/custo | Alto | quotas por ator/tenant, limites e filas |
| TM-12 | Tampering | replay de job/webhook duplica ação | estados/cobranças/notificações | Alto | assinatura, timestamp, idempotência e deduplicação |
| TM-13 | Spoofing/Tampering | webhook ou integração sem autenticação forte | escrita externa falsa | Alto | segredo rotativo/mTLS, assinatura e scopes |
| TM-14 | Disclosure | backup/dump/arquivo temporário exposto | todos os tenants | Crítico | criptografia, segregação, acesso mínimo e expiração |
| TM-15 | Tampering | migration no alvo errado ou arquivo aplicado alterado | corrupção/indisponibilidade | Crítico | allowlist de alvo, checksum, lock, aprovação e restore testado |
| TM-16 | Supply chain | pacote, asset CDN ou build comprometido | execução no cliente/servidor | Alto | lockfile, pinning, SBOM, revisão e CSP/SRI quando aplicável |
| TM-17 | Disclosure | IDs sequenciais enumeram usuários/condomínios | descoberta/PII | Alto | ID público opaco, `not_found` uniforme e rate limit |
| TM-18 | DoS/Tampering | concorrência ou retry muda estado duas vezes | corrupção de fluxo | Alto | versão otimista, transação e idempotency key |
| TM-19 | Disclosure | geolocalização/evidências acessíveis além da finalidade | privacidade/LGPD | Alto | capacidade específica, minimização, retenção e auditoria |
| TM-20 | Repudiation | service account compartilhada sem owner | ação não atribuível | Médio | identidade individual, scopes, rotação e owner |

## Controles existentes observados

A auditoria encontrou prepared statements nas consultas novas, CSRF nas mutações
principais, regeneração de sessão no login, cookies HttpOnly/SameSite, validação
de MIME/tamanho em uploads, redaction de logs e proteção explícita do usuário ID
1. Esses são sinais positivos, não cobertura completa: cada superfície e caminho
alternativo ainda precisa de teste integrado.

## Lacunas prioritárias

### P0 — tratar antes de ampliar acesso ou dados

- substituir e expirar o token de recuperação legado (TM-01);
- remover autorização fail-open e provar matriz de capacidades (TM-02);
- provar isolamento tenant no repositório e em rotas/IDs (TM-03/TM-17);
- garantir upload privado e protegido fora de execução pública (TM-07);
- reconciliar migrations/baseline antes de deploy (TM-15).

### P1 — tratar antes de produção ampla

- separar fronteiras Studio/Operation e revisar herança de rotas (TM-04);
- inventariar CSRF, sanitização HTML e política CSP (TM-05/TM-06);
- formalizar sessão, logs, auditoria, rate limiting e idempotência;
- proteger backup, integrações, jobs e supply chain.

## Estados e tratamento do risco

```text
identified → triaged → planned → mitigating → verified → accepted/closed
                   ↘ monitoring        ↘ reopened
```

Todo risco possui ID, ativo, cenário, impacto, probabilidade, owner, controle,
evidência e próxima revisão. `verified` exige teste/evidência independente.
`accepted` exige justificativa, aprovador, risco residual e expiração; não é
sinônimo de ignorado. Mudança de arquitetura, incidente ou nova integração
reabre a análise afetada.

## Requisitos por fronteira

### Identidade e sessão

Senhas usam algoritmo adaptativo; reset é aleatório, armazenado em hash, expira,
tem uso único e revoga sessões quando apropriado. Login, reset e MFA futuro têm
rate limiting sem permitir enumeração. Sessões rotacionam no login/elevação,
possuem TTL e logout/revogação efetivos.

### Autorização e tenants

Autorização combina ator, tenant, capacidade e recurso. Ausência de política
falha fechada. Operador da plataforma não recebe dados globais implicitamente.
IDs públicos não concedem acesso; `not_found` cobre ausente e invisível.

### Entrada, conteúdo e arquivos

Validação estrutural e de domínio, SQL parametrizado, escaping contextual e
sanitização HTML por política explícita. Upload usa nome opaco, diretório não
executável/privado, limites, inspeção, autorização de download e expiração.

### Serviços e operação

Jobs e webhooks autenticam origem, deduplicam e limitam retry. Segredos ficam em
store apropriado e rotacionam. Logs e auditoria são separados por finalidade,
protegidos contra alteração e minimizados. Backup tem restauração testada.

## Estratégia de testes de segurança

1. autenticação: brute force, enumeração, reset expirado/reusado e revogação;
2. autorização: matriz positiva/negativa por superfície e capacidade;
3. tenancy: dois tenants em leitura, escrita, busca, exportação e arquivos;
4. sessão/CSRF: rotação, fixation, cookies e cada método mutável;
5. entrada: SQLi, XSS armazenado/refletido, mass assignment e redirect aberto;
6. upload: MIME falso, dupla extensão, oversized, traversal e acesso direto;
7. concorrência/replay: mesma ação/job/webhook repetidos;
8. erros/logs: nenhum segredo, PII, stack ou existência cross-tenant;
9. dependências/deploy: lockfile, scan, artefato e migration no alvo recusado;
10. backup/restore: acesso, criptografia, expiração e restauração verificável.

Testes usam banco/identidades isolados e nunca atacam produção. Scans automáticos
complementam revisão de fluxo e testes de abuso; achado sem reprodução recebe
triagem, não é descartado silenciosamente.

## Monitoramento e resposta

Alertas devem cobrir falhas repetidas de login/reset, negações anormais, acesso
cross-tenant, upload rejeitado, mudança de papel/configuração, exportação em massa,
jobs em retry, divergência de migration e redaction falha. Cada alerta tem owner,
severidade, runbook e teste. Relógios e IDs de correlação permitem reconstruir a
sequência sem guardar conteúdo sensível.

## Exceções

Bypass temporário exige Issue, ameaça afetada, escopo, owner, expiração, detecção
compensatória e plano de remoção. Urgência nunca autoriza expor credencial, usar
dados de outro tenant, desativar auditoria ou tornar upload executável.

## Riscos e decisões reversíveis

| Tema | Decisão atual | Risco residual | Revisão |
| --- | --- | --- | --- |
| método | STRIDE + risco qualitativo | subjetividade | calibrar com evidências |
| tenant | filtro junto ao recurso | legado sem `tenant_id` | inventário por tabela/rota |
| conteúdo | HTML sanitizado | compatibilidade editorial | política/adapter por campo |
| uploads | privado por padrão | legado no webroot | migração gradual |
| terceiros | mínimo necessário | dependência de CDN/API | inventário e fallback |
| auditoria | eventos mínimos | volume/retenção | especificação da Issue #12 |

## Pontos abertos

- inventariar cada rota mutável e capacidade efetivamente aplicada;
- classificar tabelas globais versus tenant-scoped e seus arquivos;
- documentar diagrama de fluxo de login/reset/sessão por superfície;
- confirmar local e exposição real de todos os uploads e backups;
- listar integrações, secrets, callbacks, CDNs e dados transferidos;
- executar modelagem específica de financeiro, documentos e geolocalização;
- transformar TM-01 a TM-20 em Issues executáveis com owner e aceite.

## Gate para sair do Discovery

- fronteiras e fluxos revisados por engenharia, produto, segurança e operação;
- ativos e classificação de dados confirmados por domínio;
- riscos Crítico/Alto possuem Issue, owner, prioridade e mitigação testável;
- riscos aceitos possuem aprovador e prazo de reavaliação;
- modelo atualizado após auditoria autenticada, sem declarar cobertura inexistente.
