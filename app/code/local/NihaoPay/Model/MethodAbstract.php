<?php
require_once 'Nihaopay/Model/Requestor.php';
require_once 'Nihaopay/Model/Error/Base.php';

class Nihaopay_Model_MethodAbstract extends Mage_Payment_Model_Method_Abstract {

    protected $_canRefund               = true;
    protected $_canRefundInvoicePartial = true;
	const CODE_CCPAY = 'nihaopay'; 
	const CODE_UNIONPAY = 'nihaopay_unionpay'; 
	const CODE_ALIPAY = 'nihaopay_alipay'; 
	const CODE_WECHATPAY = 'nihaopay_wechatpay'; 
	
	protected $_store ;
	
  	protected function log($msg)
    { 
        Mage::log("NihaoPay Payments - " .$this->_code .' - ' .$msg); 
    }
    /**
     * Instantiate state and set it to state object
     * @param string $paymentAction
     * @param Varien_Object
     */
    public function initialize($paymentAction, $stateObject)
    {
        $state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
        $stateObject->setState($state);
        $stateObject->setStatus('pending_payment');
        $stateObject->setIsNotified(false);
        if ($this->_store = $this->getStore()) {
            $this->_store = is_object($this->_store) ? $this->_store->getId() : $this->_store;
        }
    }
        
	public function getOrderPlaceRedirectUrl()
	{
		return Mage::getUrl('nihaopay/securepay/redirect', array('_secure' => true));
	}  	


	public function getTitle(){
		
		
		
		if($this->_code == self::CODE_ALIPAY){
			return Mage::getStoreConfig('payment/nihaopay/alipay_title',$this->_store);
		}
		else if($this->_code == self::CODE_UNIONPAY){
			return Mage::getStoreConfig('payment/nihaopay/unionpay_title',$this->_store);
		}
		else if($this->_code == self::CODE_WECHATPAY){
			return Mage::getStoreConfig('payment/nihaopay/wechatpay_title',$this->_store);
		}
		else if($this->_code == self::CODE_CCPAY){
			return Mage::getStoreConfig('payment/nihaopay/title',$this->_store);
		}else
			return parent::getTitle();
		
	}
    
    public function isAvailable($quote = null)
    {
    
        if (parent::isAvailable($quote) ) {
        
        	if($this->_code == self::CODE_ALIPAY){
        		return Mage::getStoreConfig('payment/nihaopay/alipay_active',$this->_store);
        	}
        	else if($this->_code == self::CODE_UNIONPAY){
        		return Mage::getStoreConfig('payment/nihaopay/unionpay_active',$this->_store);
        	}
        	else if($this->_code == self::CODE_WECHATPAY){
        		return Mage::getStoreConfig('payment/nihaopay/wechatpay_active',$this->_store);
			}
        	else if($this->_code == self::CODE_CCPAY){
        		return Mage::getStoreConfig('payment/nihaopay/active',$this->_store);
			}else
				return false;
        }
        return false;
    }

  	
  	public function refund(Varien_Object $payment, $amount)
    {
        parent::refund($payment, $amount);
        
    	$debug = Mage::getStoreConfig('payment/nihaopay/nihaopay_mode');
		if($debug)
    		$token = Mage::getStoreConfig('payment/nihaopay/nihaopay_test_apikey');
    	else
    		$token = Mage::getStoreConfig('payment/nihaopay/nihaopay_live_apikey');
    	
    	
        $transactionId = $payment->getParentTransactionId();

    	$this->log('debug mode = ' . $debug . PHP_EOL . 'api key=' .$token . 'trasactionId=' . PHP_EOL .  $transactionId) ;
		

        try {
            $requestor = new Requestor();
			$requestor->setDebug($debug);
			$ret = $requestor->refund($token,$payment,$amount);

        } catch (Exception $e) {
            $this->log('exception = ' . $e->getMessage());
            //$this->_logger->info(__('Payment refunding error.'));
            Mage::throwException( $e->getMessage());
        }

        $payment
            ->setTransactionId($transactionId . '-' . Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND)
            ->setParentTransactionId($transactionId)
            ->setIsTransactionClosed(1)
            ->setShouldCloseParentTransaction(1);

        return $this;

    }
  	
}
