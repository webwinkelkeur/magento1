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
 
class Magmodules_Webwinkelconnect_Adminhtml_WebwinkelreviewsController extends Mage_Adminhtml_Controller_Action
{

    /**
     * @return $this
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('webwinkelconnect/webwinkelreviews')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

        return $this;
    }

    /**
     * Index Action
     */
    public function indexAction()
    {
        $this->_initAction()->renderLayout();
    }

    /**
     * Process Action
     */
    public function processAction()
    {            
        $storeIds = Mage::getModel('webwinkelconnect/api')->getStoreIds();                
        $startTime = microtime(true);        
        foreach ($storeIds as $storeId) {
            $msg = '';
            $apiId = Mage::getStoreConfig('webwinkelconnect/general/api_id', $storeId);
            $result = Mage::getModel('webwinkelconnect/api')->processFeed($storeId, 'history');        
            $time = (microtime(true) - $startTime);
            Mage::getModel('webwinkelconnect/log')->addToLog('reviews', $storeId, $result, '', $time, '', '');

            if (($result['review_new'] > 0) || ($result['review_updates'] > 0) || ($result['stats'] == true)) {
                $msg = Mage::helper('webwinkelconnect')->__('Webwinkel ID %s:', $apiId) . ' '; 
                $msg .= Mage::helper('webwinkelconnect')->__('%s new review(s)', $result['review_new']) . ', '; 
                $msg .= Mage::helper('webwinkelconnect')->__('%s review(s) updated', $result['review_updates']) . ' & '; 
                $msg .= Mage::helper('webwinkelconnect')->__('and total score updated.');
            }

            if ($msg) {
                Mage::getSingleton('adminhtml/session')->addSuccess($msg);                            
            } else {
                if (Mage::getSingleton('adminhtml/session')->getMessages()->count() < 1) {
                    $msg = $this->__('Webwinkel ID %s: no updates found, feed is empty or not foud!', $apiId);
                    Mage::getSingleton('adminhtml/session')->addError($msg);
                }    
            }
        }
                
        $this->_redirect('adminhtml/system_config/edit/section/webwinkelconnect');
    }

    /**
     * Mass disable reviews from Admin Grid
     */
    public function massDisableAction()
    {    
        $reviewIds = $this->getRequest()->getParam('reviewids');
        if (!is_array($reviewIds)) {
            $msg = Mage::helper('webwinkelconnect')->__('Please select item(s)');
            Mage::getSingleton('adminhtml/session')->addError($msg);
        } else {
            try {
                foreach ($reviewIds as $reviewId) {
                    $reviews = Mage::getModel('webwinkelconnect/reviews')->load($reviewId);
                    $reviews->setStatus(0)->save();
                }

                $msg = $this->__('Total of %d review(s) were disabled.', count($reviewIds));
                Mage::getSingleton('adminhtml/session')->addSuccess($msg);
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }

    /**
     * Mass enable reviews from Admin Grid
     */
    public function massEnableAction()
    {    
        $reviewIds = $this->getRequest()->getParam('reviewids');
        if (!is_array($reviewIds)) {
            $msg = Mage::helper('webwinkelconnect')->__('Please select item(s)');
            Mage::getSingleton('adminhtml/session')->addError($msg);
        } else {
            try {
                foreach ($reviewIds as $reviewId) {
                    $reviews = Mage::getModel('webwinkelconnect/reviews')->load($reviewId);
                    $reviews->setStatus(1)->save();
                }

                $msg = Mage::helper('webwinkelconnect')->__('Total of %d review(s) were enabled.', count($reviewIds));
                Mage::getSingleton('adminhtml/session')->addSuccess($msg);
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }

    /**
     * Mass enable reviews sidebar from Admin Grid
     */
    public function massEnableSidebarAction()
    {    
        $reviewIds = $this->getRequest()->getParam('reviewids');
        if (!is_array($reviewIds)) {
            $msg = Mage::helper('webwinkelconnect')->__('Please select item(s)');
            Mage::getSingleton('adminhtml/session')->addError($msg);
        } else {
            try {
                foreach ($reviewIds as $reviewId) {
                    $reviews = Mage::getModel('webwinkelconnect/reviews')->load($reviewId);
                    $reviews->setSidebar(1)->save();
                }

                $msg = $this->__('Total of %d review(s) were added to the sidebar.', count($reviewIds));
                Mage::getSingleton('adminhtml/session')->addSuccess($msg);
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }

    /**
     * Mass disable reviews sidebar from Admin Grid
     */
    public function massDisableSidebarAction()
    {    
        $reviewIds = $this->getRequest()->getParam('reviewids');
        if (!is_array($reviewIds)) {
            $msg = Mage::helper('webwinkelconnect')->__('Please select item(s)');
            Mage::getSingleton('adminhtml/session')->addError($msg);
        } else {
            try {
                foreach ($reviewIds as $reviewId) {
                    $reviews = Mage::getModel('webwinkelconnect/reviews')->load($reviewId);
                    $reviews->setSidebar(0)->save();
                }

                $msg = $this->__('Total of %d review(s) were removed from the sidebar.', count($reviewIds));
                Mage::getSingleton('adminhtml/session')->addSuccess($msg);
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }

    /**
     * Truncate reviews table from Admin Grid
     */
    public function truncateAction()
    {     
        $collection = Mage::getModel('webwinkelconnect/reviews')->getCollection();
        foreach ($collection as $item) {
            $item->delete();
        }

        $msg = Mage::helper('webwinkelconnect')->__('Succefully deleted all %s saved review(s).', count($collection));
        Mage::getSingleton('adminhtml/session')->addSuccess($msg);

        $this->_redirect('*/*/index');
    }

    /**
     * @return mixed
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/webwinkelconnect/webwinkelconnect_log');
    }
    
}