<article class="main_modal_box">
    <header class="al-center">
        <h1>Suporte</h1>
    </header>
    <div class="main_modal_form">
        <form class="app_form" action="<?= url("/app/support"); ?>" method="post" enctype="multipart/form-data">
            <?= csrf_input(); ?>
            <label>
                <div><span class="icon-user">O que precisa?</span></div>
                <select name="subject" required>
                    <option value="Pedido de suporte">&ofcir; Preciso de suporte</option>
                    <option value="Dúvida sobre o Certificado">&ofcir; Dúvida sobre o Certificado</option>
                    <option value="Nova sugestão">&ofcir; Enviar uma sugestão</option>
                    <option value="Nova reclamação">&ofcir; Enviar uma reclamação</option>
                </select>
            </label>
            <label>

                <div><span class="icon-lock">Mensagem:</span></div>
                <textarea class="radius" name="message" rows="4" required></textarea>

            </label>

            <button class="btn gradient gradient-blue gradient-hover transition icon-paper-plane-o">
                Enviar
            </button>
        </form>
    </div>
</article>
