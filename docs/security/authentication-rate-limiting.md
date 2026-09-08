# MovesOS — rate limiting de autenticação

Status: proposta de Discovery para revisão. Define regras e testes; não altera
controllers, sessões, banco, cache, firewall ou serviço externo.

## Objetivo e limites

Reduzir credential stuffing, força bruta, enumeração e abuso automatizado sem
bloquear usuários legítimos nem criar um canal de negação de serviço por vítima.
O controle cobre login e, futuramente, recuperação, redefinição, MFA, step-up e
emissão/rotação de sessão. Política de senha e bloqueio de conta são controles
separados.

Depende da [configuração segura](secure-configuration.md), do
[threat model](threat-model.md), do [logging](../architecture/logging-strategy.md)
e da [observabilidade](../architecture/observability-strategy.md).

## Baseline observado

`request_limit()` mantém contador apenas na sessão PHP. O login do Studio limita
oito requisições por cinco minutos sob a chave comum `studio_login`; o contador
não é compartilhado entre sessões/instâncias e pode agrupar ações diferentes do
mesmo navegador. O `Auth::attempt()` retorna mensagens distintas para identidade
ausente, senha incorreta e nível insuficiente, permitindo enumeração. Falhas já
geram log com hash da identidade, mas não há política canônica, Retry-After,
storage distribuído, progressive delay ou métrica comprovada.

Esse baseline é defesa local parcial, não implementação desta especificação.

## Atores e ativos

| Ator | Interesse/responsabilidade |
| --- | --- |
| usuário legítimo | acessar e recuperar a conta com resposta previsível |
| cliente confiável | respeitar cooldown e apresentar próxima ação |
| atacante/botnet | testar credenciais, enumerar ou bloquear vítimas |
| identidade | aplicar política antes de verificação custosa |
| suporte/segurança | investigar abuso e liberar exceção autorizada |
| plataforma | storage atômico, relógio, capacidade e alertas |

Ativos protegidos: credenciais, disponibilidade do login, privacidade da
existência da conta, capacidade de hashing, e-mail/MFA e logs de segurança.

## Chaves e escopos

Uma decisão combina buckets independentes, nunca apenas um identificador:

- origem de rede normalizada/prefixo com proxy confiável;
- identidade normalizada e pseudonimizada com segredo rotacionável;
- par origem + identidade;
- tenant/superfície somente após contexto confiável;
- limite global de emergência para proteger capacidade.

IP informado em header não é confiável sem cadeia de proxy allowlisted. E-mail,
CPF, telefone, username ou hash simples não aparecem em chave, log, métrica ou
resposta. O pseudônimo usa HMAC server-side versionado; rotação aceita janela de
transição sem revelar a identidade.

IPv6/prefixos, NAT corporativo, rede móvel e proxy são tratados por política.
Origem isolada nunca bloqueia indefinidamente uma conta; identidade isolada usa
limites progressivos e recuperação segura para resistir a DoS direcionado.

## Decisão e estados

```text
allowed → observed → delayed → limited → cooling_down → allowed
                       ↘ challenged → allowed
                       ↘ escalated → security_review
```

Cada tentativa avalia os buckets atomicamente antes do hash de senha quando
possível, registra resultado após autenticação e retorna a decisão mais restrita.
Sucesso reduz ou encerra buckets apropriados, mas não apaga evidência nem zera
imediatamente padrões distribuídos. Janela, capacidade, refill e delay são
configuração versionada por ação, superfície e ambiente.

Valores numéricos ficam `TBD` até baseline de tráfego/risco. O atual `8/5 min`
não é promovido a contrato. Mudança de política é auditada e implantada
gradualmente com modo observe-only.

## Respostas e experiência

- credencial inválida usa mensagem/status indistinguíveis para conta existente;
- limitação usa resposta genérica e `Retry-After` coerente quando seguro;
- API segue Problem Details com código estável `AUTH_RATE_LIMITED`;
- HTML preserva apenas campos não sensíveis e oferece recuperação segura;
- latência não revela se identidade, senha, tenant ou MFA estavam corretos;
- acessibilidade e localização não dependem de texto de fornecedor;
- CAPTCHA/challenge não é requisito inicial nem única defesa.

O cliente não recebe contagem, bucket atingido, existência da conta ou regra
interna. Retry repetido durante cooldown não estende indefinidamente a punição.

## Concorrência, consistência e indisponibilidade

O storage futuro suporta incremento/expiração atômicos e relógio consistente
entre instâncias. Chaves têm TTL obrigatório; políticas evitam crescimento sem
limite. Processos concorrentes não excedem materialmente a capacidade definida.

Falha do limiter segue matriz de risco por ação:

| Situação | Comportamento inicial |
| --- | --- |
| login normal, storage degradado | limite local conservador + alerta |
| recuperação/MFA de alto custo | falhar fechado ou degradar com quota mínima |
| limite global excedido | preservar health e rejeitar antes de trabalho caro |
| relógio/estado inconsistente | decisão conservadora, métrica e reconciliação |

