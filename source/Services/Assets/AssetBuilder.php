<?php

namespace Source\Services\Assets;

use MovesCode\Compress\CSS;
use MovesCode\Compress\JS;

final class AssetBuilder
{
    public function __construct(private readonly string $root) {}

    public function build(string $scope='all', bool $check=false): array
    {
        $definitions=$this->definitions();if($scope!=='all'){if(!isset($definitions[$scope]))throw new \InvalidArgumentException("Tema desconhecido: {$scope}");$definitions=[$scope=>$definitions[$scope]];}$results=[];foreach($definitions as $theme=>$bundles)foreach($bundles as $kind=>$bundle)$results[]=$this->bundle($theme,$kind,$bundle['sources'],$bundle['target'],$check);return$results;
    }

    private function bundle(string $theme,string $kind,array $sources,string $target,bool $check): array
    {
        $sources=array_values(array_filter($sources,'is_file'));if(!$sources)throw new \RuntimeException("Nenhuma fonte encontrada para {$theme}/{$kind}.");$temporary=tempnam(dirname($target),'.moves-assets-');if($temporary===false)throw new \RuntimeException('Não foi possível criar arquivo temporário.');try{$compressor=$kind==='css'?new CSS():new JS();foreach($sources as $source)$compressor->add($source);$compressor->minify($temporary);$generated=file_get_contents($temporary);$current=is_file($target)?file_get_contents($target):false;$changed=$current!==$generated;if($check&&$changed)throw new \RuntimeException("Bundle desatualizado: {$target}");if(!$check&&$changed){if(!rename($temporary,$target))throw new \RuntimeException("Falha ao publicar {$target}");chmod($target,0664);$temporary='';}return['theme'=>$theme,'kind'=>$kind,'target'=>$target,'sources'=>count($sources),'bytes'=>strlen((string)$generated),'changed'=>$changed];}finally{if($temporary!==''&&is_file($temporary))unlink($temporary);}
    }

    private function definitions(): array
    {
        $shared=$this->root.'/container/shared/assets/vendor';$organic=[$this->root.'/organic/organic.min.css',$this->root.'/organic/compat-v1.css'];$commonCss=[$shared.'/owl/owl.carousel.min.css',$shared.'/owl/owl.theme.default.min.css',...$organic];$commonJs=[$shared.'/scripts/jquery.min.js',$shared.'/owl/owl.carousel.js',$shared.'/scripts/jquery.form.js',$shared.'/scripts/jquery-ui.js',$shared.'/scripts/jquery.mask.js',$shared.'/scripts/highcharts.js',$shared.'/scripts/tracker.js',$shared.'/scripts/validation.js'];$theme=fn(string $app)=>$this->root.'/container/apps/'.$app.'/default/assets';$sorted=function(string $pattern):array{$files=glob($pattern)?:[];sort($files,SORT_STRING);return$files;};
        $web=$this->root.'/container/web/default/assets';$erp=$theme('erp');$residents=$theme('residents');$studio=$theme('studio');
        return[
            'web'=>['css'=>['sources'=>$sorted($web.'/css/*.css'),'target'=>$web.'/style.css'],'js'=>['sources'=>[...$commonJs,...$sorted($web.'/js/*.js')],'target'=>$web.'/scripts.js']],
            'erp'=>['css'=>['sources'=>[...$commonCss,...$sorted($erp.'/css/*.css')],'target'=>$erp.'/style.css'],'js'=>['sources'=>[...$commonJs,...$sorted($erp.'/js/*.js')],'target'=>$erp.'/scripts.js']],
            'residents'=>['css'=>['sources'=>[...$commonCss,...$sorted($residents.'/css/*.css')],'target'=>$residents.'/style.css'],'js'=>['sources'=>[...$commonJs,...$sorted($residents.'/js/*.js')],'target'=>$residents.'/scripts.js']],
            'studio'=>['css'=>['sources'=>[$this->root.'/organic/editor/organic-editor.min.css',$studio.'/css/studio-icons.css',$studio.'/css/admin.css',$studio.'/css/studio-system.css'],'target'=>$studio.'/studio.min.css'],'js'=>['sources'=>[$shared.'/scripts/jquery.min.js',$shared.'/scripts/jquery.form.js',$shared.'/scripts/jquery-ui.js',$studio.'/js/scripts.js'],'target'=>$studio.'/studio.min.js']],
        ];
    }
}
