<?php

class Nihaopay_Model_Source_Mode
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Nihaopay_Model_Paymentmethod::TEST,
                'label' => Mage::helper('nihaopay')->__('Test')
            ),
            array(
                'value' => Nihaopay_Model_Paymentmethod::LIVE,
                'label' => Mage::helper('nihaopay')->__('Live')
            ),
        );
    }
}
