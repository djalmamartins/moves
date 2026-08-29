<div class="content_sidebar">
    <nav class="stroke">
        <ul>
            <?php
            $nav = function ($href, $title) use ($app) {
                $active = ($app == $href ? "active" : null);
                $url = url("/erp/{$href}");
                return "<li><a class=\"{$active}\" href=\"{$url}\">{$title}</a></li>";
            };

            echo $nav("register/users", "Pessoa Física (PF)");
            echo $nav("register/users-pj", "Pessoa Jurídica (PJ)");


            ?>
    </nav>
</div>