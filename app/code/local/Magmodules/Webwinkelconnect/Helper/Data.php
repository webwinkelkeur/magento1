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

        return true;
    }

    public function hasCredentialFields(?string $webshop_id, ?string $api_key): bool {
        return isset($webshop_id) && isset($api_key);
    }

    public function credentialsEmpty(?string $webshop_id, ?string $api_key): bool {
        return !trim($webshop_id) || !trim($api_key);
    }

    public function isAuthorized(string $webshop_id, string $api_key): void {
        if ($webshop_id == $this->getShopId() && hash_equals($api_key, $this->getApiKey())) {
            return;
        }
        $this->returnResponseCode(401, 'Wrong credentials');
    }

    public function syncProductReview(array $product_review): void {
        $review = Mage::getModel('review/review')
            ->setEntityPkValue($product_review['product_review']['product_id'])
            ->setStatusId(Mage_Review_Model_Review::STATUS_PENDING)
            ->setTitle($product_review['product_review']['title'])
            ->setDetail(str_replace('<br>', "\n", $product_review['product_review']['review']))
            ->setEntityId(1)
            ->setStoreId(Mage::app()->getStore()->getStoreId())
            ->setStores([Mage::app()->getStore()->getStoreId()])
            ->setCustomerId($this->getCustomerId($product_review['product_review']['reviewer']['email']))
            ->setNickname($product_review['product_review']['reviewer']['name'])
            ->save();

        Mage::getModel('rating/rating')
            ->setRatingId(Mage::getStoreConfig('webwinkelconnect/product_review_invites/rating'))
            ->setReviewId($review->getId())
            ->addOptionVote($product_review['product_review']['rating'], $product_review['product_review']['product_id']);
        $review->aggregate();
        $review->setCreatedAt(DateTime::createFromFormat('Y-m-d H:i:s', $product_review['created']));
        $review->save();
    }

    private function getCustomerId(string $email):? int {
        return Mage::getModel('customer/customer')
            ->setWebsiteId(Mage::app()->getWebsite()->getId())
            ->loadByEmail($email)
            ->getId();
    }

    public function returnResponseCode(int $code, string $message): void {
        http_response_code($code);
        die($message);
    }

}