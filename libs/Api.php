<?php
/*
 * [D_n]Codex 2021
 */

require_once 'User.php';

class Api
{
    private string $api_user;
    private string $api_password;
    private array $deny_list;
    private array $url;
    private bool $response;
    private string $error_msg;

    /**
     * App constructor.
     */
    public function __construct($api_user, $api_password, $deny_list)
    {
        $this->api_user = $api_user;
        $this->api_password = $api_password;
        $this->deny_list = $deny_list;
        $this->response = false;
        $this->error_msg = NO_CONECTADO;
    }

    public function getApiUser(): string
    {
        return $this->api_user;
    }

    public function getApiPassword(): string
    {
        return $this->api_password;
    }

    public function getDenyList(): array
    {
        return $this->deny_list;
    }

    public function getURL(): array
    {
        return $this->url;
    }

    public function setURL(string $url): void
    {
        $this->url = explode('/', strtolower(rtrim($url, '/')));
    }

    public function getResponse(): bool
    {
        return $this->response;
    }

    public function setResponse(bool $response): void
    {
        $this->response = $response;
    }


    public function getErrorMsg()
    {
        return $this->error_msg;
    }

    public function setErrorMsg(string $error_msg): void
    {
        $this->error_msg = $error_msg;
    }


    public function autApi(): void
    {
        if ($this->getHTTPMethod('POST')) {
            $this->printJSON([
                'login' => $_SESSION['login'] = !(isset($_POST['user'], $_POST['password'])) ? 'false'
                    : var_export($this->getApiUser() === $_POST['user'] && $this->getApiPassword() === $_POST['password'], true)
            ]);
        }
    }

    public function outApi(): void
    {
        if ($this->getHTTPMethod('GET')) {
            $this->printJSON([
                'logout' => $msg = (session_status() === 2 && session_unset() && session_destroy()) ? 'true' : 'false'
            ]);
        }
    }


    public function connect(): void
    {
        $ATTRIBUTE_UUID = $CSRFHW = $wlanuserip = $ssid =
        $loggerId = $domain = $username = $wlanacname = $wlanmac = '';

        if (!$this->checkSession()) {
            $this->printJSON(['session' => 'false']);
        }
        if (!isset($_POST['user'], $_POST['password'])) {
            $this->printJSON(['msg' => 'false']);
        }

        $user = new User($_POST['user'], $_POST['password']);
        $url = "https://secure.etecsa.net:8443//LoginServlet?username={$user->getUser()}&password={$user->getPassword()}";
        $response = $this->getResult($url, 'connect');

        if (count($response) > 0) {
            foreach ($response as $line) {
                if (!$this->getResponse()) {
                    foreach (CONNECT_STR as $key => $value) {
                        if ($value !== '') {
                            if ($temp_line = @strrpos($line, $key)) {
                                $val = explode(',', $value);
                                $$key = substr($line, $temp_line + $val[0], $val[1]);
                            }
                        } else if (@strrpos($line, $key)) {
                            if ($key === 'Usted está conectado') {
                                $this->setResponse(true);
                            }
                            $this->setErrorMsg($key);
                            break;
                        }
                    }
                } else {
                    break;
                }
            }
        }

        if ($this->getResponse()) {
            $dataLogout = "ATTRIBUTE_UUID=" . $ATTRIBUTE_UUID . "&CSRFHW=" . $CSRFHW . "&wlanuserip=" . $wlanuserip
                . "&ssid=" . $ssid . "&loggerId=" . $loggerId . "&domain=" . $domain . "&username=" . $username
                . "&wlanacname=" . $wlanacname . "&wlanmac=" . $wlanmac . "&remove=1";
            $dataUpdate = "ATTRIBUTE_UUID=" . $ATTRIBUTE_UUID . "&CSRFHW=" . $CSRFHW . "&wlanuserip=" . $wlanuserip
                . "&ssid=" . $ssid . "&loggerId=" . $loggerId . "&domain=" . $domain . "&username=" . $username
                . "&wlanacname=" . $wlanacname . "&wlanmac=" . $wlanmac;
            $json = [
                [
                    'dataLogout' => $dataLogout,
                    'dataUpdate' => $dataUpdate,
                    'con_ini' => time()
                ], [
                    'msg' => $this->getErrorMsg()
                ]
            ];
        } else {
            $json = array(['msg' => $this->getErrorMsg()]);
        }

        $this->printJSON($json);

    }

