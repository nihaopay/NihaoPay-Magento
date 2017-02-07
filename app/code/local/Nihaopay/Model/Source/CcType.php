<?php


class Nihaopay_Model_Source_CcType
{
    public function toOptionArray()
    {
        $options =  array();

		$options [] = array(
                   'value' => 'UN',
                   'label' => 'Unionpay'
                );

        $_types = Mage::getConfig()->getNode('global/payment/cc/types')->asArray();

        uasort($_types, array('Mage_Payment_Model_Config', 'compareCcTypes'));

        foreach ($_types as $data)
        {
            if (isset($data['code']) && isset($data['name']))
            {
                $options[] = array(
                   'value' => $data['code'],
                   'label' => $data['name']
                );
            }
        }

        return $options;
    }
}
