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
 
class Magmodules_Webwinkelconnect_IndexController extends Mage_Core_Controller_Front_Action
{
    
    public function indexAction() 
    {
        $enabled = Mage::getStoreConfig('webwinkelconnect/general/enabled');
        $overview = Mage::getStoreConfig('webwinkelconnect/overview/enabled');
        if ($enabled && $overview) {
            $this->loadLayout();
            $head = $this->getLayout()->getBlock('head');        

            if ($title = Mage::getStoreConfig('webwinkelconnect/overview/meta_title')) {
                $head->setTitle($title);
            }

            if ($description = Mage::getStoreConfig('webwinkelconnect/overview/meta_description')) {
                $head->setDescription($description);
            }

            if ($keywords = Mage::getStoreConfig('webwinkelconnect/overview/meta_keywords')) {
                $head->setKeywords($keywords);
            }
        
            $this->renderLayout();
        } else {
            $this->_redirect('/');    
        }
    }

    /**
     * @throws Exception
     */
    public function syncAction() {
        $request_data = trim(file_get_contents('php://input'));
        $helper = Mage::helper('webwinkelconnect');
        if (!$request_data) {
            $helper->returnResponseCode(400, 'Empty request data');
        }
        if (!$request_data = json_decode($request_data, true)) {
            $helper->returnResponseCode(400, 'Invalid JSON data provided');
        }


        if (
            !$helper->hasCredentialFields($request_data['webshop_id'], $request_data['api_key'])
            || $helper->credentialsEmpty($request_data['webshop_id'], $request_data['api_key'])
        ) {
            $helper->returnResponseCode(403,'Missing credential fields');
        }

        $helper->isAuthorized($request_data['webshop_id'], $request_data['api_key']);

        if (empty(Mage::getResourceSingleton('catalog/product')->getProductsSku([$request_data['product_review']['product_id']]))) {
            $helper->returnResponseCode(404,sprintf('Could not find product with ID (%d)', $request_data['product_review']['product_id']));
        }

        if (!Mage::getStoreConfig('webwinkelconnect/product_review_invites/enabled')) {
            $helper->returnResponseCode(403,'Product review sync is disabled.');
        }

        $helper->syncProductReview($request_data);
    }
}