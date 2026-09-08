# MovesOS — storage de arquivos

Status: proposta de Discovery para revisão. Define contratos, segurança e testes;
não move arquivos, cria bucket/tabela ou escolhe fornecedor.

## Objetivo e princípios

Arquivos devem ser recebidos, armazenados, servidos, retidos e eliminados com
owner, tenant, finalidade e integridade explícitos.

1. metadata é fonte de verdade; path/URL não autoriza acesso;
2. conteúdo enviado é não confiável, mesmo com extensão conhecida;
3. arquivo privado é padrão; publicação exige classe e workflow próprios;
4. tenant e recurso são validados em upload e download;
5. escrita usa estágio → validação → commit, sem referência parcial;
6. deleção é lifecycle auditável, não `unlink` arbitrário;
7. backend é abstração; escolha física exige ADR/PoC.

Depende da [configuração segura](../security/secure-configuration.md), do
[isolamento multi-tenant](../security/multi-tenant-data-isolation.md), das
[filas](async-processing-strategy.md) e do [threat model](../security/threat-model.md).

## Baseline e lacunas

`Source\Support\Upload` usa biblioteca de storage local, limites por imagem/
arquivo/mídia, MIME detectado por `finfo`, validação extra de imagem e remoção
restrita à raiz configurada. Studio possui mídia privada/uso, Helpdesk grava
anexos públicos, Operation persiste documentos/evidências/assinaturas e propostas
geram PDFs. Caminhos relativos aparecem em tabelas e anexos da fila de e-mail.

Lacunas: metadata canônica/tenant, classificação pública/privada, quarentena e
antimalware, checksum, upload atômico, download autorizado central, signed URL,
retenção/hold, versionamento, backup/restore, orphan reconciliation e migração de
paths. Nome/MIME/owner são tratados de formas diferentes por fluxo.

## Atores e classes

| Ator | Responsabilidade |
| --- | --- |
| uploader | enviar conteúdo no recurso/tenant autorizado |
| serviço de storage | validar contrato, metadata e lifecycle |
| scanner/processor | malware, imagem, preview e extração isolada |
| downloader | autorizar cada acesso e servir com headers seguros |
| owner de domínio | vincular arquivo ao agregado e definir retenção |
| operador | quarentena, reconciliação, backup e incidentes |

Classes iniciais: asset público versionado, conteúdo editorial publicado,
documento privado, evidência operacional, assinatura, anexo de suporte,
exportação temporária, artefato gerado e backup. Cada classe declara MIME/tamanho,
owner, acesso, retenção, transformação, scan e criticidade.

## Identidade e metadata

Arquivo possui public ID opaco separado da storage key. Metadata mínima:
tenant/scope, owner resource type/ID, class, original display name sanitizado,
detected MIME, bytes, checksum, storage backend/key, status, visibility, created
actor/time, scan/version, retention/delete timestamps, legal hold e version.

Storage key é gerada pelo servidor, sem nome do usuário, PII, tenant legível ou
`../`; não é exposta como autorização. Checksum serve a integridade/dedup dentro
do escopo, não prova de autenticidade sozinho. Dedup cross-tenant é proibida por
padrão por side-channel e ownership.

## Estados e transições

```text
requested → uploading → staged → validating → available
                 ↘ failed   ↘ quarantined → released/blocked
available → superseded → retained → delete_pending → deleted
       ↘ legal_hold ───────────────────────────────→ released
```

Somente `available` é referenciável/baixável; conteúdo publicado exige transição
adicional do domínio. `failed/blocked/deleted` são terminais para aquela versão.
Retry cria tentativa ligada ao upload ID e não duplica objeto confirmado.

Commit de metadata e vínculo ao agregado é transacional/outbox; upload externo
incompleto é reconciliado. Objeto órfão em staging expira por job; metadata sem
objeto gera incidente e nunca resposta vazia como sucesso.

## Upload e validação

1. autenticar, autorizar tenant/recurso e aplicar quota/rate limit;
2. validar quantidade, tamanho declarado e stream limit;
3. gerar upload ID/key e receber em staging não executável;
4. detectar tipo pelo conteúdo, magic bytes e parser seguro;
5. executar malware scan/sandbox e validações da classe;
6. normalizar/re-encode de imagem quando aprovado, removendo metadata sensível;
7. calcular checksum/bytes e confirmar metadata/vínculo;
8. mover/copiar atomicamente para storage final e publicar evento.

Extensão e `Content-Type` do cliente são informativos. ZIP/archive, SVG/HTML,
office macro, PDF ativo e mídia requerem política específica. Imagens têm limite
de dimensões/pixels para evitar decompression bomb. Nome original é escapado e
usado somente para exibição/download seguro.

## Download e publicação

Download resolve public ID + recurso + tenant no servidor, aplica RBAC e estado,
registra acesso sensível e transmite por endpoint/proxy ou signed URL curta,
audience-bound e não reutilizável quando necessário. Existente cross-tenant e
ausente retornam resposta indistinguível.

