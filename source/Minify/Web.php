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
    $minCSS->add(__DIR__ . "/../../organic/styles/styles.css");
    $minCSS->add(__DIR__ . "/../../organic/styles/organic.css");

    //theme CSS
    $cssDir = scandir(__DIR__ . "/../../container/themes/" . CONF_VIEW_THEME . "/assets/css");
    foreach ($cssDir as $css) {
        $cssFile = __DIR__ . "/../../container/themes/" . CONF_VIEW_THEME . "/assets/css/{$css}";
        if (is_file($cssFile) && pathinfo($cssFile)['extension'] == "css") {
            $minCSS->add($cssFile);
        }
    }

    //Minify CSS
    $minCSS->minify(__DIR__ . "/../../container/themes/" . CONF_VIEW_THEME . "/assets/style.css");

    /**
     * JS
     */
    $minJS = new JS();
    $minJS->add(__DIR__ . "/../../organic/scripts/jquery.min.js");
    $minJS->add(__DIR__ . "/../../organic/owl/owl.carousel.js");
    $minJS->add(__DIR__ . "/../../organic/scripts/jquery.form.js");
    $minJS->add(__DIR__ . "/../../organic/scripts/jquery-ui.js");
    $minJS->add(__DIR__ . "/../../organic/scripts/jquery.mask.js");
    $minJS->add(__DIR__ . "/../../organic/scripts/highcharts.js");
    $minJS->add(__DIR__ . "/../../organic/scripts/tracker.js");
    $minJS->add(__DIR__ . "/../../organic/scripts/validation.js");

    //theme CSS
    $jsDir = scandir(__DIR__ . "/../../container/themes/" . CONF_VIEW_THEME . "/assets/js");
    foreach ($jsDir as $js) {
        $jsFile = __DIR__ . "/../../container/themes/" . CONF_VIEW_THEME . "/assets/js/{$js}";
        if (is_file($jsFile) && pathinfo($jsFile)['extension'] == "js") {
            $minJS->add($jsFile);
        }
    }

    //Minify JS
    $minJS->minify(__DIR__ . "/../../container/themes/" . CONF_VIEW_THEME . "/assets/scripts.js");
}
