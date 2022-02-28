<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scriptlodge\Bexio\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Setup\Exception;
use Magento\Framework\Encryption\EncryptorInterface;
use Scriptlodge\Bexio\Api\BexioCustomerRepositoryInterface;
use Scriptlodge\Bexio\Model\BexioCustomerFactory;


/**
 * Class ProceesOrder
 *
 * @codeCoverageIgnore
 */
class ProceesCustomer extends \Magento\Framework\Model\AbstractModel
{
    protected $contactUrl = "https://api.bexio.com/2.0/contact";
    protected $contactBulkCreateUrl = "https://api.bexio.com/2.0/contact/_bulk_create";
    protected $contactSearceUrl = 'https://api.bexio.com/2.0/contact/search';

    protected $_orderCollectionFactory;
    protected $_helperData;
    protected $_bexioCustomerRepositoryInterface;

    /**
     * Destination constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context                        $context,
        \Magento\Framework\Registry                             $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb           $resourceCollection = null,
        CustomerRepositoryInterface                             $customerRepositoryInterface,
        \Magento\Customer\Model\AddressFactory                  $addressFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder            $searchCriteriaBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface      $scopeConfig,
        \Scriptlodge\Bexio\Helper\Data                          $helperData,
        BexioCustomerRepositoryInterface                        $bexioCustomerRepositoryInterface,
        BexioCustomerFactory                                    $bexioCustomerFactory,
        EncryptorInterface                                      $encryptorInterface,
        array                                                   $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->encryptorInterface = $encryptorInterface;
        $this->_addressFactory = $addressFactory;
        $this->_helperData = $helperData;
        $this->_bexioCustomerRepositoryInterface = $bexioCustomerRepositoryInterface;
        $this->_bexioCustomerFactory = $bexioCustomerFactory;
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

    public function sendCustomerToBexio($customerId = "")
    {
        $response = $customerArray = $customerJsonData = $customerMap=[];
        $customer = "";
        $apiConfig = $this->getConfiguration();
        if (isset($apiConfig['enabled']) && $apiConfig['enabled'] != 1) return true;

        if ($customerId) {
            $customer = $this->_customerRepositoryInterface->getById($customerId);

            if($customer->getWebsiteId()==1) {

                if (!empty($customer->getEmail())) {
                    $customerArray = $this->makeCustomerData($customer);

                    $customerMap = $this->getCustomerMapData($customerId, $customer->getEmail(), $customer->getWebsiteId());
                    $customerMapId = $bexioContactId = "";
                    if (!empty($customerMap)) {
                        foreach ($customerMap->getItems() as $item) {
                            $customerMapId = $item->getEntityId();
                            $bexioContactId = $item->getBexioContactId();
                        }
                    }

                    $_id = $this->proceesRequest($customerArray, $customerMapId, $bexioContactId);

                }
            }
        } else {

        }

    }

    public function proceesRequest($customerArray, $customerMapId = "", $bexioContactId = '')
    {

        if ($bexioContactId == '') {
            $_contactList = $this->searchContact($customerArray['email'], $customerArray['country_id']);

            if (!empty($_contactList)) {
                $contactList = json_decode($_contactList);
                foreach ($contactList as $item) {
                    if ($item->id) {
                        $bexioContactId = $item->id;
                    }
                }
            }
        }


        $customerData = $this->makeBexioCustomerData($customerArray);

        $customerJsonData = json_encode($customerData);

        $contactUrl = $this->contactUrl;
        if ($bexioContactId) {
            $contactUrl = $contactUrl . '/' . $bexioContactId;
        }

        $response = $this->_helperData->sendCurlRequestToBexio($contactUrl, 'POST', $customerJsonData);

        return $this->updateCustomer($customerMapId, $response, $customerArray);
    }

    public function makeCustomerData($customer)
    {
        if (empty($customer->getEmail())) return;
        $customerData = [];

        //billing
        $billingAddressId = $customer->getDefaultBilling();
        $billingAddress = $this->_addressFactory->create()->load($billingAddressId);
        //shipping
        $shippingAddressId = $customer->getDefaultShipping();
        $shippingAddress = $this->_addressFactory->create()->load($shippingAddressId);

        $address = ($billingAddress->getStreetFull()) ? $billingAddress->getStreetFull() : "";
        $country_id=1;
        if($billingAddress->getCountry()=='DE'){
            $country_id=2;
        }

        $customerArray['customer_id']=$customer->getId();
        $customerArray['first_name']=$customer->getLastname();
        $customerArray['last_name']=$customer->getFirstname();
        $customerArray['email']=$customer->getEmail();
        $customerArray['prefix']=$customer->getPrefix();
        $customerArray['address']=$address;
        $customerArray['postcode']=$billingAddress->getPostcode();
        $customerArray['city']=$billingAddress->getCity();
        $customerArray['country_id']=$country_id;
        $customerArray['phone_mobile']=$billingAddress->getTelephone();
        $customerArray['fax']=$billingAddress->getFax();

        return $customerArray;
    }

    public function makeBexioCustomerData($customerArray){

        $customerData['contact_type_id'] = 2;  //Please use the value 1 for companies or 2 for persons
        $customerData['name_1'] = $customerArray['last_name'];  //This field is used as the company name if the field contact_type_id is set to 1. Otherwise, the field is used as the last name of the person
        $customerData['name_2'] = $customerArray['first_name']; //This field is used as the company addition if the field contact_type_id is set to 1. Otherwise, the field is used as the first name of the person
        if (isset($customerArray['prefix'])) {
            // $customerData['salutation_id']=1;
        }
        $customerData['address'] = (isset($customerArray['address']))?$customerArray['address']:"";
        if (isset($customerArray['postcode'])) {
            $customerData['postcode'] = $customerArray['postcode'];
        }
        if (isset($customerArray['city'])) {
            $customerData['city'] = $customerArray['city'];
        }
        $customerData['mail'] = (isset($customerArray['email']))?$customerArray['email']:"";
        if(isset($customerArray['country_id']) && !empty($customerArray['country_id'])) {
            $customerData['country_id']=$customerArray['country_id'];
            /*$bexio_country_id = $this->getBexioCountryId($customerArray['country_id']);
            if ($bexio_country_id) {
                $customerData['country_id'] = $bexio_country_id;
            }*/
        }
        if (isset($customerArray['phone_mobile'])) {
            $customerData['phone_mobile'] = $customerArray['phone_mobile'];
        }
        if (isset($customerArray['fax'])) {
            $customerData['fax'] = $customerArray['fax'];
        }
        if (isset($customerArray['customer_id'])) {
            $customerData['remarks'] = "Magento Customer Id :" . $customerArray['customer_id'];
        } else {
            $customerData['remarks'] = "Guest";
        }

        $customerData['contact_group_ids'] = "1,2";
        $customerData['user_id'] = 1;
        $customerData['owner_id'] = 1;
        return $customerData;
    }

    public function getCustomerMapData($customerId = "", $email = "", $webSiteId = "")
    {
        $customerMap = "";
        if ($customerId) {
            $customerMap= $this->_bexioCustomerRepositoryInterface->getByCustomerId($customerId);
            if(!empty($customerMap)){
                return $customerMap;
            }
        }

        if (!empty($email) && !empty($webSiteId)) {
            $this->searchCriteriaBuilder->addFilter('email', $email, 'eq');
            $this->searchCriteriaBuilder->addFilter('country_id', $webSiteId, 'eq');
            $this->searchCriteriaBuilder->setPageSize(1)->setCurrentPage(1);
            $searchCriteria = $this->searchCriteriaBuilder->create();
            //$customerMap = $this->_bexioCustomerRepositoryInterface->getList($searchCriteria)->getItems();
            return $this->_bexioCustomerRepositoryInterface->getList($searchCriteria);

        }
        return $customerMap;
    }

    protected function updateCustomer($customerMapId = "", $response, $customer = "")
    {
        try {

            if (empty($response)) return;
            $responseArray = json_decode($response);
            $needSync = 0;
            $error = "";
            $bexioContactId="";
            if (isset($responseArray->error_code) && $responseArray->error_code == 422) {
                $needSync = 1;
                $error = $responseArray->message;
            } else {
                $bexioContactId = $responseArray->id;
            }

            $customerId = (isset($customer['customer_id'])) ? $customer['customer_id'] : "";
            $webSiteId = (isset($customer['country_id'])) ? $customer['country_id'] : "";
            $email = (isset($customer['email'])) ? $customer['email'] : "";

            if (empty($customerMapId)) {
                $bexioCustomer = $this->_bexioCustomerFactory->create();
                $bexioCustomer->setCustomerId($customerId);
                $bexioCustomer->setBexioContactId($bexioContactId);
                $bexioCustomer->setNeedSync($needSync);
                $bexioCustomer->setError($error);
                $bexioCustomer->setCountryId($webSiteId);
                $bexioCustomer->setEmail($email);
                $this->_bexioCustomerRepositoryInterface->save($bexioCustomer);
            } elseif ($customerMapId) {
                $bexioCustomer = $this->_bexioCustomerRepositoryInterface->get($customerMapId);
                $bexioCustomer->setNeedSync($needSync);
                $bexioCustomer->setError($error);
                $bexioCustomer->setCountryId($webSiteId);
                $bexioCustomer->setCustomerId($customerId);
                $bexioCustomer->setEmail($email);
                $this->_bexioCustomerRepositoryInterface->save($bexioCustomer);
            } else {
                /*$customerMapId = "";
                foreach ($customerMap->getItems() as $item) {
                    $customerMapId = $item->getEntityId();
                }
                $bexioCustomer = $this->_bexioCustomerRepositoryInterface->get($customerMapId);
                $bexioCustomer->setNeedSync($needSync);
                $bexioCustomer->setError($error);
                $bexioCustomer->setCountryId($webSiteId);
                $bexioCustomer->setEmail($email);
                $this->_bexioCustomerRepositoryInterface->save($bexioCustomer);*/
            }

        } catch (Exception $exception) {
            //  print_r($exception->getMessage());
        }
        return $bexioContactId;
    }


    public function getBexioCountryId($code)
    {
        $countryList = array(
            0 => array(
                'id' => 1,
                'name' => 'Schweiz',
                'name_short' => 'CH',
                'iso_3166_alpha2' => 'CH',
            ),
            1 => array(
                'id' => 2,
                'name' => 'Deutschland',
                'name_short' => 'D',
                'iso_3166_alpha2' => 'DE',
            ),
            2 => array(
                'id' => 3,
                'name' => 'Österreich',
                'name_short' => 'A',
                'iso_3166_alpha2' => 'AT',
            ),
            3 =>
                array(
                    'id' => 4,
                    'name' => 'Brasilien',
                    'name_short' => 'BR',
                    'iso_3166_alpha2' => 'BR',
                ),
            4 =>
                array(
                    'id' => 5,
                    'name' => 'Kanada',
                    'name_short' => 'CDN',
                    'iso_3166_alpha2' => 'CA',
                ),
            5 =>
                array(
                    'id' => 6,
                    'name' => 'China',
                    'name_short' => 'CN',
                    'iso_3166_alpha2' => 'CN',
                ),
            6 =>
                array(
                    'id' => 7,
                    'name' => 'Tschechische Republik',
                    'name_short' => 'CZ',
                    'iso_3166_alpha2' => 'CZ',
                ),
            7 =>
                array(
                    'id' => 8,
                    'name' => 'Frankreich',
                    'name_short' => 'F',
                    'iso_3166_alpha2' => 'FR',
                ),
            8 =>
                array(
                    'id' => 9,
                    'name' => 'Grossbritannien',
                    'name_short' => 'GB',
                    'iso_3166_alpha2' => 'GB',
                ),
            9 =>
                array(
                    'id' => 10,
                    'name' => 'Italien',
                    'name_short' => 'I',
                    'iso_3166_alpha2' => 'IT',
                ),
            10 =>
                array(
                    'id' => 11,
                    'name' => 'Liechtenstein',
                    'name_short' => 'FL',
                    'iso_3166_alpha2' => 'LI',
                ),
            11 =>
                array(
                    'id' => 12,
                    'name' => 'Hong Kong',
                    'name_short' => 'HK',
                    'iso_3166_alpha2' => 'HK',
                ),
            12 =>
                array(
                    'id' => 13,
                    'name' => 'Portugal',
                    'name_short' => 'PT',
                    'iso_3166_alpha2' => 'PT',
                ),
            13 =>
                array(
                    'id' => 14,
                    'name' => 'Ägypten',
                    'name_short' => 'EG',
                    'iso_3166_alpha2' => 'EG',
                ),
            14 =>
                array(
                    'id' => 15,
                    'name' => 'USA',
                    'name_short' => 'USA',
                    'iso_3166_alpha2' => 'US',
                ),
            15 =>
                array(
                    'id' => 16,
                    'name' => 'Spanien',
                    'name_short' => 'ES',
                    'iso_3166_alpha2' => 'ES',
                ),
            16 =>
                array(
                    'id' => 17,
                    'name' => 'Australien',
                    'name_short' => 'AU',
                    'iso_3166_alpha2' => 'AU',
                ),
            17 =>
                array(
                    'id' => 18,
                    'name' => 'Argentinien',
                    'name_short' => 'AR',
                    'iso_3166_alpha2' => 'AR',
                ),
            18 =>
                array(
                    'id' => 19,
                    'name' => 'Malediven',
                    'name_short' => 'MV',
                    'iso_3166_alpha2' => 'MV',
                ),
            19 =>
                array(
                    'id' => 20,
                    'name' => 'Bulgarien',
                    'name_short' => 'BG',
                    'iso_3166_alpha2' => 'BG',
                ),
            20 =>
                array(
                    'id' => 21,
                    'name' => 'Rumänien',
                    'name_short' => 'RO',
                    'iso_3166_alpha2' => 'RO',
                ),
            21 =>
                array(
                    'id' => 22,
                    'name' => 'Luxemburg',
                    'name_short' => 'LU',
                    'iso_3166_alpha2' => 'LU',
                ),
            22 =>
                array(
                    'id' => 23,
                    'name' => 'Türkei',
                    'name_short' => 'TR',
                    'iso_3166_alpha2' => 'TR',
                ),
            23 =>
                array(
                    'id' => 24,
                    'name' => 'Israel',
                    'name_short' => 'ISR',
                    'iso_3166_alpha2' => 'IL',
                ),
            24 =>
                array(
                    'id' => 25,
                    'name' => 'Niederlande',
                    'name_short' => 'NL',
                    'iso_3166_alpha2' => 'NL',
                ),
            25 =>
                array(
                    'id' => 26,
                    'name' => 'Vereinigte Arabische Emirate',
                    'name_short' => 'VAE',
                    'iso_3166_alpha2' => 'AE',
                ),
            26 =>
                array(
                    'id' => 27,
                    'name' => 'Belgien',
                    'name_short' => 'B',
                    'iso_3166_alpha2' => 'BE',
                ),
            27 =>
                array(
                    'id' => 28,
                    'name' => 'Singapur',
                    'name_short' => 'SIN',
                    'iso_3166_alpha2' => 'SG',
                ),
            28 =>
                array(
                    'id' => 29,
                    'name' => 'Montenegro',
                    'name_short' => 'MTN',
                    'iso_3166_alpha2' => 'ME',
                ),
            29 =>
                array(
                    'id' => 30,
                    'name' => 'Katar',
                    'name_short' => 'KAT',
                    'iso_3166_alpha2' => 'QA',
                ),
            30 =>
                array(
                    'id' => 31,
                    'name' => 'Dänemark',
                    'name_short' => 'DK',
                    'iso_3166_alpha2' => 'DK',
                ),
            31 =>
                array(
                    'id' => 32,
                    'name' => 'Schweden',
                    'name_short' => 'SE',
                    'iso_3166_alpha2' => 'SE',
                ),
            32 =>
                array(
                    'id' => 33,
                    'name' => 'Malaysia',
                    'name_short' => 'MY',
                    'iso_3166_alpha2' => 'MY',
                ),
            33 =>
                array(
                    'id' => 34,
                    'name' => 'Irland',
                    'name_short' => 'IRL',
                    'iso_3166_alpha2' => 'IE',
                ),
            34 =>
                array(
                    'id' => 35,
                    'name' => 'Norwegen',
                    'name_short' => 'NO',
                    'iso_3166_alpha2' => 'NO',
                ),
            35 =>
                array(
                    'id' => 36,
                    'name' => 'Finnland',
                    'name_short' => 'FI',
                    'iso_3166_alpha2' => 'FI',
                ),
            36 =>
                array(
                    'id' => 37,
                    'name' => 'Ungarn',
                    'name_short' => 'HU',
                    'iso_3166_alpha2' => 'HU',
                ),
        );

        foreach ($countryList as $country) {
            if ($country['iso_3166_alpha2'] == $code) {
                return $country['id'];
            }
        }
        return false;
    }

    public function searchContact($email, $country_id)
    {
        if ($email && $country_id) {
            $contactSearceUrl = $this->contactSearceUrl;

            $requestArray = [
                array('field' => 'mail', 'value' => $email, 'criteria' => "="),
                array('field' => 'country_id', 'value' => $country_id, 'criteria' => "="),
            ];

            $requestJsonData = json_encode($requestArray);

            return $response = $this->_helperData->sendCurlRequestToBexio($contactSearceUrl, 'POST', $requestJsonData);
            //   print_r($response);
        }
    }

}
