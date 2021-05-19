<?php

namespace app\models;

use yii\db\ActiveRecord;

class ProductPack extends ActiveRecord
{ 
    public static function tableName()
    {
        return '{{ProductHasPack}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // [['beginner', 'text', 'closed', 'closed_date'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
	    ];
    }

    public static function findByEid($eidType, $eId) {

        $q = "SELECT modelId FROM ExternalId WHERE model='ProductHasPack' AND value=:value AND typeId = :eidType";
        $result = \Yii::$app->db->createCommand($q, ['value'=>$eId, 'eidType'=>$eidType])->queryScalar();
        
        if ($result) {
            return self::findOne(['id'=>$result]);
        }

    }



    public function getLowestPrice() {
        $return = 0;
        $q = "
            SELECT round(min(purchasePrice),2) As purchasePrice FROM " . SupplierHasPack::tableName() . "
            WHERE productHasPackId = {$this->id}
        ";
        $result = Yii::$app->db->createCommand($q)->queryAll();
        if (isset($result[0]['purchasePrice'])) {
            $return = $result[0]['purchasePrice'];
        }
        return $return;
    }

    public function getAveragePrice() {
        $return = 0;
        $q = "
            SELECT round(avg(purchasePrice),2) As purchasePrice FROM " . SupplierHasPack::tableName() . "
            WHERE productHasPackId = {$this->id}
        ";
        $result = Yii::$app->db->createCommand($q)->queryAll();
        if (isset($result[0]['purchasePrice'])) {
            $return = $result[0]['purchasePrice'];
        }
        return $return;
    }

    public function getSupplierPrice() {
        $shp = SupplierPack::findOne(array('productHasPackId' => $this->id, 'supplierId' => $this->supplierId));

        if ($shp && $shp instanceof SupplierHasPack)
            return $shp->purchasePrice; else
            return false;
    }

    public function getPurchasePrice() {
        $return = 0;
        switch ($this->purchasePriceType) {
            case 'lowest':
                $return = $this->getLowestPrice();
                break;

            case 'average':
                $return = $this->getAveragePrice();
                break;

            case 'stock':
                // nothing to do here right now TODO
                break;

            case 'supplier':
                if ($price = $this->getSupplierPrice())
                    $return = $price;

                break;
        }
        return $return;
    }
    
    public function getPrice($pricelistId = 1) {
        return Price::findOne(array('productHasPackId'=>$this->id, 'pricelistId'=>$pricelistId));
    }

    public function getFullPriceSum($pricelistId = 1) {
         $sumasumarum = $this->pack->amount * $this->getPriceValue($pricelistId);
         return $sumasumarum;
    }

    public function getFullPriceSumDph($pricelistId = 1) {
        return round($this->getFullPriceSum($pricelistId) * (1 + (Yii::$app->params['dph'] / 100)), 2);
    }

    // public function getShopPrice() {
    //     if (!Yii::$app->user->isGuest) {
    //         $price = $this->getPrice(Yii::$app->user->model->pricelistId);
    //         if ($price) return $price;
    //         else return $this->getPrice();
    //     } else {
    //         return $this->getPrice();
    //     }
    // }

    // public function getShopFullPriceSumDph() {
    //     if (!Yii::$app->user->isGuest) {
    //         $price = $this->getFullPriceSumDph(Yii::$app->user->model->pricelistId);
    //         if ($price) return $price;
    //             else return $this->getFullPriceSumDph();
    //     } else {
    //         return $this->getFullPriceSumDph();
    //     }
    // }

    // public function getShopFullPriceSum() {
    //     if (!Yii::$app->user->isGuest) {
    //         $price = $this->getFullPriceSum(Yii::$app->user->model->pricelistId);
    //         if ($price) return $price;
    //         else return $this->getFullPriceSum();
    //     } else {
    //         return $this->getFullPriceSum();
    //     }
    // }

    public function getTypeValue($pricelistId = 1) {
        if (!$pricelistId) $pricelistId = 1;
        $priceModel = $this->getPrice($pricelistId);

        if ($priceModel) {
            $typeValue = $priceModel->typeValue;
        } else {
            $pricelist = Pricelist::findByPk($pricelistId);
            $typeValue = $pricelist->typeValue;
        }
        return $typeValue;
    }

    public function getPriceMarza($pricelistId = 1) {
        if (!$pricelistId) $pricelistId = 1;
        $priceModel = $this->getPrice($pricelistId);

        $typeValue = 0;

        if ($priceModel) {
            $typeValue = $priceModel->marza;
        } else {
            $pricelist = Pricelist::findByPk($pricelistId);
            if ($pricelist && $pricelist->staticValue) return $pricelist->staticValue;
        }
        return $typeValue;
    }

    public function getPriceValue($priceListId = 1) {
        
        $result = $this->getPriceUnitValue($priceListId);

        if ($this->pack->bigOnly) {
            $result = $result * $this->pack->large;
        }
        return $result;
    }

    public function getPriceUnitValue($priceListId = 1) {

        if (!$priceListId) $priceListId = 1;
        $priceModel = $this->getPrice($priceListId);

        $marza = 0;
        if ($priceModel) {
            $typeValue = $priceModel->typeValue;
            $marza = $priceModel->marza;
            $type = $priceModel->type;
        } else {
            $pricelist = Pricelist::findByPk($priceListId);
            $typeValue = $pricelist->typeValue;
            $type = $pricelist->type;
            if ($pricelist->staticValue) {
                $marza = $pricelist->staticValue;
            }
        }
        
        switch ($type) {
            case 'koeficient':
                $purchasePrice = $this->getPurchasePrice();
                $k = $typeValue / 100 + 1;
                $result = round($purchasePrice * $k,2) + $marza;
            break;

            case 'static':
                if (!$priceModel) $result = 0; else 
                $result = $priceModel->value;
            break;

            case 'discount':
                if ($priceListId == 1) {
                    if (!$priceModel) $result = 0;
                    $result = $priceModel->value;
                } else {
                    $v = $this->getPriceValue($priceListId);
                    $v -= $typeValue;
                    $result = round($v,2);
                }
            break;

            case 'purchaseplus':
                $purchasePrice = $this->getPurchasePrice();
                $v = $purchasePrice + $typeValue + $marza;
                $result = round($v,2);
            break;
        }

        return $result;
        
    }

    public function getPriceValueDph($pricelistId) {
        $priceValue = $this->getPriceValue($pricelistId);
        return round($priceValue * (1 + (Yii::$app->params['dph'] / 100)), 2);
    }

    public function getPack() {
        return $this->hasOne(Pack::className(), ['id' => 'packId']);
    }


}