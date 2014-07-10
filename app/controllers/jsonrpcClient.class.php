<?php

/**
 * Description of JSONRPC client
 *
 * @author Loïc Gevrey
 */
class jsonrpcClient {
    /*     * ********Attributs******************* */

    private $errorCode = '';
    private $errorMessage = '';
    private $error = '';
    private $result;
    private $rawResult;
    private $apikey = '';
    private $options = array();
    private $apiAddr;

    /*     * ********Static******************* */

    function __construct($_apiAddr, $_apikey, $_options = array()) {
        $this->apiAddr = $_apiAddr;
        $this->apikey = $_apikey;
        $this->options = $_options;
    }

    public function sendRequest($_method, $_params = null, $_timeout = 10, $_file = null, $_maxRetry = 3) {
        $_params['apikey'] = $this->apikey;
        $_params = array_merge($_params, $this->options);
        $request = array(
            'request' => json_encode(array(
                'jsonrpc' => '2.0',
                'id' => rand(1, 9999),
                'method' => $_method,
                'params' => $_params,
        )));
        $this->rawResult = $this->send($request, $_timeout, $_file, $_maxRetry);

        if ($this->rawResult === false) {
            return false;
        }
        $result = json_decode(trim($this->rawResult), true);

        if (isset($result['result'])) {
            $this->result = $result['result'];
            return true;
        } else {
            if (isset($result['error']['code'])) {
                $this->error = 'Code : ' . $result['error']['code'];
                $this->errorCode = $result['error']['code'];
            }
            if (isset($result['error']['message'])) {
                $this->error .= '<br/>Message : ' . $result['error']['message'];
                $this->errorMessage = $result['error']['message'];
            }
            return false;
        }
    }

    private function send($_request, $_timeout = 10, $_file = null, $_maxRetry = 3) {
        $url = parse_url($this->apiAddr);
        $host = $url['host'];
        if (!ip2long($host)) {
            exec("timeout 2 ping -n -c 1 -W 2 $host", $output, $retval);
            if ($retval != 0) {
                throw new Exception(__('Impossible de résoudre le DNS : ', __FILE__) . $host . __('. Pas d\'internet ?', __FILE__),3456);
            }
        }
        if ($_file !== null) {
            $_request = array_merge($_request, $_file);
        }
        $nbRetry = 0;
        while ($nbRetry < $_maxRetry) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->apiAddr);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, $_timeout);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $_timeout);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $_request);
            curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
            curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
            $response = curl_exec($ch);
            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $nbRetry++;
            if (curl_errno($ch) && $nbRetry < $_maxRetry) {
                curl_close($ch);
                usleep(500000);
            } else {
                $nbRetry = $_maxRetry + 1;
            }
        }
        if ($http_status != 200) {
            $this->error = 'Erreur http : ' . $http_status . ' Details : ' . $response;
        }
        if (curl_errno($ch)) {
            $this->error = 'Erreur curl sur : ' . $this->apiAddr . '. Détail :' . curl_error($ch);
        }
        curl_close($ch);
        return $response;
    }

    /*     * ********Getteur Setteur******************* */

    public function getError() {
        return $this->error;
    }

    public function getResult() {
        return $this->result;
    }

    public function getRawResult() {
        return $this->rawResult;
    }

    public function getErrorCode() {
        return $this->errorCode;
    }

    public function getErrorMessage() {
        return $this->errorMessage;
    }

}

?>
