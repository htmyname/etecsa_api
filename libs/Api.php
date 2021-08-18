<?php
/*
 * Copyright (c) 2020. [D_n]Codex
 */

require_once 'User.php';

class Api
{
    protected $user;
    protected $password;
    protected $respuesta;
    protected $deny_list;
    protected $url;

    /**
     * App constructor.
     */
    public function __construct()
    {
        $this->deny_list = ['printJSON', 'getAvailableTime', 'checkSession'];
        $this->url = ['autApi'];
        session_start();
    }

    public function autApi()
    {
        if (isset($_POST['user'], $_POST['password'])) {
            $this->user = $_POST['user'];
            $this->password = $_POST['password'];
            if ($this->user == USER_API && $this->password == PASS_API) {
                $_SESSION['loged'] = 'true';
                $this->respuesta = ['login' => 'true'];
            }
        } else {
            $_SESSION['loged'] = 'false';
            $this->respuesta = ['login' => 'false'];
        }
        $this->printJSON($this->respuesta);
    }

    public function outApi()
    {
        $msg = ['logout' => 'false'];
        if (session_status() == 2) {
            session_unset();
            session_destroy();
            $msg = ['logout' => 'true'];
        }
        $this->printJSON($msg);
    }

    public function connect()
    {
        $msg = ['msg' => 'false'];
        $sendResponse = false;
        $error_msg = NO_CONECTADO;

        $this->chkSession($msg);

        if (isset($_POST['user'], $_POST['password'])) {
            $user = new User($_POST['user'], $_POST['password']);
        } else {
            $this->printJSON($msg);
        }

        $url = "https://secure.etecsa.net:8443//LoginServlet?username={$user->getUser()}&password={$user->getPassword()}";

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_COOKIESESSION, 1);
        curl_setopt($ch, CURLOPT_COOKIEJAR, 'libs\cookies');
        curl_setopt($ch, CURLOPT_COOKIEFILE, 'libs\cookies');
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $info = curl_exec($ch);
        curl_close($ch);

        $response = preg_split("([\r]+)", $info);

        foreach ($response as $line) {

            if ($temp_line = strpos($line, 'ATTRIBUTE_UUID')) {//PRIMERA OCURRENCIA

                $ATTRIBUTE_UUID = substr($line, $temp_line + 15, -41);

            } else if ($temp_line = strrpos($line, 'CSRFHW')) {//ULTIMA OCURRENCIA

                $CSRFHW = substr($line, $temp_line + 7, -16);

            } else if ($temp_line = strrpos($line, 'wlanuserip')) {//ULTIMA OCURRENCIA

                $wlanuserip = substr($line, $temp_line + 11, -1);

            } else if ($temp_line = strrpos($line, 'ssid')) {//ULTIMA OCURRENCIA

                $ssid = substr($line, $temp_line + 5, -1);

            } else if ($temp_line = strrpos($line, 'loggerId')) {//ULTIMA OCURRENCIA

                $loggerId = substr($line, $temp_line + 9, -1);

            } else if ($temp_line = strrpos($line, 'domain')) {//ULTIMA OCURRENCIA

                $domain = substr($line, $temp_line + 7, -1);

            } else if ($temp_line = strrpos($line, 'username')) {//ULTIMA OCURRENCIA

                $username = substr($line, $temp_line + 9, -1);

            } else if ($temp_line = strrpos($line, 'wlanacname')) {//ULTIMA OCURRENCIA

                $wlanacname = substr($line, $temp_line + 11, -1);

            } else if ($temp_line = strrpos($line, 'wlanmac')) {//ULTIMA OCURRENCIA

                $wlanmac = substr($line, $temp_line + 8, -2);

            } else if ($temp_line = strrpos($line, 'Usted está conectado')) {//ULTIMA OCURRENCIA

                $sendResponse = true;
                $error_msg = CONECTADO;

            } else if ($temp_line = strrpos($line, 'Su tarjeta no tiene saldo disponible')) {//ULTIMA OCURRENCIA

                $error_msg = NO_SALDO;

            } else if ($temp_line = strrpos($line, 'Usted ha realizado muchos intentos')) {//ULTIMA OCURRENCIA

                $error_msg = MUCHOS_INTENTOS;

            } else if ($temp_line = strrpos($line, 'Entre el nombre de usuario y contraseña correctos')) {//ULTIMA OCURRENCIA

                $error_msg = INVALID_USER_PASS;

            } else if ($temp_line = strrpos($line, 'El nombre de usuario o contraseña son incorrectos')) {//ULTIMA OCURRENCIA

                $error_msg = ERROR_USER_PASS;

            } else if ($temp_line = strrpos($line, 'Entre la contraseña')) {//ULTIMA OCURRENCIA

                $error_msg = INVALID_PASS;

            } else if ($temp_line = strrpos($line, 'El usuario ya está conectado')) {//ULTIMA OCURRENCIA

                $error_msg = YA_CONECTADO;

            }
        }

