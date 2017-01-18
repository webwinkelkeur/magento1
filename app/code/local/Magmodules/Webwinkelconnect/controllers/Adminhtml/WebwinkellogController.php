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

class Magmodules_Webwinkelconnect_Adminhtml_WebwinkellogController extends Mage_Adminhtml_Controller_Action
{

    /**
     * @return $this
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('webwinkelconnect/webwinkellog')
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
     * Mass delete log records from Admin Gird
     */
    public function massDeleteAction()
    {
        $logIds = $this->getRequest()->getParam('logids');
        if (!is_array($logIds)) {
            $msg = Mage::helper('webwinkelconnect')->__('Please select item(s)');
            Mage::getSingleton('adminhtml/session')->addError($msg);
        } else {
            try {
                foreach ($logIds as $id) {
                    Mage::getModel('webwinkelconnect/log')->load($id)->delete();
                }

                $msg = Mage::helper('webwinkelconnect')->__('Total of %d log record(s) deleted.', count($logIds));
                Mage::getSingleton('adminhtml/session')->addSuccess($msg);
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }

    /**
     * Clean log Action from Admin Grid
     */
    public function cleanAction()
    {
        $enabled = Mage::getStoreConfig('webwinkelconnect/log/clean');
        $days = Mage::getStoreConfig('webwinkelconnect/log/clean_days');
        if (($enabled) && ($days > 0)) {
            $logmodel = Mage::getModel('webwinkelconnect/log');
            $deldate = date('Y-m-d', strtotime('-' . $days . ' days'));
            $logs = $logmodel->getCollection()
                ->addFieldToSelect('id')
                ->addFieldToFilter('date', array('lteq' => $deldate));
            foreach ($logs as $log) {
                $logmodel->load($log->getId())->delete();
            }

            $msg = Mage::helper('webwinkelconnect')->__('Total of %s log record(s) deleted.', count($logs));
            Mage::getSingleton('adminhtml/session')->addSuccess($msg);
        }

        $this->_redirect('*/*/index');
    }

    /**
     * @return mixed
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/webwinkelconnect/webwinkelconnect_reviews');
    }

}