<?php

class Magmodules_Webwinkelconnect_Model_Rating extends Mage_Rating_Model_Rating {
    public function toOptionArray() {
        $rating_options = Mage::getModel('rating/rating')->getCollection();
        $result = [];
        foreach ($rating_options as $option) {
            $result[] = ['value' => $option->getRatingId(), 'label' => $option->getRatingCode()];
        }
        return $result;
    }
}