        $error_json = ['msg' => $error_msg];

        if ($sendResponse === true) {

            $dataLogout = "ATTRIBUTE_UUID=" . $ATTRIBUTE_UUID . "&CSRFHW=" . $CSRFHW . "&wlanuserip=" . $wlanuserip . "&ssid=" . $ssid . "&loggerId=" . $loggerId . "&domain=" . $domain . "&username=" . $username . "&wlanacname=" . $wlanacname . "&wlanmac=" . $wlanmac . "&remove=1";
            $dataUpdate = "ATTRIBUTE_UUID=" . $ATTRIBUTE_UUID . "&CSRFHW=" . $CSRFHW . "&wlanuserip=" . $wlanuserip . "&ssid=" . $ssid . "&loggerId=" . $loggerId . "&domain=" . $domain . "&username=" . $username . "&wlanacname=" . $wlanacname . "&wlanmac=" . $wlanmac;
            $con_ini = time();

            $conex_json = ['dataLogout' => $dataLogout, 'dataUpdate' => $dataUpdate, 'con_ini' => $con_ini];

            $json = array($conex_json, $error_json);
        } else {
            $json = array($error_json);
        }

        $this->printJSON($json);

    }

    public function disconnect()
    {
        $msg = ['msg' => 'false'];
        $json_msg = $msg;
        $con_fin = time(); //hora en la que cierras session

        $this->chkSession($msg);

        if (isset($_POST['dataLogout'], $_POST['con_ini'], $_POST['tiempo'],
            $_POST['tiempo_real'], $_POST['saldo'], $_POST['internet_price'])) {
            $dataLogout = $_POST['dataLogout'];
            $con_ini = $_POST['con_ini'];
            //$time_dis = $_POST['tiempo'];
            $time_dis_real = $_POST['tiempo_real'];
            $saldo_dis = $_POST['saldo'];
            $centavosXhora = $_POST['internet_price'];
        } else {
            $this->printJSON($msg);
        }

        $horaEnSegundos = 3600;
        $centavosXsegundo = $centavosXhora / $horaEnSegundos;
        $SegundosXunCentavo = substr(1 / $centavosXsegundo, 0, 4);

        if ($con_ini != 0) {

            $time_gastado = $con_fin - $con_ini;
            $cant_centavos_gastados = ceil($time_gastado / $SegundosXunCentavo);
            $time_dis_real -= $SegundosXunCentavo * $cant_centavos_gastados;
            $time_dis = floor($time_dis_real);
            $saldo_dis = $saldo_dis * 100 - $cant_centavos_gastados;
            $saldo_dis /= 100;

            if ($saldo_dis <= 0) {
                $saldo_dis = 0;
                $time_dis = 0;
            }

            $time_dis_json = $this->getTimeDisponible($time_dis);

            $url = "https://secure.etecsa.net:8443/LogoutServlet?" . $dataLogout;

            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_COOKIEJAR, 'libs\cookies');
            curl_setopt($ch, CURLOPT_COOKIEFILE, 'libs\cookies');
            curl_setopt($ch, CURLOPT_HEADER, TRUE);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $info = curl_exec($ch);
            curl_close($ch);

            $response = preg_split("([\r]+)", $info);

            foreach ($response as $line) {

                if (strrpos($line, 'SUCCESS') != false) {//ULTIMA OCURRENCIA

                    $json_msg = ['msg' => DISCONNECTED, 'saldo' => $saldo_dis, 'tiempo' => $time_dis_json];

                } else if (strrpos($line, 'FAILURE') != false) {//ULTIMA OCURRENCIA

                    $json_msg = ['msg' => DISCONNECTED, 'saldo' => $saldo_dis, 'tiempo' => $time_dis_json];

                } else if (strrpos($line, 'request error') != false) {//ULTIMA OCURRENCIA

                    $json_msg = ['msg' => NO_DISCONNECTED];

                }
            }

            $this->printJSON($json_msg);

        } else {
            $this->printJSON($msg);
        }
    }

    public function getTime()
    {
        $msg = ['msg' => 'false'];

        $this->chkSession($msg);

        if (isset($_POST['internet_price'], $_POST['dataUpdate'])) {
            $centavosXhora = $_POST['internet_price'];
            $dataUpdate = $_POST['dataUpdate'];
        } else {
            $this->printJSON($msg);
        }

        $horaEnSegundos = 3600;
        $centavosXsegundo = $centavosXhora / $horaEnSegundos;
        $SegundosXunCentavo = substr(1 / $centavosXsegundo, 0, 4);

        $url = "https://secure.etecsa.net:8443/EtecsaQueryServlet?op=getLeftTime&" . $dataUpdate;

        if ($ch = curl_init($url)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_COOKIEJAR, 'libs\cookies');
            curl_setopt($ch, CURLOPT_COOKIEFILE, 'libs\cookies');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $info = curl_exec($ch);
            curl_close($ch);

            $horas = substr($info, 0, 2);
            $minutos = substr($info, 3, 2);
            $segundos = substr($info, 6, 2);

            if ($horas != "") {
                $saldo_dis = $horas * $centavosXhora + $minutos * $centavosXsegundo * 60 + $segundos * $centavosXsegundo;
                $saldo_dis = round($saldo_dis);
                //saldo disponible antes de ser centavos
                $time_dis_real = $saldo_dis * $SegundosXunCentavo;
                //saldo disponible convertido a centavos
                $saldo_dis /= 100;

                $time_dis_seg = $horas * 3600 + $minutos * 60 + $segundos;
                $time_dis = $info;

                if ($saldo_dis <= 0) {
                    $time_dis_real = 0;
                    $time_dis = "00:00:00";
                }

                $json = ['msg' => 'true', 'saldo' => $saldo_dis, 'tiempo' => $time_dis, 'tiempo_real' => $time_dis_real, 'tiempo_en_seg' => $time_dis_seg];

            } else {
                $saldo_dis = 0;
                $time_dis = "00:00:00";
                $time_dis_real = 0;
                $time_dis_seg = 0;
                $json = ['msg' => 'false', 'saldo' => $saldo_dis, 'tiempo' => $time_dis, 'tiempo_real' => $time_dis_real, 'tiempo_en_seg' => $time_dis_seg];
            }

        }

        $this->printJSON($json);
    }

    private function getAvailableTime($tiempo_en_segundos)
    {
        $horas = floor($tiempo_en_segundos / 3600);
        $minutos = floor(($tiempo_en_segundos - ($horas * 3600)) / 60);
        $segundos = $tiempo_en_segundos - ($horas * 3600) - ($minutos * 60);

        if ($horas < 10) {
            $horas = "0" . $horas;
        }
        if ($minutos < 10) {
            $minutos = "0" . $minutos;
        }
        if ($segundos < 10) {
            $segundos = "0" . $segundos;
        }

        return $horas . ':' . $minutos . ":" . $segundos;
    }

    private function checkSession($msg)
    {
        if (isset($_SESSION['loged'])) {
            if ($_SESSION['loged'] != 'true') {
                $this->printJSON($msg);
            }
        } else {
            $this->printJSON($msg);
        }
    }

    function methodExists(): bool
    {
        $url = $this->getURL();
        return !method_exists(Api::class, $url[0]) || in_array(true, array_map(static function ($deny) use ($url) {
                return strcasecmp($url[0], $deny) === 0;
            }, $this->deny_list), true);
    }

    public function printJSON($json)
    {
        header("Content-Type:application/json;charset=utf-8");
        echo json_encode($json);
        exit();
    }

    function getURL()
    {
        return explode('/', rtrim($this->url, '/'));
    }

    function setURL($url)
    {
        $this->url = $url;
    }

}
