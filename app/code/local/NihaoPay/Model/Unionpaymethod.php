<?php

class NihaoPay_Model_Unionpaymethod extends NihaoPay_Model_MethodAbstract {
  	protected $_code  = NihaoPay_Model_MethodAbstract::CODE_UNIONPAY;
    protected $_formBlockType = 'nihaopay/securepay_form';
    protected $_infoBlockType = 'payment/info_cc';
    protected $_isInitializeNeeded      = true;
    protected $_canUseForMultishipping  = false;
    //protected $_isGateway               = true;
    protected $_canUseInternal          = false;
    //protected $_canUseCheckout          = true;
    
    



  	
}