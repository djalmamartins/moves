<div class="content_sidebar">
    <nav class="stroke">
        <ul>
            <?php
            $nav = function ($href, $title) use ($app) {
                $active = ($app == $href ? "active" : null);
                $url = url("/erp/{$href}");
                return "<li><a class=\"{$active}\" href=\"{$url}\">{$title}</a></li>";
            };

            if (!$condo->select){
                echo $nav("condo/profile", "Cadastro");
            }else{
                echo $nav("condo/profile", "Informações");
                echo $nav("condo/address", "Endereço");
                echo $nav("condo/units", "Unidades");
                echo $nav("condo/accountable", "Responsável");
                echo $nav("condo/banks", "Bancos");
                echo $nav("condo/documents", "Documentos");
                echo $nav("condo/occurrences", "Ocorrências");
                echo $nav("condo/apportionment", "Rateio");
                echo $nav("condo/maintenance", "Manutenção");
                echo $nav("condo/historic", "Histórico");
            }

            ?>
    </nav>
</div>