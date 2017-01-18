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

class Magmodules_Webwinkelconnect_Block_Sidebar extends Mage_Core_Block_Template
{

    /**
     * @param $sidebar
     * @return bool
     */
    public function getSidebarCollection($sidebar)
    {
        $enabled = '';
        $qty = '5';

        if (Mage::getStoreConfig('webwinkelconnect/general/enabled')) {
            if ($sidebar == 'left') {
                $qty = Mage::getStoreConfig('webwinkelconnect/sidebar/left_qty');
                $enabled = Mage::getStoreConfig('webwinkelconnect/sidebar/left');
            }

            if ($sidebar == 'right') {
                $qty = Mage::getStoreConfig('webwinkelconnect/sidebar/right_qty');
                $enabled = Mage::getStoreConfig('webwinkelconnect/sidebar/right');
            }
        }

        if ($enabled) {
            $shopId = Mage::getStoreConfig('webwinkelconnect/general/api_id');
            $collection = Mage::getModel("webwinkelconnect/reviews")->getCollection()
                ->setOrder('date', 'DESC')
                ->addFieldToFilter('status', 1)
                ->addFieldToFilter('sidebar', 1)
                ->addFieldToFilter('shop_id', array('eq' => array($shopId)))
                ->setPageSize($qty);

            return $collection->load();
        }

        return false;
    }

    /**
     * @param $sidebarreview
     * @param string $sidebar
     * @return mixed
     */
    public function formatContent($sidebarreview, $sidebar = 'left')
    {
        $content = $sidebarreview->getExperience();
        if ($sidebar == 'left') {
            $charLimit = Mage::getStoreConfig('webwinkelconnect/sidebar/left_lenght');
        }

        if ($sidebar == 'right') {
            $charLimit = Mage::getStoreConfig('webwinkelconnect/sidebar/right_lenght');
        }

        $content = Mage::helper('core/string')->truncate($content, $charLimit, ' ...');

        return $content;

    }

    /**
     * @param string $sidebar
     * @return bool|string
     */
    public function getReviewsUrl($sidebar = 'left')
    {
        $link = '';
        $url = '';

        if ($sidebar == 'left') {
            $link = Mage::getStoreConfig('webwinkelconnect/sidebar/left_link');
        }

        if ($sidebar == 'right') {
            $link = Mage::getStoreConfig('webwinkelconnect/sidebar/left_right');
        }

        if ($link == 'internal') {
            $url = $this->getUrl('webwinkelconnect');
            $class = '';
        }

        if ($link == 'external') {
            $url = Mage::getStoreConfig('webwinkelconnect/general/url');
            $class = 'webwinkelkeurReviews';
        }

        if ($url) {
            return '<a href="' . $url . '" class="' . $class . '">' . $this->__('View all reviews') . '</a>';
        } else {
            return false;
        }
    }

    /**
     * @param string $sidebar
     * @return bool
     */
    public function getSnippetsEnabled($sidebar = 'left')
    {
        if ($sidebar == 'left') {
            return Mage::getStoreConfig('webwinkelconnect/sidebar/left_snippets');
        }

        if ($sidebar == 'right') {
            return Mage::getStoreConfig('webwinkelconnect/sidebar/right_snippets');
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getTotalScore()
    {
        return $this->helper('webwinkelconnect')->getTotalScore();
    }

}    