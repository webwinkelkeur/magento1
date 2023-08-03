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
            Mage::throwException($this->__('You have no ratings created. Please create at least one rating to use the product reviews feature.'));
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
            $product_id = $product_review['product_review']['product_id'];
            $parent_id = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product_id);
            if (!empty($parent_id)) {
                $product_id = $parent_id[0];
            }

            ($review = Mage::getModel('review/review'))
                ->setEntityPkValue($product_id)
                ->setStatusId(Mage_Review_Model_Review::STATUS_PENDING)
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

            Mage::getModel('rating/rating')
                ->setRatingId(Mage::getStoreConfig('webwinkelconnect/product_review_invites/rating'))
                ->setReviewId($review->getId())
                ->addOptionVote($rating_option_id, $product_id);
            $review->aggregate();
            $review->setCreatedAt($product_review['product_review']['created']);
            $review->save();

            $connection->commit();
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

        $curl = new Varien_Http_Adapter_Curl();
        $curl->setConfig([
            'timeout'   => 10,
        ]);
        $curl->addOptions([
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_FAILONERROR => true,
        ]);
        $curl->write(Zend_Http_Client::GET, $permission_url, '2', false, $order->getIncrementId());
        $response = $curl->read();
        $log = Mage::getModel('webwinkelconnect/log');
        $start_time = microtime(true);
        if ($response) {
            $response_html = Zend_Http_Response::fromString($response);
        } else {
            $log->addToLog(
                'invitation',
                $order->getStoreId(),
                '',
                $this->__(
                    sprintf(
                        'Curl error number: %s. Curl error message: %s',
                        $curl->getErrno(),
                        $curl->getError()
                    )
                ),
                (microtime(true) - $start_time),
                'orderupdate',
                $permission_url,
                $order->getId()
            );
            return false;
        }
        if($response_html->getStatus() != 200) {
            $log->addToLog(
                'invitation',
                $order->getStoreId(),
                '',
                $this->__(
                    sprintf(
                        'Response code: %s. Response body: %s',
                        $response_html->getStatus(),
                        $response_html->getBody()
                    )
                ),
                (microtime(true) - $start_time),
                'orderupdate',
                $permission_url,
                $order->getId()
            );
            return false;
        }
        $decoded_body = json_decode($response_html->getBody());
        if (!$decoded_body->has_consent) {
            $log->addToLog(
                'invitation',
                $order->getStoreId(),
                '',
                $this->__(
                    sprintf(
                        'Invitation has not been sent as the customer did not consent. Response code: %s. Response body: %s',
                        $response_html->getStatus(),
                        $response_html->getBody()
                    )
                ),
                (microtime(true) - $start_time),
                'orderupdate',
                $permission_url,
                $order->getId()
            );
            return false;
        }
        return true;
    }

    private function getCustomerId(string $email):? int {
        return Mage::getModel('customer/customer')
            ->setWebsiteId(Mage::app()->getWebsite()->getId())
            ->loadByEmail($email)
            ->getId();
    }
}