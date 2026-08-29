$(function () {
    const $menu = $(".j_menu_mobile_tab");
    const $open = $(".j_menu_mobile_open");

    $open.on("click", function () {
        $(this).attr("aria-expanded", "true");
        $("body").css("overflow", "hidden");
        window.setTimeout(function () { $(".j_menu_mobile_close").trigger("focus"); }, 220);
    });

    function closeMobileMenu() {
        if (window.innerWidth <= 1000) {
            $menu.stop(true).animate({left: "100%"}, 200, function () {
                $menu.css({right: "auto", left: "auto", display: "none"});
            });
            $open.attr("aria-expanded", "false").trigger("focus");
            $("body").css("overflow", "");
        }
    }

    $(".j_menu_mobile_close").on("click", function () {
        $open.attr("aria-expanded", "false");
        $("body").css("overflow", "");
    });
    $menu.find("a").on("click", closeMobileMenu);
    $(document).on("keydown", function (event) {
        if (event.key === "Escape" && $open.attr("aria-expanded") === "true") closeMobileMenu();
    });
});
