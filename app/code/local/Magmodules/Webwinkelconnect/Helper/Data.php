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

class Magmodules_Webwinkelconnect_Helper_Data extends Mage_Core_Helper_Abstract
{
    private $curl_handle;

    /**
     * @return mixed
     */
    public function getTotalScore()
    {
        if (Mage::getStoreConfig('webwinkelconnect/general/enabled')) {
            $shopId = Mage::getStoreConfig('webwinkelconnect/general/api_id');
            $reviewStats = Mage::getModel('webwinkelconnect/stats')->load($shopId, 'shop_id');
            if ($reviewStats->getAverage() > 0) {
                $reviewStats->setPercentage($reviewStats->getAverage());
                $reviewStats->setStarsQty(number_format(($reviewStats->getPercentage() / 10), 1, ',', ''));

                return $reviewStats;
            }
        }

        return false;
    }

    /**
     * @return bool|string
     */
    public function getExternalLink()
    {
        if (Mage::getStoreConfig('webwinkelconnect/general/url')) {
            $url = Mage::getStoreConfig('webwinkelconnect/general/url');
            $url = ' <a href="' . $url . '" target="_blank">WebwinkelKeur.nl</a>';

            return Mage::helper('webwinkelconnect')->__('on') . $url;
        }

        return false;
    }

    /**
     * @param $rating
     * @return string
     */
    public function getHtmlStars($rating)
    {
        $html = '<div class="rating-box">';
        $html .= '	<div class="rating" style="width:' . $rating . '%"></div>';
        $html .= '</div>';

        return $html;
    }

    public function getApiKey(): ?string {
        $api_key = Mage::getStoreConfig('webwinkelconnect/general/api_key');
        return $api_key && $api_key != ''
            ? $api_key
            : null;
    }

    public function getShopId(): ?string {
        $shop_id = Mage::getStoreConfig('webwinkelconnect/general/api_id');
        return $shop_id && $shop_id != ''
            ? $shop_id
            : null;
    }

    public function isProductReviewInviteEnabled(): bool {
        if (!Mage::getStoreConfig('webwinkelconnect/general/enabled')) {
            return false;
        }
        if (!Mage::getStoreConfig('webwinkelconnect/product_review_invites/enabled')) {
            return false;
        }
        if ($this->getApiKey() === null) {
            return false;
        }
        if ($this->getShopId() === null) {
            return false;
        }

        $rating_options_collection = Mage::getModel('rating/rating')->getCollection();
        if ($rating_options_collection->getSize() < 1) {
            throw Mage::exception('Magmodules_Webwinkelconnect', $this->__('You have no ratings created. Please create at least one rating to use the product reviews feature.'));
        }

        return true;
    }

    public function hasCorrectCredentials(?string $webshop_id, ?string $api_key): bool {
        if (!trim($webshop_id) || !trim($api_key)) {
            return false;
        }
        if ($webshop_id != $this->getShopId() || !hash_equals($api_key, $this->getApiKey())) {
            return false;
        }
        return true;
    }

    public function syncProductReview(array $product_review): array {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        if (!$this->isProductReviewInviteEnabled()) {
            throw new RuntimeException("Product reviews are disabled");
        }
        try {
            $connection->beginTransaction();
            $product_id = (int) $product_review['product_review']['product_id'];
            $parent_id = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product_id);
            if ($parent_id) {
                $product_id = $parent_id[0];
            }
            if (!Mage::getModel('catalog/product')->load($product_id)->getId()) {
                return [
                    'message' => $this->__('Could not find product with ID (%d)', $product_id),
                    'code' => 404
                ];
            }
            $review = Mage::getModel('review/review');
            if (isset($product_review['product_review']['id'])) {
                $review = $review->load($product_review['product_review']['id']);
                if (!$review->getId()) {
                    return [
                        'message' => $this->__('Review with ID %s does not exist in Magento', $product_review['product_review']['id']),
                        'code' => 404
                    ];
                }
            }
            if (!empty($product_review['product_review']['deleted']) && !isset($product_review['product_review']['id'])) {
                return [
                    'message' => $this->__('Invalid review delete request, review ID missing.'),
                    'code' => 400
                ];
            }
            if (!empty($product_review['product_review']['deleted']) && $review->getId()) {
                $review_delete_start = microtime(true);
                Mage::register('isSecureArea', true);
                $review->delete();
                Mage::unregister('isSecureArea');
                $connection->commit();

                $review_delete = microtime(true) - $review_delete_start;
                Mage::getModel('webwinkelconnect/log')->addToLog(
                    'review_delete',
                    Mage::app()->getStore(),
                    '',
                    $this->__("Deleted review with ID (%s)", $product_review['product_review']['id']),
                    $review_delete,
                    '',
                    '',
                    $product_review['product_review']['id']
                );

                return ['message' => json_encode(['review_id' => $review->getId()]), 'code' => 200];
            }

            $review_edit_time_start = microtime(true);
            $review->setEntityPkValue($product_id)
                ->setStatusId(Mage_Review_Model_Review::STATUS_APPROVED)
                ->setTitle($product_review['product_review']['title'])
                ->setDetail($product_review['product_review']['review'])
                ->setEntityId($review->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE))
                ->setStoreId(Mage::app()->getStore()->getStoreId())
                ->setStores([Mage::app()->getStore()->getStoreId()])
                ->setCustomerId($this->getCustomerId($product_review['product_review']['reviewer']['email']))
                ->setNickname($product_review['product_review']['reviewer']['name'])
                ->save();

