<?php 

namespace app\services;

use app\models\ProductPack;
use app\models\SupplierPack;
use app\models\Supplier;

class ProductService {

    private $found = [];
    private $foundDetail = [];

	public function getAlternativeIds($productId, $resucrsive = false) {
        $exceptSql = "";

        // echo "found :\n";
        // print_r($this->found);

        if ($this->found) {
            $exceptSql = "AND alternative_id not in (".implode(",", $this->found).")";
        }

        $q = "SELECT distinct alternative_id FROM product_alternative WHERE alternative_id <> $productId AND product_id=$productId $exceptSql";
        // echo "$q \n";


        $ids = \Yii::$app->db->createCommand($q)->queryColumn();

        foreach ($ids as $id) {
            $this->foundDetail[$productId] = $id;
        }

        $this->found = array_merge($ids, $this->found);

        // echo "new ids";
        // print_r($ids);
        
        if ($resucrsive) {
            foreach ($ids as $altId) {
                $ids = array_merge($ids, $this->getAlternativeIds($altId, true));
                $this->found = array_merge($ids, $this->found);
            }
        }
        
        return $ids;
    }

}