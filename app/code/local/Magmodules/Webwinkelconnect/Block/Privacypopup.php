<?php

class Magmodules_Webwinkelconnect_Block_Privacypopup extends Mage_Core_Block_Template
{
    public function isPopupEnabled(): bool {
        return Mage::getStoreConfig('webwinkelconnect/privacy_first_option/privacy_popup');
    }
}