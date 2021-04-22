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

class Magmodules_Webwinkelconnect_Model_Stats extends Mage_Core_Model_Abstract
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('webwinkelconnect/stats');
    }

    /**
     * @param $feed
     * @param int $storeId
     * @return bool
     */
    public function processFeed($feed, $storeId = 0)
    {
        $apiId = Mage::getStoreConfig('webwinkelconnect/general/api_id', $storeId);

        if ($storeId == 0) {
            $config = Mage::getModel('core/config');
            $config->saveConfig('webwinkelconnect/general/url', $feed->link, 'default', $storeId);
            $config->saveConfig('webwinkelconnect/general/company', $feed->company, 'default', $storeId);
        } else {
            $config = Mage::getModel('core/config');
            $config->saveConfig('webwinkelconnect/general/url', $feed->link, 'stores', $storeId);
            $config->saveConfig('webwinkelconnect/general/company', $feed->company, 'stores', $storeId);
        }

        if ($feed->votes > 0) {
            $company = $feed->company;
            $average = floatval($feed->average);
            $averageStars = floatval($feed->average_stars);
            $average = ($average * 10);
            $averageStars = ($averageStars * 10);
            $votes = $feed->votes;
            $percentagePositive = $feed->percentage_positive;
            $numberPositive = $feed->number_positive;
            $percentageNeutral = $feed->percentage_neutral;
            $numberNeutral = $feed->number_neutral;
            $percentageNegative = $feed->percentage_negative;
            $numberNegative = $feed->number_negative;

            if ($indatabase = $this->loadbyApiId($apiId)) {
                $id = $indatabase->getId();
            } else {
                $id = '';
            }

            $model = Mage::getModel('webwinkelconnect/stats');
            $model->setId($id)
                ->setShopId($apiId)
                ->setCompany($company)
                ->setAverage($average)
                ->setAverageStars($averageStars)
                ->setVotes($votes)
                ->setPercentagePositive($percentagePositive)
                ->setNumberPositive($numberPositive)
                ->setPercentageNeutral($percentageNeutral)
                ->setNumberNeutral($numberNeutral)
                ->setPercentageNegative($percentageNegative)
                ->setNumberNegative($numberNegative)
                ->save();

            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $apiId
     * @return $this
     */
    public function loadbyApiId($apiId)
    {
        $this->_getResource()->load($this, $apiId, 'shop_id');

        return $this;
    }

}