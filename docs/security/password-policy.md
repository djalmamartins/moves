# MovesOS — política de senha

Status: proposta de Discovery para revisão. Define contrato e testes; não altera
hashes, usuários, banco, formulários ou configuração de produção.

## Objetivo e princípios

Senhas devem resistir a adivinhação online/offline sem impor regras previsíveis
que reduzam usabilidade. São um fator de autenticação, não substituem rate
limiting, lockout, sessão segura ou MFA para administradores, financeiro e
autorizadores de pagamentos.

1. aceitar senhas longas e passphrases, inclusive espaços e Unicode normalizado;
2. privilegiar comprimento e bloqueio de credenciais comprometidas;
3. não exigir rotação periódica sem evidência de comprometimento;
4. nunca armazenar, registrar, enviar ou exibir senha em texto claro;
5. mudança/redefinição revoga riscos e sessões conforme política explícita;
6. parâmetros de hash são versionados e evoluem por rehash seguro.

Depende do [threat model](threat-model.md) e complementa
[rate limiting](authentication-rate-limiting.md),
[lockout](authentication-lockout.md) e [configuração segura](secure-configuration.md).

## Baseline e lacunas

`is_passwd()` valida somente limites globais; `passwd()` usa `password_hash` com
algoritmo/opções configurados e evita re-hash quando a entrada já parece hash.
`passwd_verify()` verifica e `passwd_rehash()` permite atualização após login.
`User::save()` aplica validação/hash, e `Auth::reset()` reutiliza os limites.

Lacunas: não há contrato comprovado para senha comprometida, máximo resistente a
DoS, normalização, histórico, troca autenticada, algoritmo por versão ou
revogação de sessões. Aceitar valor pré-hash em helper público cria risco de
bypass se uma borda não distinguir texto claro de hash persistido. Mensagens e
fluxos variam entre superfícies.

## Atores e responsabilidades

| Ator | Responsabilidade |
| --- | --- |
| usuário | escolher segredo exclusivo e confirmar mudança |
| identidade | validar, hashear, comparar e rehashear |
| administrador/suporte | iniciar recuperação sem conhecer/definir senha |
| segurança | parâmetros, lista comprometida e resposta a incidente |
| plataforma | secret pepper futuro, capacidade e rotação |
| auditoria | registrar ação/resultado sem senha ou hash |

## Regras de criação e validação

- mínimo e máximo exatos ficam `TBD` após UX, compatibilidade e teste de carga;
- mínimo favorece passphrase; máximo deve aceitar uso de gerenciador sem permitir
  consumo abusivo de CPU/memória;
- não exigir mistura artificial de maiúscula, número ou símbolo;
- bloquear senha comprometida/comum e variante direta de identidade conhecida;
- permitir colar, gerenciadores de senha e campo `autocomplete` adequado;
- indicador de força é orientação, não regra contraditória ao servidor;
- confirmação é comparação exata após a normalização definida;
- senha vazia nunca significa “manter atual” dentro do comando de mudança.

Política é aplicada igualmente no cadastro, convite, troca, reset, administração
autorizada e importação. API/UI recebem códigos por campo, sem revelar lista de
senhas comprometidas ou dados usados na comparação.

## Estados e transições

```text
unset → active → change_required → active
          ↘ suspected_compromise → recovery_required → active
          ↘ expired_by_policy_exception → change_required
          ↘ disabled (estado externo da conta)
```

Senha não expira por calendário como padrão. `change_required` é usado para
credencial temporária/importada ou ação administrativa justificada.
`suspected_compromise` decorre de incidente/sinal aprovado, não de toda falha de
login. Nova senha só fica `active` após persistência atômica do novo hash e
eventos/auditoria; falha mantém o estado anterior de forma consistente.

## Hashing e verificação

O serviço futuro recebe somente senha em texto na borda autorizada e retorna hash
tipado; repositório nunca aceita texto claro. Algoritmo memory-hard disponível no
runtime é preferido, com parâmetros aprovados por benchmark e ambiente. Salt é
gerado pela biblioteca. Pepper, se adotado, fica em secret store, tem versão e
plano de rotação; não é requisito presumido.

Comparação usa API constante do runtime. Rehash ocorre após autenticação válida,
em atualização atômica que não converte novamente um hash em senha. Algoritmo,
parâmetros e versão são observáveis sem registrar hash. Backup e réplica tratam
hash como dado altamente sensível.

## Senhas comprometidas e histórico

Consulta a corpus comprometido usa serviço/local dataset com privacidade (por
exemplo prefixo seguro), timeout, cache limitado e política de indisponibilidade.
Senha/hash completo nunca sai do processo. A escolha de fornecedor/dataset exige
ADR e avaliação jurídica.

