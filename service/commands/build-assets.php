<?php

declare(strict_types=1);

putenv('MOVESOS_ENV=testing');
require dirname(__DIR__,2).'/vendor/autoload.php';

use Source\Services\Assets\AssetBuilder;

$arguments=array_slice($argv,1);$check=in_array('--check',$arguments,true);$scope='all';foreach($arguments as $argument)if(!str_starts_with($argument,'--'))$scope=$argument;
try{$results=(new AssetBuilder(dirname(__DIR__,2)))->build($scope,$check);foreach($results as $result)fwrite(STDOUT,sprintf('%s %s/%s (%d fontes, %d bytes)%s',strtoupper($check?'ok':($result['changed']?'gerado':'inalterado')),$result['theme'],$result['kind'],$result['sources'],$result['bytes'],PHP_EOL));}catch(\Throwable $exception){fwrite(STDERR,$exception->getMessage().PHP_EOL);exit(1);}
