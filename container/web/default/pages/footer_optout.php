

<section class="condominio-cta">
    <div class="cta-content">

        <div class="cta-icon">
            <svg viewBox="0 0 64 64" aria-hidden="true">
                <rect x="8" y="12" width="48" height="44" rx="4"></rect>

                <line x1="8" y1="24" x2="56" y2="24"></line>

                <line x1="20" y1="7" x2="20" y2="17"></line>
                <line x1="44" y1="7" x2="44" y2="17"></line>

                <circle cx="19" cy="34" r="2"></circle>
                <circle cx="32" cy="34" r="2"></circle>
                <circle cx="45" cy="34" r="2"></circle>

                <circle cx="19" cy="45" r="2"></circle>
                <circle cx="32" cy="45" r="2"></circle>
                <circle cx="45" cy="45" r="2"></circle>
            </svg>
        </div>

        <div class="cta-text">
            <h2>Vamos conversar sobre o seu condomínio?</h2>

            <p>
                Solicite uma proposta personalizada e descubra como podemos<br class="desktop">
                gerar mais eficiência, economia e valorização.
            </p>
        </div>

        <div class="cta-actions">
            <a href="<?= url("/solicite-sua-proposta"); ?>" class="btn btn-primary">
                Solicitar proposta
            </a>

            <a href="https://wa.me/<?= htmlspecialchars(preg_replace('/\D+/', '', CONF_SITE_WHATSAPP)) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-whatsapp">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.5 3.5A11.8 11.8 0 0 0 12.1 0C5.6 0 .3 5.3.3 11.8c0 2.1.5 4.1 1.6 5.9L.2 24l6.5-1.7a11.8 11.8 0 0 0 5.6 1.4c6.5 0 11.8-5.3 11.8-11.8 0-3.2-1.3-6.1-3.6-8.4Zm-8.2 18.2c-1.8 0-3.6-.5-5.1-1.4l-.4-.2-3.9 1 1-3.8-.2-.4a9.8 9.8 0 1 1 8.6 4.8Zm5.4-7.3c-.3-.1-1.7-.8-2-.9-.3-.1-.5-.1-.7.2-.2.3-.8.9-.9 1.1-.2.2-.3.2-.6.1-1.7-.8-2.8-1.5-4-3.4-.3-.5.3-.5.8-1.6.1-.2 0-.4 0-.6l-.9-2.1c-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.6.1-.9.4-.3.3-1.2 1.2-1.2 2.9s1.2 3.3 1.4 3.6c.2.2 2.4 3.7 5.9 5.2 2.2.9 3.1 1 4.2.8.7-.1 1.7-.7 1.9-1.3.2-.6.2-1.2.2-1.3-.1-.1-.3-.2-.6-.3Z"/></svg>
                Falar no WhatsApp
            </a>
        </div>

    </div>
</section>
