<!--HEADER-->
<header class="app_header">
    <div class="app_button app_mobile" data-mobilemenu="open">
        <svg class="navbar__menu-icon" id="menu" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 13C12.5523 13 13 12.5523 13 12C13 11.4477 12.5523 11 12 11C11.4477 11 11 11.4477 11 12C11 12.5523 11.4477 13 12 13Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M19 13C19.5523 13 20 12.5523 20 12C20 11.4477 19.5523 11 19 11C18.4477 11 18 11.4477 18 12C18 12.5523 18.4477 13 19 13Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M5 13C5.55228 13 6 12.5523 6 12C6 11.4477 5.55228 11 5 11C4.44772 11 4 11.4477 4 12C4 12.5523 4.44772 13 5 13Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M12 6C12.5523 6 13 5.55228 13 5C13 4.44772 12.5523 4 12 4C11.4477 4 11 4.44772 11 5C11 5.55228 11.4477 6 12 6Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M19 6C19.5523 6 20 5.55228 20 5C20 4.44772 19.5523 4 19 4C18.4477 4 18 4.44772 18 5C18 5.55228 18.4477 6 19 6Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M5 6C5.55228 6 6 5.55228 6 5C6 4.44772 5.55228 4 5 4C4.44772 4 4 4.44772 4 5C4 5.55228 4.44772 6 5 6Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M12 20C12.5523 20 13 19.5523 13 19C13 18.4477 12.5523 18 12 18C11.4477 18 11 18.4477 11 19C11 19.5523 11.4477 20 12 20Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M19 20C19.5523 20 20 19.5523 20 19C20 18.4477 19.5523 18 19 18C18.4477 18 18 18.4477 18 19C18 19.5523 18.4477 20 19 20Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path><path d="M5 20C5.55228 20 6 19.5523 6 19C6 18.4477 5.55228 18 5 18C4.44772 18 4 18.4477 4 19C4 19.5523 4.44772 20 5 20Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
    </div>
        <div class="app_box">
            <div class="app_box_header">
                <div class="app_button app_mobile" data-mobilemenu="close">
                    <svg class="menu__x" id="x" width="25" height="24" viewBox="0 0 25 24" fill="none" stroke="currentColor" xmlns="http://www.w3.org/2000/svg"><path d="M18.9775 6L6.97754 18" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6.97754 6L18.9775 18" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </div>
                <div class="app_link">
                    <a class="link link--arrowed" href="/">
                        <strong>Site</strong>
                        <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" width="28" height="28"
                             viewBox="0 0 32 32">
                            <g fill="none" stroke="var(--app-primary)" stroke-width="1.5" stroke-linejoin="round"
                               stroke-miterlimit="10">
                                <circle class="arrow-icon--circle" cx="16" cy="16" r="15.12"></circle>
                                <path class="arrow-icon--arrow"
                                      d="M16.14 9.93L22.21 16l-6.07 6.07M8.23 16h13.98"></path>
                            </g>
                        </svg>
                    </a>
                </div>
            </div>
            <ul class="app_mobile_nav">
                <?= $this->insert("/views/sidebar_mobile"); ?>
            </ul>
        </div>
    <div class="app_button app_logo">
        <a href="<?= url("/app"); ?>" title="Retornar a Página Inicial">
            <img src="<?= themeApp("/assets/images/logo-connect-condominios.svg", CONF_VIEW_APP); ?>" alt="">
        </a>
    </div>

    <div class="app_sidebar">
        <ul class="app_nav">

        </ul>
    </div>

    <div class="app_button app_help" data-modalopen=".app_modal_contact">?</div>
    <div class="app_button app_user">
        <?php if (user()->photo()): ?>
            <img class="rounded" alt="<?= user()->first_name; ?>" title="<?= user()->first_name; ?>"
                 src="<?= image(user()->photo, 260, 260); ?>"/>
        <?php else: ?>
            <div class="rounded app_user_none"><?= substr(user()->first_name,0,1); ?></div>
        <?php endif; ?>
    </div>
</header>
