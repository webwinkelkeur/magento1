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

class Magmodules_Webwinkelconnect_Model_System_Config_Source_Language
{

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $lan = array();
        $lan[] = array('value' => '', 'label' => Mage::helper('webwinkelconnect')->__('Use default'));
        $lan[] = array('value' => 'cus', 'label' => Mage::helper('webwinkelconnect')->__('Based on customer country'));
        $lan[] = array('value' => 'nld', 'label' => Mage::helper('webwinkelconnect')->__('Dutch'));
        $lan[] = array('value' => 'eng', 'label' => Mage::helper('webwinkelconnect')->__('English'));
        $lan[] = array('value' => 'deu', 'label' => Mage::helper('webwinkelconnect')->__('German'));
        $lan[] = array('value' => 'fra', 'label' => Mage::helper('webwinkelconnect')->__('French'));
        $lan[] = array('value' => 'spa', 'label' => Mage::helper('webwinkelconnect')->__('Spanish'));
        $lan[] = array('value' => 'ita', 'label' => Mage::helper('webwinkelconnect')->__('Italian'));
        $lan[] = array('value' => 'hrv', 'label' => Mage::helper('webwinkelconnect')->__('Croatian'));
        $lan[] = array('value' => 'bul', 'label' => Mage::helper('webwinkelconnect')->__('Bulgarian'));

        return $lan;
    }

} 