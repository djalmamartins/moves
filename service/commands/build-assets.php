<?php

declare(strict_types=1);

$root=dirname(__DIR__,2);
require $root.'/vendor/movescode/compress/src/Compress.php';
require $root.'/vendor/movescode/compress/src/CSS.php';
require $root.'/vendor/movescode/compress/src/JS.php';
require $root.'/source/Services/Assets/AssetBuilder.php';

$arguments=array_slice($argv,1);$check=in_array('--check',$arguments,true);$scope='all';foreach($arguments as $argument)if(!str_starts_with($argument,'--'))$scope=$argument;
try{$results=(new \Source\Services\Assets\AssetBuilder($root))->build($scope,$check);foreach($results as $result)fwrite(STDOUT,sprintf('%s %s/%s (%d fontes, %d bytes)%s',strtoupper($check?'ok':($result['changed']?'gerado':'inalterado')),$result['theme'],$result['kind'],$result['sources'],$result['bytes'],PHP_EOL));}catch(\Throwable $exception){fwrite(STDERR,$exception->getMessage().PHP_EOL);exit(1);}
