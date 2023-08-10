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

    public function syncAction(): void {
        $request_data = file_get_contents('php://input');
        if (!$request_data) {
            $this->getResponse()->setHttpResponseCode(400)->setBody('Empty request data');
            return;
        }
        if (!$request_data = json_decode($request_data, true)) {
            $this->getResponse()->setHttpResponseCode(400)->setBody('Invalid JSON data provided');
            return;
        }

        if (!isset($request_data['webshop_id']) || !isset($request_data['api_key'])) {
            $this->getResponse()->setHttpResponseCode(403)->setBody('Missing one or more credential fields');
            return;
        }

        $helper = Mage::helper('webwinkelconnect');

        if (!$helper->hasCorrectCredentials(strval($request_data['webshop_id']), strval($request_data['api_key']))) {
            $this->getResponse()->setHttpResponseCode(403)->setBody('Empty or incorrect credential fields');
            return;
        }

        if (!Mage::getModel('catalog/product')->load(strval($request_data['product_review']['product_id']))->getId()) {
            $this->getResponse()->setHttpResponseCode(404)->setBody(sprintf('Could not find product with ID (%d)', $request_data['product_review']['product_id']));
            return;
        }

        if (!Mage::getStoreConfig('webwinkelconnect/product_review_invites/enabled')) {
            $this->getResponse()->setHttpResponseCode(403)->setBody('Product review sync is disabled');
            return;
        }
        $sync_response = $helper->syncProductReview($request_data);
        $this->getResponse()
            ->setHttpResponseCode($sync_response['code'])
            ->setBody($sync_response['message']);
    }
}