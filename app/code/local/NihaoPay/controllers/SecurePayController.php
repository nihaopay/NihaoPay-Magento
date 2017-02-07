<?php

class Nihaopay_SecurepayController extends Mage_Core_Controller_Front_Action 
{
	public function redirectAction() 
	{

        $this->getResponse()->setBody($this->getLayout()->createBlock('nihaopay/securepay_nihaopayform')->toHtml());

		return;
	}

	public function ipnAction() 
	{
		$data = $this->getRequest()->getParams();
		
		$this->log('Get request to IPN');
		$this->log(print_r($data,true));
		
		$this->processPayment($data);
		
	}

	public function callbackAction() 
	{
		$data = $this->getRequest()->getParams();

		$this->log('Get request to callback');
		$this->log(print_r($data,true));

		//$this->processPayment($data);


		if(isset($data['status']) && $data['status']=='success'){
			$this->_redirect('checkout/onepage/success');
			
		}else{
            Mage::helper('checkout')->sendPaymentFailedEmail(
                Mage::getSingleton('checkout/session')->getQuote(),
                $this->__('Unable to place the order.')
            );
            Mage::getSingleton('checkout/session')->addError($this->__('Unable to place the order.'));
            $this->log('place order error');
            $this->_redirect('checkout/cart');
			
		}

	}

	protected function processPayment($data){
	
		if(!isset($data['reference']) || !isset($data['status'])){
			Mage::app()->getResponse()
				->setHeader('HTTP/1.1','503 Service Unavailable')
				->sendResponse();
			exit;
		}

		$order_id = '';
		$refs = explode('at',$data['reference']);
		//first item is order id
		if($refs !=null && is_array($refs)){
			$order_id = $refs[0];		
		}else{
		 	$this->log('reference code invalid:' . $data['reference']);
		 	return;
		}

		$order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
		if (!$order->getId()) {
			Mage::app()->getResponse()
				->setHeader('HTTP/1.1','503 Service Unavailable')
				->sendResponse();
			exit;
		}
		$this->log('Find order id='.$order->getId());
		if($data['status']=='success'){
			$this->successIPN($order,$data);
		
		}else{
			$this->failIPN($order,$data);
		}
		
	}
	
	protected function successIPN($order,$data){
	
		$payment = $order->getPayment();
		$amount = ((int)$data['amount'])/100;
		$amount = number_format((float)$amount, 2, '.', '');
		$payment->setTransactionId($data['id'])
			->setCurrencyCode($order->getOrderCurrencyCode())
			->setPreparedMessage('')
			->setIsTransactionClosed(1)
			->registerCaptureNotification($amount);
		$order->save();

		// notify customer
		$invoice = $payment->getCreatedInvoice();
		if ($invoice && !$order->getEmailSent()) {
			$order->queueNewOrderEmail()->addStatusHistoryComment(
				$this->__('Notified customer about invoice #%s.', $invoice->getIncrementId())
			)
			->setIsCustomerNotified(true)
			->save();
		}	
	}
	
	protected function failIPN($order,$data){
        $payment = $order->getPayment();

        $payment->setTransactionId($data['id'])
            ->setNotificationResult(true)
            ->setIsTransactionClosed(true);
        if (!$order->isCanceled()) {
            $payment->registerPaymentReviewAction(Mage_Sales_Model_Order_Payment::REVIEW_ACTION_DENY, false);
        } else {
            
            $comment = $this->__('Transaction ID: "%s"', $data['id']);
            $order->addStatusHistoryComment($comment, false);
        }

        $order->save();
 	
	}
	
  	
  	protected function log($msg)
    {
        Mage::log("Nihaopay SecurePay controller - ".$msg);
    }
  	

}
