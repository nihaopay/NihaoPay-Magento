<?php
class NihaoPay_Block_SecurePay_Form extends Mage_Payment_Block_Form
{
  protected function _construct()
  {
    parent::_construct();
    $this->setTemplate('nihaopay/securepay/form.phtml');
  }
}