Headers: MIME aprovado, `Content-Disposition`, `nosniff`, CSP/sandbox quando
inline, cache por classe e nome seguro. Conteúdo privado não fica em webroot nem
URL previsível. CDN/cache key inclui visibilidade/tenant/version; revogação
invalida acesso e não depende apenas de URL expirar.

Publicação copia/promove versão imutável com owner e rollback. Substituir arquivo
cria nova versão; links estáveis resolvem versão autorizada. Download em lote/
exportação exige capability, limite, expiração e auditoria.

## Transformações e processamento assíncrono

Thumbnail, OCR, preview, transcode, scan e PDF rodam como jobs idempotentes com
resource limits, timeout e ambiente isolado. Processor relê metadata/tenant e não
recebe credencial ampla. Output possui parent/version/checksum e passa validação.

Falha mantém original em estado seguro e registra reason; não publica output
parcial. Bibliotecas/parsers são inventariados e atualizados; formato desconhecido
não é convertido por shell sem argumentos seguros.

## Quotas, retenção e deleção

Quota considera bytes staged/available/versioned por tenant e classe; números
ficam `TBD` após baseline. Reserva no início e libera na falha/deleção confirmada,
com reconciliação para corrida. Um tenant não esgota storage global sem fairness.

Retenção nasce da classe/owner e respeita legal hold. Delete é two-phase:
`delete_pending`, grace quando aplicável, objeto apagado/verificado e metadata
terminal/tombstone. Referência ativa impede deleção ou exige workflow de detach.
Restaurar cria nova transição/version; não reanima objeto após descarte físico.

## Segurança, privacidade e multi-tenancy

Controles cobrem path traversal, upload executável, polyglot, MIME confusion,
malware, XSS inline, decompression bomb, parser exploit, IDOR, signed URL leakage,
cache poisoning, orphan e backup exposto. Credenciais de backend ficam em secret
store com menor privilégio e ambientes/buckets segregados.

Metadata/query/storage key/cache/queue carregam tenant. Logs/auditoria não contêm
arquivo, signed URL, path interno ou nome com PII. Auditar upload disponível/
bloqueado, publicação, download sensível, export, hold, delete, restore, quarantine
override e mudança de policy.

## Falhas, integridade e recuperação

Backend indisponível não confirma upload/download. Retry usa stream reiniciável ou
multipart reconciliado, nunca append cego. Checksums são verificados em cópia,
restore e periodicamente por amostra/política. Corrupção/quebra de redundância é
incidente com owner.

Backup inclui objetos + metadata/keys/policy de modo consistente; restore valida
checksums e isolamento antes de liberar. Migração dual-read/write só com fonte de
verdade e cutover/rollback explícitos.

## Métricas e alertas

- bytes/objetos por classe/status, upload/download rate e latência;
- validação/scan/quarantine/failure por reason seguro;
- quota, staging age, orphan, missing object e checksum mismatch;
- signed URL/download negado e cache invalidation;
- jobs de transformação, retenção/delete e backup/restore;
- tenant/resource/key/nome não são labels irrestritas.

## Estratégia de testes

1. MIME/extensão divergente, polyglot, archive e bomb são rejeitados;
2. limites de stream, pixels, quantidade e quota resistem à concorrência;
3. staging/commit falho não cria referência disponível;
4. retry/idempotency não duplica objeto/metadata;
5. arquivo ausente/cross-tenant tem resposta indistinguível;
6. signed URL expira, respeita audience e revogação;
7. headers impedem sniffing/execução/cache privado indevido;
8. processor malicioso/falho fica isolado e não publica parcial;
9. cache, queue, metadata e key preservam tenant;
10. delete/hold/reference concorrentes seguem lifecycle;
11. orphan/missing/corrupt reconciliam e alertam;
12. backup/restore/migração preservam checksum e acesso;
13. logs/UI/auditoria não vazam conteúdo, path, URL ou PII;
14. integração usa dois tenants e ID 2, nunca altera arquivo do ID 1.

## Migração incremental

1. inventariar raízes, tabelas, paths, classes, consumers e exposição web;
2. criar catálogo/metadata e adapter do filesystem atual;
3. centralizar upload/download de uma classe privada;
4. adicionar staging, checksum, scan e reconciliação;
5. migrar Operation/Helpdesk/mídia/PDF por classe;
6. implementar lifecycle, quotas, backup e observabilidade;
7. avaliar object storage via ADR/PoC e migrar gradualmente;
8. remover acesso por path somente após equivalência observada.

## Riscos, decisões e gate

| Tema | Decisão atual | Risco | Gate |
| --- | --- | --- | --- |
| backend | abstrato/filesystem legado | escala/exposição | inventário + ADR |
| scanner | contrato, fornecedor aberto | indisponibilidade | PoC/fallback |
| download | endpoint/signed URL | vazamento por link | threat tests |
| dedup | apenas no escopo | custo | privacy/security review |
| retenção | por classe | excesso/lacuna | legal/owner |
| migração | adapter + versões | órfãos | reconciliação |

Antes de implementar: aprovar catálogo, metadata, MIME/limits, scanner, quotas,
retenção, download e Issues por classe. Exceção exige owner, classe, finalidade,
controles e expiração. Esta Discovery não move, publica ou exclui arquivos.
