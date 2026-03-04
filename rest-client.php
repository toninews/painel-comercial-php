<?php
$location = 'http://localhost/projeto-php/rest.php';
$parameters = [];
$parameters['endpoint'] = 'pessoas.show';
$parameters['id']     = '1';

$url = $location . '?' . http_build_query($parameters);
var_dump( json_decode( file_get_contents($url) ) );
