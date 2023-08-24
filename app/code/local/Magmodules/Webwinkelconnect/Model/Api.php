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
    public function sendInvitation(Mage_Sales_Model_Order $order): bool
    {
        $start_time = microtime(true);
        $store_id = $order->getStoreId();
        $helper = Mage::helper('webwinkelconnect');
        $post_data['email'] = $order->getCustomerEmail();
        $post_data['order'] = $order->getIncrementId();
        $post_data['delay'] = trim(Mage::getStoreConfig('webwinkelconnect/invitation/delay', $store_id));
        $post_data['customer_name'] = $order->getCustomerName();
        $post_data['client'] = 'magento1';
        $post_data['platform_version'] = Mage::getVersion();
        $post_data['language'] = $this->getLanguage($store_id, $order);

        if (Mage::getStoreConfig('webwinkelconnect/product_review_invites/enabled')) {
            $post_data['order_data'] = json_encode([
                'order' => $order,
                'products' => $this->getOrderProducts($order),
            ]);
        }

        $url = 'https://dashboard.webwinkelkeur.nl/api/1.0/invitations.json?' .
            http_build_query([
                'id' => $helper->getShopId(),
                'code' => $helper->getApiKey(),
            ]);

        if (
            Mage::getStoreConfig('webwinkelconnect/privacy_first_option/privacy_popup')
            && !Mage::helper('webwinkelconnect')->hasConsent($order)
        ) {
            return false;
        }

        $ch = $helper->getCurlHandle($url, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $post_data,
        ]);
        $response = curl_exec($ch);
        if ($response === false) {
            Mage::getModel('webwinkelconnect/log')->addToLog(
                'invitation',
                $order->getStoreId(),
                '',
                $helper->__("There was an error sending the invitation to the dashboard: (%s) %s", curl_error($ch), curl_errno($ch)),
                (microtime(true) - $start_time),
                'orderupdate',
                $url,
                $order->getId()
            );
            return false;
        }

        Mage::getModel('webwinkelconnect/log')->addToLog(
            'invitation',
            $order->getStoreId(),
            '',
            json_decode($response)->message,
            (microtime(true) - $start_time),
            'orderupdate',
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

    private function getOrderProducts(Mage_Sales_Model_Order $order): array {
        $products = [];
        foreach ($order->getAllItems() as $item) {
            $product = $item->getProduct();
            if ($product->isConfigurable()) {
                continue;
            }
            $products[] = [
                'name' => $product->getName(),
                'url' => $this->getProductUrl($product),
                'id' => $product->getId(),
                'sku' => $product->getSku(),
                'image_url' => $this->getProductImageUrl($product),
                'brand' => $product->getAttributeText('manufacturer'),
            ];
        }

        return $products;
    }

    private function getProductImageUrl(Mage_Catalog_Model_Product $product): string {
        $media_config = Mage::getModel('catalog/product_media_config');
        if ($product->getMediaGalleryImages()->getSize()) {
            $image_file = $product->getImage();

            return $media_config->getMediaUrl($image_file);
        }

        $parent_ids = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
        if ($parent_ids) {
            $parent_id = $parent_ids[0];
            $parent_product = Mage::getModel('catalog/product')->load($parent_id);
            $image_file = $parent_product->getImage();

            return $media_config->getMediaUrl($image_file);
        }
        return '';
    }

    private function getLanguage(int $storeId, Mage_Sales_Model_Order $order): string {
        $language = Mage::getStoreConfig('webwinkelconnect/invitation/language', $storeId);
        if (!$language) {
            return explode('_', Mage::getStoreConfig('general/locale/code',$storeId))[0];
        }
        if ($language == 'cus') {
            $address = $order->getShippingAddress();
            return strtolower($address->getCountry());
        }
        return $language;
    }

    private function getProductUrl(Mage_Catalog_Model_Product $product): string {
        $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
        if ($parentIds) {
            return Mage::getModel('catalog/product')->load($parentIds[0])->getProductUrl();
        }
        return $product->getProductUrl();
    }

}