    public function disconnect()
    {
        $con_end = time();
        $json_msg = ['msg' => 'false'];
        $con_start = $real_time = $internet_price = $balance = 0;
        $dataLogout = '';
        if (!$this->checkSession()) {
            $this->printJSON($json_msg);
        }

        if (isset($_POST['dataLogout'], $_POST['con_ini'], $_POST['tiempo'],
            $_POST['tiempo_real'], $_POST['saldo'], $_POST['internet_price'])) {
            $dataLogout = $_POST['dataLogout'];
            $con_start = $_POST['con_ini'];
            $real_time = $_POST['tiempo_real'];
            $balance = $_POST['saldo'];
            $internet_price = $_POST['internet_price'];
        } else {
            $this->printJSON($json_msg);
        }

        if ($con_start !== 0) {
            $spent_time = $con_end - $con_start;
            $spent_money = ceil($internet_price * $spent_time / 36);
            $real_time -= floor($spent_time / $internet_price * 36);
            $balance = ($balance * 100 - $spent_money) / 100;

            if ($balance <= 0) {
                $balance = 0;
                $real_time = 0;
            }

            $time_dis_json = $this->getAvailableTime($real_time);
            $url = "https://secure.etecsa.net:8443/LogoutServlet?" . $dataLogout;
            $response = $this->getResult($url, 'disconnect');

            if (count($response) > 0) {
                foreach ($response as $line) {
                    if ($json_msg === ['msg' => 'false']) {
                        foreach (DISCONNECT_STR as $value) {
                            if (strrpos($line, $value) !== false) {
                                if ($value === 'request error') {
                                    $json_msg = ['msg' => NO_DISCONNECTED];
                                } else {
                                    $json_msg = ['msg' => DISCONNECTED, 'saldo' => $balance, 'tiempo' => $time_dis_json];
                                }
                                break;
                            }
                        }
                    } else {
                        break;
                    }
                }
            }
        }
        $this->printJSON($json_msg);
    }

    public function getTime()
    {
        //$msg = ['msg' => 'false'];

        if (!$this->checkSession()) {
            $this->printJSON(['msg' => 'false']);
        }

        if (isset($_POST['internet_price'], $_POST['dataUpdate'])) {
            $centavosXhora = $_POST['internet_price'];
            $dataUpdate = $_POST['dataUpdate'];
        } else {
            $this->printJSON(['msg' => 'false']);
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

    public function getAvailableTime($tiempo_en_segundos)
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

    private function checkSession()
    {
        return (isset($_SESSION['login']) && $_SESSION['login'] === 'true');
    }

    public function methodExists(): bool
    {
        $url = $this->getURL();
        return !method_exists(__CLASS__, $url[0]) || in_array($url[0], $this->getDenyList(), true);
    }

    public function getResult($url, $action): array
    {
        if ($ch = curl_init($url)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
            curl_setopt($ch, CURLOPT_POST, 1);
            if ($action === 'connect') {
                curl_setopt($ch, CURLOPT_COOKIESESSION, 1);
            }
            curl_setopt($ch, CURLOPT_COOKIEJAR, 'libs\cookies');
            curl_setopt($ch, CURLOPT_COOKIEFILE, 'libs\cookies');
            curl_setopt($ch, CURLOPT_HEADER, TRUE);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = preg_split("([\r]+)", curl_exec($ch));
            curl_close($ch);
            return $result;
        }
        return [];
    }

    public function getHTTPMethod($method): bool
    {
        echo $_SERVER['REQUEST_METHOD'];
        if ($_SERVER['REQUEST_METHOD'] === $method) {
            return true;
        }
        $this->printJSON(['msg' => METHOD_NOT_ALLOWED]);
        return false;
    }


    public function printJSON($json): void
    {
        //header("Content-Type:application/json;charset=utf-8");
        try {
            echo json_encode($json, JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
            echo ERROR_500;
        }
        exit();
    }

}
