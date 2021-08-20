<?php
/*
 * [D_n]Codex 2021
 */

session_start();

require_once 'libs/configs.php';
require_once 'libs/Api.php';

$api = new Api(USER_API, PASS_API, DENY_LIST);

if (isset($_GET['url'])) {
    $api->setURL($_GET['url']);
} else {
    $api->setURL(DEFAULT_URL);
}

if ($api->methodExists()) {
    $json = ['msg' => ERROR_404];
    $api->printJSON($json);
} else {
    $api->{$api->getURL()[0]}();
}
