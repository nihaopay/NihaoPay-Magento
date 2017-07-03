<?php
require_once 'Nihaopay/Model/CurlClient.php';
require_once 'Nihaopay/Model/Error/Api.php';


class Requestor
{
	private $debug = false;
	
	public function __construct(){
	
	}

	public function request($token, $payment,$amount){
		$order = $payment->getOrder();
	
		$httpClient = CurlClient::instance();
		$url = "";
		if($this->debug)
			$url = "https://apitest.nihaopay.com/v1.2/transactions/expresspay";
		else
			$url = "https://api.nihaopay.com/v1.2/transactions/expresspay";
		$headers = array("Authorization: Bearer " . $token);
		
		if($this->debug){
			//test account
			$params = array("amount"=>$this->getAmount($order->getGrandTotal(),$order->getOrderCurrencyCode())
					,"card_type"=>"unionpay"
					,"currency"=>$order->getOrderCurrencyCode()
					,"card_number"=>'6221558812340000'
					,"card_exp_month"=>'11'
					,"card_exp_year"=>'2017'
					,"card_cvv"=>'123'
                    ,"client_ip"=>Mage::helper('core/http')->getRemoteAddr()
                    ,"reference"=>$this->getReferenceCode($order->getIncrementId())
					,"description"=>sprintf('#%s, %s', $order->getIncrementId(), $order->getCustomerEmail())
					);
		}else{
			$params = array("amount"=>$this->getAmount($order->getGrandTotal(),$order->getOrderCurrencyCode())
					,"card_type"=>"unionpay"
					,"currency"=>$order->getOrderCurrencyCode()
					,"card_number"=>$payment->getCcNumber()
					,"card_exp_month"=>sprintf('%02d',$payment->getCcExpMonth())
					,"card_exp_year"=>$payment->getCcExpYear()
					,"card_cvv"=>$payment->getCcCid()
                    ,"client_ip"=>Mage::helper('core/http')->getRemoteAddr()
                    ,"reference"=>$this->getReferenceCode($order->getIncrementId())
					,"description"=>sprintf('#%s, %s', $order->getIncrementId(), $order->getCustomerEmail())
					);
		}
		list($rbody, $rcode, $rheaders) = $httpClient->request("post",$url,$headers,$params,false);
		$resp = $this->_interpretResponse($rbody, $rcode, $rheaders,$params);
		
		return $resp;
		
	}
	
	public function refund($token,$payment,$amount){
	
	    $transactionId = $payment->getParentTransactionId();
		$order = $payment->getOrder();

		$httpClient = CurlClient::instance();
		$url = "";
		if($this->debug)
			$url = "https://apitest.nihaopay.com/v1.2/transactions/" . $transactionId . "/refund";
		else
			$url = "https://api.nihaopay.com/v1.2/transactions/" . $transactionId . "/refund";
		$headers = array("Authorization: Bearer " . $token);

		$params = array("amount"=>$this->getAmount($amount,$order->getOrderCurrencyCode())
				,"currency"=>$order->getOrderCurrencyCode()
				,"reason"=>''
				);
					
		list($rbody, $rcode, $rheaders) = $httpClient->request("post",$url,$headers,$params,false);
		$resp = $this->_interpretResponse($rbody, $rcode, $rheaders,$params);
		
		return $resp;
					
					
	}
	private function _interpretResponse($rbody, $rcode, $rheaders,$params)
    {
        try {
            $resp = json_decode($rbody, true);
        } catch (Exception $e) {
            $msg = "Invalid response body from API: $rbody "
              . "(HTTP response code was $rcode)";
            throw new Error_Api($msg, $rcode, $rbody);
        }

        if ($rcode < 200 || $rcode >= 300) {
            $this->handleApiError($rbody, $rcode, $rheaders, $resp,$params);
        }
        return $resp;
    }
    public function handleApiError($rbody, $rcode, $rheaders, $resp,$param)
    {
        if (!is_array($resp) || !isset($resp['error'])) {
            $msg = "Invalid response object from API77777777: $rbody "
              . "(HTTP response code was $rcode)";
        }

        $error = isset($resp['error']) ? $resp['error']:$rcode ;
        $msg = isset($resp['message']) ? $resp['message'] : null;
        $code = isset($error['code']) ? $error['code'] : null;

        throw new Error_Api($msg,$param, $rcode, $rbody, $resp, $rheaders);

    }
    
    public function setDebug($debug){
    	$this->debug = $debug;
    }
    public function getDebug(){
    	return $this->debug ;
    }
	protected function log($msg)
    {
        Mage::log("Requestor - ".$msg);
    }    
    
    public function getSecureForm($token,$vendor,$order,$ipn,$callback){
    
		$httpClient = CurlClient::instance();
		$url = "";
		if($this->debug)
			$url = "https://apitest.nihaopay.com/v1.2/transactions/securepay";
		else
			$url = "https://api.nihaopay.com/v1.2/transactions/securepay";
		$headers = array("Authorization: Bearer " . $token);


		$product='';      
        foreach($order->getAllItems() as $item)
        {
            $product .= $item->getName().'...'; 
            break;          
        }
		$params = array("amount"=>$this->getAmount($order->getGrandTotal(),$order->getOrderCurrencyCode())
				,"vendor"=>$vendor
				,"currency"=>$order->getOrderCurrencyCode()
				,"reference"=>$this->getReferenceCode($order->getIncrementId())
				,"ipn_url"=>$ipn
				,"callback_url"=>$callback
				,"terminal" => $this->ismobile()?'WAP':'ONLINE'
                ,"description"=>$product
                ,"note"=>sprintf('#%s(%s)', $order->getRealOrderId(), $order->getCustomerEmail())
				);
		
		$this->log('send params to '.$url .' with head' . print_r($headers,true));
		$this->log('params:'. print_r($params,true));
		
		list($rbody, $rcode, $rheaders) = $httpClient->request("post",$url,$headers,$params,false);
		$this->log($rbody);

		$resp = $this->_interpretResponse($rbody, $rcode, $rheaders,$params);
		
		return $rbody;
    }

    function getReferenceCode($order_id){

    	$tmstemp = time();
        return $order_id . 'at' . $tmstemp;
    }
    
    function getAmount($amount, $currency = 'USD')
    {
        if ($currency == 'JPY') {
            return (int)$amount;
        }
        else{
            $amount = round($amount, 2) * 100;
            return (int)$amount;
        }
    }

    
    function ismobile() {
		$is_mobile = '0';

		if(preg_match('/(android|up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
			$is_mobile=1;
		}

		if((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml')>0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
			$is_mobile=1;
		}

		$mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4));
		$mobile_agents = array('w3c ','acs-','alav','alca','amoi','andr','audi','avan','benq','bird','blac','blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno','ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-','maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-','newt','noki','oper','palm','pana','pant','phil','play','port','prox','qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar','sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-','tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp','wapr','webc','winw','winw','xda','xda-');

		if(in_array($mobile_ua,$mobile_agents)) {
			$is_mobile=1;
		}

		if (isset($_SERVER['ALL_HTTP'])) {
			if (strpos(strtolower($_SERVER['ALL_HTTP']),'OperaMini')>0) {
				$is_mobile=1;
			}
		}

		if (strpos(strtolower($_SERVER['HTTP_USER_AGENT']),'windows')>0) {
			$is_mobile=0;
		}

		return $is_mobile;
	}
	
    
}