            $rating_option_id = Mage::getModel('rating/rating_option')->getCollection()
                ->addFieldToFilter('rating_id', Mage::getStoreConfig('webwinkelconnect/product_review_invites/rating'))
                ->addFieldToFilter('code', $product_review['product_review']['rating'])
                ->getFirstItem()
                ->getOptionId();
            if (!$rating_option_id) {
                Mage::log('The dashboard rating has no analog in Magento.', Zend_Log::ERR, 'exception.log', true);
                throw Mage::exception('Magmodules_Webwinkelconnect', $this->__('The dashboard rating has no analog in Magento.'));
            }
            Mage::getModel('rating/rating')
                ->setRatingId(Mage::getStoreConfig('webwinkelconnect/product_review_invites/rating'))
                ->setReviewId($review->getId())
                ->addOptionVote($rating_option_id, $product_id);

            $review->aggregate();
            $review->setCreatedAt($product_review['product_review']['created']);
            $review->save();

            $review_edit_time = microtime(true) - $review_edit_time_start;

            $connection->commit();

            if ($product_review['product_review']['id']) {

                Mage::getModel('webwinkelconnect/log')->addToLog(
                    'review_edit',
                    Mage::app()->getStore(),
                    '',
                    $this->__("Edited review with ID (%s)", $product_review['product_review']['id']),
                    $review_edit_time,
                    '',
                    '',
                    $product_review['product_review']['id']
                );
            }

            return [
                'message' => json_encode(['review_id' => $review->getId()]),
                'code' => 200
            ];
        } catch (Exception $e) {
            $connection->rollback();
            return ['message' => $e->getMessage(), 'code' => 500];
        }
    }


    public function hasConsent(Mage_Sales_Model_Order $order): bool {
        $permission_url = 'https://dashboard.webwinkelkeur.nl/api/2.0/order_permissions.json?' .
            http_build_query([
                'orderNumber' => $order->getIncrementId(),
                'id' => $this->getShopId(),
                'code' => $this->getApiKey(),
            ]);

        $start_time = microtime(true);
        $ch = $this->getCurlHandle($permission_url);

        $response = curl_exec($ch);
        if ($response === false) {
            $message = $this->__("Consent check failed with cURL error: (%s) %s", curl_errno($ch), curl_error($ch));
        } elseif (empty(json_decode($response)->has_consent)) {
            $message = 'Invitation has not been sent as the customer did not consent.';
        } else {
            return true;
        }

        Mage::getModel('webwinkelconnect/log')->addToLog(
            'invitation',
            $order->getStoreId(),
            '',
            $message,
            (microtime(true) - $start_time),
            'orderupdate',
            $permission_url,
            $order->getId()
        );
        return false;
    }

    public function getCurlHandle(string $url, array $options = []) {
        if (!$this->curl_handle) {
            $this->curl_handle = curl_init();
        } else {
            curl_reset($this->curl_handle);
        }
        $default_curl_options = [
            CURLOPT_TIMEOUT => 10,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_FAILONERROR => true,
            CURLOPT_URL => $url,
        ];
        $all_options = curl_setopt_array($this->curl_handle, $default_curl_options + $options);
        if (!$all_options) {
            throw new RuntimeException($this->__("Could not set cURL options: (%s) %s", curl_errno($this->curl_handle), curl_error($this->curl_handle)));
        }
        return $this->curl_handle;
    }


    public function getOrderProducts(Mage_Sales_Model_Order $order): array {
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

    private function getProductUrl(Mage_Catalog_Model_Product $product): string {
        $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
        if ($parentIds) {
            return Mage::getModel('catalog/product')->load($parentIds[0])->getProductUrl();
        }
        return $product->getProductUrl();
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

    public function getInviteLanguage(int $storeId, Mage_Sales_Model_Order $order): string {
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

    private function getCustomerId(string $email):? int {
        return Mage::getModel('customer/customer')
            ->setWebsiteId(Mage::app()->getWebsite()->getId())
            ->loadByEmail($email)
            ->getId();
    }
}