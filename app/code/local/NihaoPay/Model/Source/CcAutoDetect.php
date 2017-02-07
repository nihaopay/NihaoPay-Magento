<?php


class Nihaopay_Model_Source_CcAutoDetect
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => false,
                'label' => Mage::helper('nihaopay')->__('Disabled')
            ),
            array(
                'value' => 1,
                'label' => Mage::helper('nihaopay')->__('Show all accepted card types')
            ),
            array(
                'value' => 2,
                'label' => Mage::helper('nihaopay')->__('Show only the detected card type')
            ),
        );
    }
}
