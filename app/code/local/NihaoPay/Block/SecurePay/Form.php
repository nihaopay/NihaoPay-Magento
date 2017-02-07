<?php
class Nihaopay_Block_Securepay_Form extends Mage_Payment_Block_Form
{
  protected function _construct()
  {
    parent::_construct();
    $this->setTemplate('nihaopay/securepay/form.phtml');
  }
}