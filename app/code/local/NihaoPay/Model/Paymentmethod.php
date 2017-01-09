<?php
require_once 'NihaoPay/Model/Requestor.php';
require_once 'NihaoPay/Model/Error/Base.php';

class NihaoPay_Model_Paymentmethod extends NihaoPay_Model_MethodAbstract {
  	protected $_code  = 'nihaopay';
    protected $_isInitializeNeeded      = false;
    protected $_canUseForMultishipping  = true;
    protected $_isGateway               = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = true;
    protected $_canRefund               = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid                 = true;
    protected $_canCancelInvoice        = true;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
    protected $_formBlockType = 'payment/form_cc';
    protected $_infoBlockType = 'payment/info_cc';
    protected $_canSaveCc     = false;
  	const TEST = 'test';
  	const LIVE = 'live';
    
    
    public function capture(Varien_Object $payment, $amount)
    {
        parent::capture($payment, $amount);

    	$debug = Mage::getStoreConfig('payment/nihaopay/nihaopay_mode');
		if($debug)
    		$token = Mage::getStoreConfig('payment/nihaopay/nihaopay_test_apikey');
    	else
    		$token = Mage::getStoreConfig('payment/nihaopay/nihaopay_live_apikey');
    	
    	$this->log('debug mode = ' . $debug . PHP_EOL . 'api key=' .$token);
    	
        $order = $payment->getOrder();
        try {

			$requestor = new Requestor();
			$requestor->setDebug($debug);
			$ret = $requestor->request($token, $payment, $amount);
			$this->log('return from nihaopay:' . print_r($ret,true));
            if(isset($ret['id'])){
            	$payment
					->setTransactionId($ret['id'])
					->setIsTransactionClosed(1);
            }
        } catch (Exception $e) {
        	$this->log($e->getMessage());
            //$this->_logger->info('Nihaopay exception = ' . $e->getMessage() );
            if($e instanceof Error_Base ){
            	$this->log('NihaoPay param = ' . print_r($e->getParams(),true) . $e->getHttpStatus() );
            	$this->log('NihaoPay response = ' . $e->getHttpBody() );
            }
        	Mage::throwException($e->getMessage());
        }

    	return $this;
  	}
  	
	public function getOrderPlaceRedirectUrl()
	{
		return '';
	}  
}