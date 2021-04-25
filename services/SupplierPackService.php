<?php 

namespace app\services;

use app\models\ProductPack;
use app\models\SupplierPack;
use app\models\Supplier;

class SupplierPackService {

	public function updatePackSupplierPrice($pack, $supplier, $price) {
		if (!($pack instanceof ProductPack)) {
			$pack = ProductPack::findOne(['id'=>$pack]);
		}

		if (!($supplier instanceof Supplier)) {
			$supplier = Supplier::findOne(['id'=>$supplier]);
		}

		$supplierPack = SupplierPack::findOne([
			'supplierId'=>$supplier->id,
			'productHasPackId'=>$pack->id
		]);


		if (!$supplierPack) {
			$supplierPack = new SupplierPack();
			$supplierPack->supplierId = $supplier->id;
			$supplierPack->productHasPackId = $pack->id;
		}


		$supplierPack->purchasePrice = $price;

		return $supplierPack->save(false);
	}

	public function updatePackSupplier($pack, $supplier) {
		if (!($pack instanceof ProductPack)) {
			$pack = ProductPack::findOne(['id'=>$pack]);
		}

		if (!($supplier instanceof Supplier)) {
			$supplier = Supplier::findOne(['id'=>$supplier]);
		}

		$pack->supplierId = $supplier->id;
		return $pack->save(false);

	}

}