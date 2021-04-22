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
 
class Magmodules_Webwinkelconnect_Block_Adminhtml_Webwinkelreviews_Renderer_Experience
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    /**
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $value =  $row->getData($this->getColumn()->getIndex());
        $charLimit = '120';
        if (strlen($value) > $charLimit) {
            $link = ' ... <a href="#" class="magtooltip">' . Mage::helper('webwinkelconnect')->__('(more)');
            $contentSmall = Mage::helper('core/string')->truncate($value, $charLimit, $link);
            $content = $contentSmall . '<span>' . $value . '</span></a>';
            return $content;
        } else {
            return $value;
        }    
    }
    
}