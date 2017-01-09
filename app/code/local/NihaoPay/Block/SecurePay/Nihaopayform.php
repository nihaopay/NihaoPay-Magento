<?php
require_once 'NihaoPay/Model/Requestor.php';
require_once 'NihaoPay/Model/Error/Base.php';


class Nihaopay_Block_SecurePay_NihaoPayform extends Mage_Core_Block_Abstract
{


	protected function _toHtml()
    {


    	$debug = Mage::getStoreConfig('payment/nihaopay/nihaopay_mode');
		if($debug)
    		$token = Mage::getStoreConfig('payment/nihaopay/nihaopay_test_apikey');
    	else
    		$token = Mage::getStoreConfig('payment/nihaopay/nihaopay_live_apikey');
	
		$sOrderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
		$oOrder = Mage::getModel('sales/order')->loadByIncrementId($sOrderId);
		
		$ipn = Mage::getUrl('nihaopay/securepay/ipn');
		$callback = Mage::getUrl('nihaopay/securepay/callback');
		$methodCode = $oOrder->getPayment()->getMethod();
		$this->log('current method=' . $methodCode);
        $vendor = '';
		if($methodCode == NihaoPay_Model_MethodAbstract::CODE_ALIPAY){
			$vendor = 'alipay';
		}
		else if($methodCode == NihaoPay_Model_MethodAbstract::CODE_UNIONPAY){
			$vendor = 'unionpay';
		}
		else if($methodCode == NihaoPay_Model_MethodAbstract::CODE_WECHATPAY){
			$vendor = 'wechatpay';
		}
		$requestor = new Requestor();
		$requestor->setDebug($debug);
		$ret = $requestor->getSecureForm($token, $vendor ,$oOrder,$ipn,$callback);
		//$this->log('return from nihaopay:' . print_r($ret,true));
		
		
		return $ret;
	}
	

  	
  	protected function log($msg)
    {
        Mage::log("NihaoPay SecurePay form - ".$msg);
    }
  	 
 
  
}