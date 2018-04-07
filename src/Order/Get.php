<?php 

namespace KeithBrink\Walmart\Order;

use KeithBrink\Walmart\WalmartCore;
use Carbon\Carbon;

class Get extends WalmartCore {

    public function all(Carbon $start_date, Carbon $end_date, array $query = [])
    {
        $query = array_merge($query, array(
            'createdStartDate' => $start_date->toIso8601String(),
            'createdEndDate' => $end_date->toIso8601String(),
        ));
        $this->setMethod('GET');
        $this->setEndpoint('v3/ca/orders');
        $this->setGetData($query);
        $this->sendRequest();
        
        $response = $this->getResponse();
        
        return $response;
    }

    public function allReleased(Carbon $start_date, Carbon $end_date)
    {
        $this->setMethod('GET');
        $this->setEndpoint('v3/ca/orders/released');
        $this->setGetData([
            'createdStartDate' => $start_date->toIso8601String(),
            'createdEndDate' => $end_date->toIso8601String(),
        ]);
        $this->sendRequest();

        $response = $this->getResponse();

        return $response;
    }

    public function one($order_id)
    {
        $this->setMethod('GET');
        $this->setEndpoint('v3/ca/orders/'.$order_id);
        $this->sendRequest();

        $response = $this->getResponse();

        return $response;
    }

}