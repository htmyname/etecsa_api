<?php
/*
 * Copyright (c) 2020. [D_n]Codex
 */

//TimeZone
date_default_timezone_set("America/Havana");

//Login de la API
define('USER_API', 'root');
define('PASS_API', '12345');

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