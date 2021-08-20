<?php
/*
 * [D_n]Codex 2021
 */

//TimeZone
date_default_timezone_set("America/Havana");

//Login de la API
define('USER_API', 'root');
define('PASS_API', '12345');

//Config
define('DEFAULT_URL', strtolower('autApi'));
define('DENY_LIST', array_map(static function ($method) {
    return strtolower($method);
}, [
    'printJSON',
    'getAvailableTime',
    'checkSession',
    '__construct',
    'getUser',
    'setUser',
    'getPassword',
    'setPassword',
]));

//Web Scraping
define('CONNECT_STR',
    [
        'ATTRIBUTE_UUID' => '15,-41',
        'CSRFHW' => '7,-16',
        'wlanuserip' => '11,-1',
        'ssid' => '5,-1',
        'loggerId' => '9,-1',
        'domain => 7,-1',
        'username' => '9,-1',
        'wlanacname' => '11,-1',
        'wlanmac' => '8,-2',
        'Usted está conectado' => '',
        'Su tarjeta no tiene saldo disponible' => '',
        'Usted ha realizado muchos intentos' => '',
        'Entre el nombre de usuario y contraseña correctos' => '',
        'El nombre de usuario o contraseña son incorrectos' => '',
        'Entre la contraseña' => '',
        'El usuario ya está conectado' => '',
    ]
);

define('DISCONNECT_STR', ['SUCCESS', 'FAILURE', 'request error']);

//Personalizar mensajes de ETECSA solo cambie el segundo parámetro
define('NO_CONECTADO', 'No hay conexión');
define('CONECTADO', 'Usted está conectado');
define('NO_SALDO', 'Su tarjeta no tiene saldo disponible');
define('MUCHOS_INTENTOS', 'Usted ha realizado muchos intentos. Por favor intente más tarde');
define('INVALID_USER_PASS', 'El nombre de usuario o contraseña son incorrectos');
define('ERROR_USER_PASS', 'El nombre de usuario o contraseña son incorrectos');
define('INVALID_PASS', 'Contraseña no válida, por favor cambie la contraseña');
define('YA_CONECTADO', 'Otro usuario está conectado');
define('DISCONNECTED', 'Desconectado con éxito');
define('NO_DISCONNECTED', 'No se pudo desconectar');
define('ERROR_404', 'Error 404 Not Found');
define('ERROR_500', 'Internal Server Error');
define('METHOD_NOT_ALLOWED', 'Metodo HTTP no permitido');
