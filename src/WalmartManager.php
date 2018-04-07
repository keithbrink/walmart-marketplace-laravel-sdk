<?php 

namespace KeithBrink\Walmart;

use KeithBrink\Walmart\Feed\Status as FeedStatus;
use KeithBrink\Walmart\Item\Upload as UploadItem;
use KeithBrink\Walmart\Order\Get as GetOrder;
use KeithBrink\Walmart\Order\Modify as ModifyOrder;

class WalmartManager {

    public function feedStatus() {
        return new FeedStatus;
    }

    public function itemUpload() {
        return new UploadItem;
    }

    public function orderGet() {
        return new GetOrder;
    }

    public function orderModify() {
        return new ModifyOrder;
    }

}