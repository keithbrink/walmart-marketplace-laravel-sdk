<?php 

namespace KeithBrink\Walmart\Order;

use KeithBrink\Walmart\WalmartCore;
use Carbon\Carbon;

class Modify extends WalmartCore {

    public function acknowledge($order_id)
    {
        $this->setMethod('POST');
        $this->setEndpoint('v3/ca/orders/'.$order_id.'/acknowledge');
        $this->sendRequest();

        $response = $this->getResponse();

        return $response;
    }

    public function cancelLine($order_id, $line_number = 1)
    {
        $xml = '
            <?xml version="1.0" encoding="UTF-8"?>
            <ns2:orderCancellation xmlns:ns2="http://walmart.com/mp/v3/orders" xmlns:ns3="http://walmart.com/">
                <ns2:orderLines>
                    <ns2:orderLine>
                        <ns2:lineNumber>'.$line_number.'</ns2:lineNumber>
                        <ns2:orderLineStatuses>
                            <ns2:orderLineStatus>
                                <ns2:status>Cancelled</ns2:status>
                                <ns2:cancellationReason>CANCEL_BY_SELLER</ns2:cancellationReason>
                                <ns2:statusQuantity>
                                    <ns2:unitOfMeasurement>EACH</ns2:unitOfMeasurement>
                                    <ns2:amount>1</ns2:amount>
                                </ns2:statusQuantity>
                            </ns2:orderLineStatus>
                        </ns2:orderLineStatuses>
                    </ns2:orderLine>
                </ns2:orderLines>
            </ns2:orderCancellation>
        ';

        $this->setMethod('POST');
        $this->setEndpoint('v3/ca/orders/'.$order_id.'/cancel');
        $this->setPostXmlData($xml);
        $this->sendRequest();

        $response = $this->getResponse();

        return $response;
    }

    public function shipItem($order_id, Carbon $ship_date, $carrier_name, $tracking_number, $tracking_url, $line_number = 1, $method = 'Standard') {
        $xml = '
            <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
            <orderShipment xmlns:ns2="http://walmart.com/mp/v3/orders" xmlns:ns3="http://walmart.com/">
                <orderLines>
                    <orderLine>
                        <lineNumber>'.$line_number.'</lineNumber>
                        <orderLineStatuses>
                            <orderLineStatus>
                                <status>Shipped</status>
                                <statusQuantity>
                                    <unitOfMeasurement>Each</unitOfMeasurement>
                                    <amount>1</amount>
                                </statusQuantity>
                                <trackingInfo>
                                    <shipDateTime>'.$ship_date->toIso8601String().'</shipDateTime>
                                    <carrierName>
                                        <otherCarrier>'.$carrier_name.'</otherCarrier>
                                    </carrierName>
                                    <methodCode>'.$method.'</methodCode>
                                    <trackingNumber>'.$tracking_number.'</trackingNumber>
                                    <trackingURL>'.$tracking_url.'</trackingURL>
                                </trackingInfo>
                            </orderLineStatus>
                        </orderLineStatuses>
                    </orderLine>
                </orderLines>
            </orderShipment>
        ';

        $this->setMethod('POST');
        $this->setEndpoint('v3/ca/orders/'.$order_id.'/shipping');
        $this->setPostXmlData($xml);
        $this->sendRequest();

        $response = $this->getResponse();

        return $response;
    }
}