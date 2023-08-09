<?php

class Magmodules_Webwinkelconnect_Helper_Hash extends Mage_Core_Helper_Abstract
{
    private const ALGORITHM = 'sha512';

    public function getHashForDash(array $data): string {
        $helper = Mage::helper('webwinkelconnect');
        if (!$helper->getShopId() || !$helper->getApiKey()) {
            throw Mage::exception(
                'Magmodules_Webwinkelconnect',
                $helper->__(
                    'Could not add order data for Webwinkelkeur dashboard to "thank you page" because of invalid keys'
                )
            );
        }
        return hash_hmac(self::ALGORITHM, http_build_query($data), $helper->getShopId() . ":". $helper->getApiKey());
    }
}