O fallback não vira bypass previsível nem dependência única. A escolha final de
fail-open/closed por ação requer ADR e teste de carga/falha.

## Exceções e recuperação

Allowlist permanente de pessoa/IP é proibida. Exceção temporária requer escopo,
owner, motivo, aprovação, expiração e auditoria. Monitor sintético usa identidade
e origem próprias com quota separada. Rede interna continua autenticada e limitada.

Suporte não revela se a conta existe nem altera contador sem capacidade própria.
Liberação manual registra ator, alvo pseudonimizado, motivo e correlation ID.
Bloqueio persistente pertence à política específica de lockout, não ao TTL do
rate limiter.

## Segurança, auditoria e multi-tenancy

Eventos mínimos: decisão limited/challenged, mudança de política, degradação do
storage, bypass temporário e liberação. Tentativa comum pode ser agregada para
evitar volume; evento de segurança relevante não é amostrado.

Logs não guardam senha, token, body, identidade direta ou IP além da finalidade e
retenção aprovadas. Métricas usam buckets/códigos limitados, não pseudônimo ou
tenant de alta cardinalidade. Consulta detalhada exige capacidade e fica auditada.

Um tenant não consulta contagens ou padrões de outro. Limite global protege a
plataforma sem atribuir abuso a tenant não autenticado. Após autenticação, quotas
por tenant não substituem buckets de origem/identidade.

## Métricas e alertas

- tentativas e decisões por ação/superfície/resultado seguro;
- proporção limitada, delays e Retry-After;
- latência antes e durante verificação de senha;
- storage: erro, timeout, saturação e divergência de fallback;
- origens/identidades pseudônimas distintas apenas em investigação restrita;
- recuperação após cooldown e falsos positivos reportados;
- capacidade de CPU/hash e e-mail/MFA protegida.

Alertas exigem owner/runbook e consideram baseline, distribuição e ataque
coordenado. Picos legítimos de login após deploy não são classificados
automaticamente como incidente de segurança.

## Estratégia de testes

1. primeiro acesso permitido e limites de cada bucket aplicados;
2. concorrência atômica não ultrapassa quota materialmente;
3. janela/refill/cooldown usam relógio controlado e TTL expira;
4. identidade existente/inexistente produz resposta e timing equivalentes;
5. troca de sessão/cookie não contorna origem/identidade;
6. rotação de IP/botnet não contorna identidade/global;
7. ataque a uma identidade não bloqueia outras no mesmo NAT;
8. IPv4, IPv6 e proxy confiável/não confiável normalizam corretamente;
9. sucesso reduz buckets conforme política sem apagar evidência;
10. storage indisponível aciona fallback e alerta sem bypass/avalanche;
11. Retry-After é coerente e repetição não prolonga cooldown indevido;
12. logs, métricas e respostas não enumeram conta nem vazam PII/segredo;
13. dois tenants não consultam ou alteram buckets entre si;
14. mudança, exceção e liberação são autorizadas e auditadas;
15. carga mede custo de senha e proteção global antes do esgotamento.

Testes usam identidades sintéticas e ID 2 quando integração local exigir usuário;
nunca bloqueiam, modificam ou autenticam destrutivamente o usuário ID 1.

## Migração incremental

1. inventariar todas as bordas de autenticação e chamadas a `request_limit()`;
2. definir catálogo de ações, respostas e métricas em modo observe-only;
3. introduzir adapter central compatível com sessão atual;
4. adicionar storage atômico e fallback testado em ambiente isolado;
5. habilitar par origem+identidade no login de uma superfície;
6. calibrar baseline, falsos positivos e proteção de capacidade;
7. expandir para recuperação, MFA, step-up e demais superfícies;
8. remover limites locais somente após equivalência observada.

Rollout usa feature flag por ação/superfície, percentuais e kill switch auditado.
Rollback preserva contadores até expiração para não produzir desbloqueio abrupto.

## Riscos e decisões reversíveis

| Tema | Decisão atual | Risco | Gate |
| --- | --- | --- | --- |
| algoritmo | token/sliding window em ADR | burst injusto | teste concorrente |
| storage | abstrato e atômico | dependência nova | PoC/fallback |
| limites | TBD por baseline | falso positivo | observe-only |
| identidade | HMAC versionado | rotação/correlação | desenho de chaves |
| IP | múltiplos escopos | NAT/botnet | simulação realista |
| challenge | opcional | acessibilidade/vendor | threat review |
| fallback | por ação | bypass ou indisponibilidade | fault injection |

## Pontos abertos e gate

- catalogar superfícies, proxies e operações de autenticação;
- medir tráfego, distribuição, custo de hash e incidentes atuais;
- definir limites, algoritmo, storage e matriz fail-open/closed por ação;
- alinhar lockout, recuperação, MFA e step-up sem dependência circular;
- aprovar retenção, pseudonimização, owners, alertas e runbooks;
- abrir Issues executáveis pequenas para adapter, storage e cada superfície.

A aprovação desta Discovery não autoriza bloquear contas, criar tabela, instalar
serviço/CAPTCHA ou alterar mensagens de login.
