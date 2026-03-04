<?php
return [
    'host'  =>  getenv('DB_HOST') ?: '',
    'port'  =>  getenv('DB_PORT') ?: '',
    'name'  =>  getenv('DB_NAME') ?: 'App/Database/painel_comercial.db',
    'user'  =>  getenv('DB_USER') ?: '',
    'pass'  =>  getenv('DB_PASS') ?: '',
    'type'  =>  getenv('DB_TYPE') ?: 'sqlite',
    'prep'  =>  "1"
];
