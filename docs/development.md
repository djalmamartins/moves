# Desenvolvimento

Use uma branch de trabalho e commits semanticamente coerentes. Antes de cada
commit execute:

```bash
composer validate --strict
composer dump-autoload --optimize
composer test
```

Para mudanças visuais, valide desktop, tablet e mobile em light e dark mode.
Não edite `.env`, uploads, certificados, backups ou bundles gerados como se
fossem código-fonte. Utilitários de manutenção ficam em `service/` e devem ser
idempotentes sempre que possível.
