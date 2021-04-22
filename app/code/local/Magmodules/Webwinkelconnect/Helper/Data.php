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

}