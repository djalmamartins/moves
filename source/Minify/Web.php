<?php

use MovesCode\Compress\CSS;
use MovesCode\Compress\JS;
if ((getenv("MOVESOS_ENV") ?: "") === "testing") {
    return;
}

if (is_local_host()) {
    /**
     * CSS
     */
    $minCSS = new CSS();
    //theme CSS
    $cssDir = scandir(__DIR__ . "/../../container/themes/" . CONF_VIEW_THEME . "/assets/css");
    foreach ($cssDir as $css) {
        $cssFile = __DIR__ . "/../../container/themes/" . CONF_VIEW_THEME . "/assets/css/{$css}";
        if (is_file($cssFile) && pathinfo($cssFile)['extension'] == "css") {
            $minCSS->add($cssFile);
        }
    }

    //Minify CSS
    $webStyleTarget = __DIR__ . "/../../container/themes/" . CONF_VIEW_THEME . "/assets/style.css";
    $minCSS->minify($webStyleTarget);
    chmod($webStyleTarget, 0664);

    /**
     * JS
     */
    $minJS = new JS();
    $minJS->add(__DIR__ . "/../../container/shared/assets/vendor/scripts/jquery.min.js");
    $minJS->add(__DIR__ . "/../../container/shared/assets/vendor/owl/owl.carousel.js");
    $minJS->add(__DIR__ . "/../../container/shared/assets/vendor/scripts/jquery.form.js");
    $minJS->add(__DIR__ . "/../../container/shared/assets/vendor/scripts/jquery-ui.js");
    $minJS->add(__DIR__ . "/../../container/shared/assets/vendor/scripts/jquery.mask.js");
    $minJS->add(__DIR__ . "/../../container/shared/assets/vendor/scripts/highcharts.js");
    $minJS->add(__DIR__ . "/../../container/shared/assets/vendor/scripts/tracker.js");
    $minJS->add(__DIR__ . "/../../container/shared/assets/vendor/scripts/validation.js");

    //theme CSS
    $jsDir = scandir(__DIR__ . "/../../container/themes/" . CONF_VIEW_THEME . "/assets/js");
    foreach ($jsDir as $js) {
        $jsFile = __DIR__ . "/../../container/themes/" . CONF_VIEW_THEME . "/assets/js/{$js}";
        if (is_file($jsFile) && pathinfo($jsFile)['extension'] == "js") {
            $minJS->add($jsFile);
        }
    }

    //Minify JS
    $webScriptTarget = __DIR__ . "/../../container/themes/" . CONF_VIEW_THEME . "/assets/scripts.js";
    $minJS->minify($webScriptTarget);
    chmod($webScriptTarget, 0664);
}
