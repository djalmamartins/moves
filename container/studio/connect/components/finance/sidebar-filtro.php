<div class="content_sidebar">
    <nav class="stroke">
        <ul>
            <?php
            $nav = function ($href, $title) use ($app) {
                $active = ($app == $href ? "active" : null);
                $url = url("/erp/{$href}");
                return "<li><a class=\"{$active}\" href=\"{$url}\">{$title}</a></li>";
            };

            echo $nav("$app", "Todos");
            echo $nav("$app/s", "Simples");
            echo $nav("$app/p", "Parceladas");
            echo $nav("$app/r", "Recorrentes");

            ?>
    </nav>

    <div class="filter">
        <form class="search_form">
            <input id="s" type="text">
            <label for="search"></label>
        </form>


        <div>
            <span class="btn_filter icon-filter">Filtros</span>
            <span class="btn_filter_exit">Limpar</span>
        </div>

    </div>


</div>