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

class Magmodules_Webwinkelconnect_Model_Observer
{

    /**
     * Stats cron
     */
    public function processStats()
    {
        $storeIds = Mage::getModel('webwinkelconnect/api')->getStoreIds();
        foreach ($storeIds as $storeId) {
            $enabled = Mage::getStoreConfig('webwinkelconnect/general/enabled', $storeId);
            $cronEnabled = Mage::getStoreConfig('webwinkelconnect/reviews/cron', $storeId);
            if ($enabled && $cronEnabled) {
                $crontype = 'stats';
                $startTime = microtime(true);
                $feed = Mage::getModel('webwinkelconnect/api')->getFeed($storeId, $crontype);
                $results = array();
                $results['stats'] = Mage::getModel('webwinkelconnect/stats')->processFeed($feed, $storeId);
                $results['company'] = $feed->company;

                $log = Mage::getModel('webwinkelconnect/log');
                $log->addToLog('reviews', $storeId, $results, '', (microtime(true) - $startTime), $crontype);
            }
        }
    }

    /**
     * Reviews cron
     */
    public function processReviews()
    {
        $storeIds = Mage::getModel('webwinkelconnect/api')->getStoreIds();
        foreach ($storeIds as $storeId) {
            $enabled = Mage::getStoreConfig('webwinkelconnect/general/enabled', $storeId);
            $cronEnabled = Mage::getStoreConfig('webwinkelconnect/reviews/cron', $storeId);
            if ($enabled && $cronEnabled) {
                $crontype = 'reviews';
                $startTime = microtime(true);
                $feed = Mage::getModel('webwinkelconnect/api')->getFeed($storeId, $crontype);
                $results = Mage::getModel('webwinkelconnect/reviews')->processFeed($feed, $storeId, $crontype);
                $results['stats'] = Mage::getModel('webwinkelconnect/stats')->processFeed($feed, $storeId);

                $log = Mage::getModel('webwinkelconnect/log');
                $log->addToLog('reviews', $storeId, $results, '', (microtime(true) - $startTime), $crontype);
            }
        }
    }

    /**
     * History cron
     */
    public function processHistory()
    {
        $storeIds = Mage::getModel('webwinkelconnect/api')->getStoreIds();
        foreach ($storeIds as $storeId) {
            $enabled = Mage::getStoreConfig('webwinkelconnect/general/enabled', $storeId);
            $cronEnabled = Mage::getStoreConfig('webwinkelconnect/reviews/cron', $storeId);
            if ($enabled && $cronEnabled) {
                $crontype = 'history';
                $startTime = microtime(true);
                $storeId = 0;
                $feed = Mage::getModel('webwinkelconnect/api')->getFeed($storeId, $crontype);
                $results = Mage::getModel('webwinkelconnect/reviews')->processFeed($feed, $storeId, $crontype);
                $results['stats'] = Mage::getModel('webwinkelconnect/stats')->processFeed($feed, $storeId);

                $log = Mage::getModel('webwinkelconnect/log');
                $log->addToLog('reviews', $storeId, $results, '', (microtime(true) - $startTime), $crontype);
            }
        }
    }

    /**
     * Clean log cron
     */
    public function cleanLog()
    {
        $enabled = Mage::getStoreConfig('webwinkelconnect/log/clean', 0);
        $days = Mage::getStoreConfig('webwinkelconnect/log/clean_days', 0);
        if (($enabled) && ($days > 0)) {
            $deldate = date('Y-m-d', strtotime('-' . $days . ' days'));
            $collection = Mage::getModel('webwinkelconnect/log')->getCollection()
                ->addFieldToSelect('id')
                ->addFieldToFilter('date', array('lteq' => $deldate));

            foreach ($collection as $log) {
                $log->delete();
            }
        }
    }

    /**
     * Inventation observer fired after shipment create
     * @param $observer
     */
    public function processInvitationcallAfterShipment($observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();
        $enabled = Mage::getStoreConfig('webwinkelconnect/invitation/enabled', $order->getStoreId());
        $apiKey = Mage::getStoreConfig('webwinkelconnect/general/api_key', $order->getStoreId());
        if ($enabled && $apiKey) {
            $status = Mage::getStoreConfig('webwinkelconnect/invitation/status', $order->getStoreId());
            if ($order->getStatus() == $status) {
                $diff = floor(time() - strtotime($order->getCreatedAt())) / (60 * 60 * 24);
                $backlog = Mage::getStoreConfig('webwinkelconnect/invitation/backlog', $order->getStoreId());
                if ($backlog > 0) {
                    if ($diff < $backlog) {
                        Mage::getModel('webwinkelconnect/api')->sendInvitation($order);
                    }
                } else {
                    Mage::getModel('webwinkelconnect/api')->sendInvitation($order);
                }
            }
        }
    }

    /**
     * @param $observer
     */
    public function processInvitationcall($observer)
    {
        $order = $observer->getEvent()->getOrder();
        $invEnabled = Mage::getStoreConfig('webwinkelconnect/invitation/enabled', $order->getStoreId());
        $apiKey = Mage::getStoreConfig('webwinkelconnect/general/api_key', $order->getStoreId());
        if ($invEnabled && !empty($apiKey)) {
            $status = Mage::getStoreConfig('webwinkelconnect/invitation/status', $order->getStoreId());
            if ($order->getStatus() == $status) {
                $diff = floor(time() - strtotime($order->getCreatedAt())) / (60 * 60 * 24);
                $backlog = Mage::getStoreConfig('webwinkelconnect/invitation/backlog', $order->getStoreId());
                if ($backlog > 0) {
                    if ($diff < $backlog) {
                        Mage::getModel('webwinkelconnect/api')->sendInvitation($order);
                    }
                } else {
                    Mage::getModel('webwinkelconnect/api')->sendInvitation($order);
                }
            }
        }
    }

}