Histórico, se exigido por risco/regulação, guarda apenas hashes anteriores com
retenção limitada e comparação segura; nunca permite restaurar senha. Evitar
reutilização não justifica histórico ilimitado. Parâmetros antigos continuam
verificáveis durante migração.

## Mudança, reset e efeitos

Troca autenticada exige senha atual e step-up para papel/risco aplicável. Reset
usa token de uso único, curto, vinculado à finalidade e armazenado como digest.
Suporte apenas inicia o fluxo. Após sucesso, token é consumido atomicamente,
lockout é reconciliado e sessões/tokens são revogados conforme a futura política
de sessões; notificação não contém a senha.

Alterar e-mail, MFA ou papel não troca senha implicitamente. Falha de notificação
não desfaz senha já confirmada, mas gera fila/alerta e ação segura.

## Segurança, auditoria e multi-tenancy

Senha, hash, pepper, token e respostas de challenge são proibidos em log,
auditoria, trace, métrica, URL e analytics. Redaction falha fechada. Dumps e
exports restringem acesso a hashes; suporte e operador de tenant nunca os veem.

Auditar criação por convite, troca, reset concluído/rejeitado, `change_required`,
rehash/migração agregada e mudança de política, usando ator, alvo opaco, tenant
confiável, outcome e correlation ID. A mesma identidade mantém política coerente
entre tenants; tenant não pode enfraquecer o mínimo global, apenas exigir perfil
aprovado sem acessar credenciais de outro escopo.

## Concorrência e falhas

Troca/reset usa versão da credencial ou compare-and-swap. Dois tokens/comandos
concorrentes produzem no máximo uma senha ativa; replay retorna resultado seguro
sem aplicar novamente. Hashing tem fila/limite para impedir exaustão. Falha de
storage não deixa hash parcial nem consome token antes do commit.

Indisponibilidade da checagem comprometida segue matriz definida: cadastro comum
pode falhar fechado ou aguardar; recuperação de conta crítica exige postura mais
conservadora. Bypass silencioso é proibido e a degradação é observável.

## Estratégia de testes

1. limites, espaços, Unicode e normalização são idênticos em todas as bordas;
2. senha comprometida, derivada da identidade e confirmação divergente falham;
3. senha forte longa e colada por gerenciador é aceita;
4. hash tem algoritmo/parâmetros aprovados, salt distinto e não é duplamente hash;
5. login válido rehashea somente quando necessário;
6. timing/resposta não revelam conta, histórico ou corpus comprometido;
7. troca/reset atômico resiste a concorrência, replay e token vencido;
8. falha do banco/dataset preserva estado e gera sinal seguro;
9. sucesso revoga/reconcilia sessões e lockout conforme contrato futuro;
10. senha/hash/token não aparecem em logs, traces, auditoria ou exportação;
11. tenant não enfraquece política nem consulta estado de outro;
12. benchmarks protegem CPU/memória sob carga e rate limiting;
13. teste integrado usa usuário sintético/ID 2 e nunca altera ID 1.

## Migração incremental

1. inventariar bordas, helpers, algoritmos, opções e hashes existentes;
2. publicar serviço tipado separando senha clara de hash persistido;
3. unificar validação/códigos e instrumentar sem coletar senha;
4. benchmarkar parâmetros e definir matriz de compatibilidade;
5. habilitar bloqueio de comprometidas em observe-only;
6. migrar troca/reset/cadastro uma superfície por vez;
7. rehashear oportunisticamente após login e job aprovado para casos restantes;
8. remover helper/bordas ambíguas após equivalência e cobertura.

Rollout é reversível por versão/feature flag. Hash antigo não é apagado antes de
commit confirmado; rollback mantém verificação compatível sem reduzir segurança.

## Riscos, decisões e gate

| Tema | Decisão atual | Risco | Gate |
| --- | --- | --- | --- |
| tamanho | TBD | UX ou DoS | benchmark + pesquisa UX |
| algoritmo | memory-hard preferido | runtime/custo | matriz de compatibilidade |
| comprometidas | integração abstrata | privacidade/indisponibilidade | ADR/PoC |
| histórico | só se justificado | retenção sensível | threat/legal review |
| expiração | não periódica | requisito regulatório | exceção documentada |
| pepper | não presumido | complexidade de rotação | ADR/secret store |

Exceção exige owner, motivo, público, prazo, compensação e auditoria; senha em
texto claro, log/hash exposto ou regra inferior ao mínimo global não são aceitos.
Antes da implementação, aprovar parâmetros por benchmark, normalização, política
de comprometidas, migração de hashes, sessões/reset/MFA e Issues pequenas. Esta
Discovery não autoriza rehash em massa nem mudança de senha de qualquer usuário.
