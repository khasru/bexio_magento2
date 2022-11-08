<?php

namespace Scriptlodge\Bexio\Helper;

use Magento\Framework\Encryption\EncryptorInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    public function __construct(
        \Magento\Framework\App\Helper\Context              $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        EncryptorInterface                                 $encryptorInterface
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->encryptorInterface = $encryptorInterface;
        parent::__construct($context);
    }

    public function getConfigValue($path, $storeScope = 0, $decrypt = false)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        if ($decrypt) {
            return $this->encryptorInterface->decrypt($this->scopeConfig->getValue($path, $storeScope));
        } else {
            return $this->scopeConfig->getValue($path, $storeScope);
        }
    }

    public function getConfiguration()
    {
        $apiConfig['enabled'] = $this->getConfigValue('bexio/general/enabled');
        $apiConfig['token'] = $this->getConfigValue('bexio/general/token');
        return $apiConfig;
    }

    public function sendCurlRequestToBexio($url, $method = 'POST', $body = "")
    {
        $apiConfig = $this->getConfiguration();

        if (isset($apiConfig['enabled']) && $apiConfig['enabled'] != 1) return true;
        $token = $apiConfig['token'];
	$lan=	strlen($body);

        $headers = [
            "Content-Type: application/json",
            "Accept: application/json",
            "Authorization: Bearer " . $token,
            'Content-Length: ' . $lan
        ];


//print_r($headers);

        $curl = curl_init($url);
        // curl_setopt($post, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        if ($method == 'POST') {
            curl_setopt($curl, CURLOPT_POST, 1);
        } else {
            curl_setopt($curl, CURLOPT_POST, 0);
        }
        curl_setopt($curl, CURLOPT_TIMEOUT, 45);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        if ($body) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        }
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($curl);

        //$info = curl_getinfo($curl);
        $response = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        /*print_r($body);
        print_r($result);
        print_r($response);
        exit();*/

        if (201 === $response || 200 == $response) {
            curl_close($curl);
            return $result;
        } else {
            /*$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/bexio.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $error = 'Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl);
            $logger->info($url);
            $logger->info($curl);
            $logger->info($error);
            $logger->info("Request:" . $body . " Respons:" . $result);*/
            curl_close($curl);
            return $result;
        }
    }

}
