<div class="content_sidebar">
    <nav class="stroke">
        <ul>
            <?php
            $nav = function ($href, $title) use ($app) {
                $active = ($app == $href ? "active" : null);
                $url = url("/erp/{$href}");
                return "<li><a class=\"{$active}\" href=\"{$url}\">{$title}</a></li>";
            };

            if(!$profile->users){
                echo $nav("users/profile", "Pessoa Física (PF)");
                echo $nav("users/profile/profile_pj", "Pessoa Jurídica (PJ)");
            }else{
                if(!empty($profile->users->id)){
                    if($profile->users->type == "pj"){
                        echo $nav("users/profile/profile_pj/{$profile->users->id}", "Informações");
                    }else{
                        echo $nav("users/profile/{$profile->users->id}", "Informações");
                    }
                }else{
                    if($profile->users->type == "pj"){
                        echo $nav("users/profile/profile_pj", "Informações");
                    }else{
                        echo $nav("users/profile", "Informações");
                    }
                }

                echo $nav("users/address/{$profile->users->id}", "Endereço");
                echo $nav("users/invoices/{$profile->users->id}", "Faturas");
                echo $nav("users/historic/{$profile->users->id}", "Histórico");
            }

            ?>
    </nav>
</div>