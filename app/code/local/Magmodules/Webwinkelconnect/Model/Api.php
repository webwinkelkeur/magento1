<?php
/**
 * Magmodules.eu - http://www.magmodules.eu
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@magmodules.eu so we can send you a copy immediately.
 *
 * @category      Magmodules
 * @package       Magmodules_WebwinkelKeur
 * @author        Magmodules <info@magmodules.eu>
 * @copyright     Copyright (c) 2017 (http://www.magmodules.eu)
 * @license       http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */

class Magmodules_Webwinkelconnect_Model_Api extends Mage_Core_Model_Abstract
{

    /**
     * @param int $storeid
     * @param $type
     * @return bool
     */
    public function processFeed($storeid = 0, $type)
    {
        if ($feed = $this->getFeed($storeid, $type)) {
            $results = Mage::getModel('webwinkelconnect/reviews')->processFeed($feed, $storeid, $type);
            $results['stats'] = Mage::getModel('webwinkelconnect/stats')->processFeed($feed, $storeid);

            return $results;
        } else {
            return false;
        }
    }

    /**
     * @param $storeid
     * @param string $type
     * @return bool|SimpleXMLElement
     */
    public function getFeed($storeid, $type = '')
    {
        $apiId = trim(Mage::getStoreConfig('webwinkelconnect/general/api_id', $storeid));
        $apiKey = trim(Mage::getStoreConfig('webwinkelconnect/general/api_key', $storeid));
        $apiUrl = 'https://www.webwinkelkeur.nl/apistatistics.php?id=' . $apiId . '&password=' . $apiKey;

        if ($apiId && $apiKey) {
            if(@simplexml_load_file($apiUrl)) {

                if ($type != 'stats') {
                    $apiUrl .= '&showall=1';
                }

                return simplexml_load_file($apiUrl);
            } else {
                $e = file_get_contents($apiUrl);
                $msg = Mage::helper('webwinkelconnect')->__('%s, please check the online manual for suggestions.', $e);
                Mage::getSingleton('adminhtml/session')->addError($msg);
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param $order
     * @return bool
     */
    public function sendInvitation($order)
    {
        $startTime = microtime(true);
        $crontype = 'orderupdate';
        $orderId = $order->getIncrementId();
        $storeId = $order->getStoreId();
        $apiId = trim(Mage::getStoreConfig('webwinkelconnect/general/api_id', $storeId));
        $apiKey = trim(Mage::getStoreConfig('webwinkelconnect/general/api_key', $storeId));
        $delay = trim(Mage::getStoreConfig('webwinkelconnect/invitation/delay', $storeId));
        $language = Mage::getStoreConfig('webwinkelconnect/invitation/language', $storeId);
        $resendDouble = Mage::getStoreConfig('webwinkelconnect/invitation/resend_double', $storeId);

        $email = $order->getCustomerEmail();
        $customerName = $order->getCustomerName();

        $url = 'https://www.webwinkelkeur.nl/api.php?id=' . $apiId;
        $url .= '&password=' . $apiKey . '&email=' . urlencode($email) . '&order=' . $orderId;
        $url .= '&delay=' . $delay . '&client=magento&customername=' . urlencode($customerName);

        if (!$resendDouble) {
            $url = $url . '&noremail=1';
        }

        if (!empty($language)) {
            if ($language == 'cus') {
                $lanArray = array('NL' => 'nld', 'EN' => 'eng', 'DE' => 'deu', 'FR' => 'fra', 'ES' => 'spa');
                $address = $order->getShippingAddress();
                if (isset($lanArray[$address->getCountry()])) {
                    $url = $url . '&language=' . $lanArray[$address->getCountry()];
                }
            } else {
                $url = $url . '&language=' . $language;
            }
        }

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_VERBOSE, 1);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($curl);
        curl_close($curl);

        if ($response) {
            $responseHtml = $response;
        } else {
            $responseHtml = 'No response from https://www.webwinkelkeur.nl';
        }

        Mage::getModel('webwinkelconnect/log')->addToLog(
            'invitation', $order->getStoreId(),
            '',
            $responseHtml,
            (microtime(true) - $startTime),
            $crontype,
            $url,
            $order->getId()
        );

        return true;
    }


    /**
     * @return array
     */
    public function getStoreIds()
    {
        $storeIds = array();
        $apiIds = array();
        $stores = Mage::getModel('core/store')->getCollection();
        foreach ($stores as $store) {
            if ($store->getIsActive()) {
                $apiId = Mage::getStoreConfig('webwinkelconnect/general/api_id', $store->getId());
                if (!in_array($apiId, $apiIds)) {
                    $apiIds[] = $apiId;
                    $storeIds[] = $store->getId();
                }
            }
        }

        return $storeIds;
    }

}