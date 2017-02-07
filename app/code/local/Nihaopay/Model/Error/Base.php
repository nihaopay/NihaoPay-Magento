<?php

abstract class Error_Base extends Exception
{

	protected $params = null;
	
    public function __construct(
        $message,
        $params,
        $httpStatus = null,
        $httpBody = null,
        $jsonBody = null,
        $httpHeaders = null
    ) {
        parent::__construct($message);
        $this->params =  $params;
        $this->httpStatus = $httpStatus;
        $this->httpBody = $httpBody;
        $this->jsonBody = $jsonBody;
        $this->httpHeaders = $httpHeaders;
        $this->requestId = null;

        if ($httpHeaders && isset($httpHeaders['Request-Id'])) {
            $this->requestId = $httpHeaders['Request-Id'];
        }
    }
    public function getParams()
    {
        return $this->params;
    }

    public function getHttpStatus()
    {
        return $this->httpStatus;
    }

    public function getHttpBody()
    {
        return $this->httpBody;
    }

    public function getJsonBody()
    {
        return $this->jsonBody;
    }

    public function getHttpHeaders()
    {
        return $this->httpHeaders;
    }

    public function getRequestId()
    {
        return $this->requestId;
    }

    public function __toString()
    {
        $id = $this->requestId ? " from API request '{$this->requestId}'": "";
        $message = explode("\n", parent::__toString());
        $message[0] .= $id;
        return implode("\n", $message);
    }
}
