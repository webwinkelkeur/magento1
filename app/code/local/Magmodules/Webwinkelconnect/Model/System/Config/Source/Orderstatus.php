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
 
class Magmodules_Webwinkelconnect_Model_System_Config_Source_Orderstatus
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $storeModel = Mage::getSingleton('adminhtml/system_store');
        $statuses = Mage::getSingleton('sales/order_config')->getStatuses();

        $options = array();
        $options[] = array('value' => 0, 'label' => Mage::helper('webwinkelconnect')->__('-- none'));
        foreach ($statuses as $k => $v) {
            $options[] = array('label' => $v, 'value' => $k);
        }

        return $options;
    }
    
} 