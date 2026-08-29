<?php

use MovesCode\Compress\CSS;
use MovesCode\Compress\JS;
if ((getenv("MOVESOS_ENV") ?: "") === "testing") {
    return;
}

$erpThemePath = __DIR__ . "/../../container/studio/" . CONF_VIEW_ERP;
$erpStyleTarget = $erpThemePath . "/assets/style.css";
$erpScriptTarget = $erpThemePath . "/assets/scripts.js";
$erpAssetsWritable = (is_file($erpStyleTarget) ? is_writable($erpStyleTarget) : is_writable($erpThemePath . "/assets"))
    && (is_file($erpScriptTarget) ? is_writable($erpScriptTarget) : is_writable($erpThemePath . "/assets"));

if (is_local_host()
    && is_dir($erpThemePath . "/assets/css")
    && is_dir($erpThemePath . "/assets/js")
    && $erpAssetsWritable) {
    /**
     * CSS
     */
    $minCSS = new CSS();
    $minCSS->add(__DIR__ . "/../../container/shared/assets/vendor/owl/owl.carousel.min.css");
    $minCSS->add(__DIR__ . "/../../container/shared/assets/vendor/owl/owl.theme.default.min.css");
    $minCSS->add(__DIR__ . "/../../organic/organic.min.css");
    $minCSS->add(__DIR__ . "/../../organic/compat-v1.css");

    //theme CSS
    $cssDir = scandir(__DIR__ . "/../../container/studio/" . CONF_VIEW_ERP . "/assets/css");
    foreach ($cssDir as $css) {
        $cssFile = __DIR__ . "/../../container/studio/" . CONF_VIEW_ERP . "/assets/css/{$css}";
        if (is_file($cssFile) && pathinfo($cssFile)['extension'] == "css") {
            $minCSS->add($cssFile);
        }
    }

    //Minify CSS
    $minCSS->minify($erpStyleTarget);
    chmod($erpStyleTarget, 0664);

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
    $jsDir = scandir(__DIR__ . "/../../container/studio/" . CONF_VIEW_ERP . "/assets/js");
    foreach ($jsDir as $js) {
        $jsFile = __DIR__ . "/../../container/studio/" . CONF_VIEW_ERP . "/assets/js/{$js}";
        if (is_file($jsFile) && pathinfo($jsFile)['extension'] == "js") {
            $minJS->add($jsFile);
        }
    }

    //Minify JS
    $minJS->minify($erpScriptTarget);
    chmod($erpScriptTarget, 0664);
}
