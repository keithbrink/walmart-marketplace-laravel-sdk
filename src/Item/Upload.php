<?php 

namespace KeithBrink\Walmart\Item;

use KeithBrink\Walmart\WalmartCore;

class Upload extends WalmartCore {

    public function bulk($xml)
    {
        $this->setMethod('POST');
        $this->setEndpoint('v3/ca/feeds?feedType=item');
        $this->setPostXmlData($xml);
        $this->sendRequest();

        $response = $this->getResponse();

        return $response;
    }

}