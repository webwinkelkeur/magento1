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
 
class Magmodules_Webwinkelconnect_Block_Custom extends Mage_Core_Block_Template
{

    /**
     *
     */
    protected function _construct()
    {
        if (Mage::getStoreConfig('webwinkelconnect/general/enabled')) {
            $this->setWebwinkelconnectEnabled(1);
        } else {
            $this->setWebwinkelconnectEnabled(0);
        }
     
        parent::_construct();                                   
        $this->setTemplate('magmodules/webwinkelconnect/widget/custom.phtml');
    }

    /**
     * @return mixed
     */
    public function getWebwinkelconnectData()
    {
        return $this->helper('webwinkelconnect')->getTotalScore();
    }        

}