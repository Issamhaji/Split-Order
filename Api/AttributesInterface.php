<?php
namespace Training\SplitOrder\Api;

interface AttributesInterface
{
    const QUANTITY_AND_STOCK_STATUS = 'quantity_and_stock_status';

    public function loadValue($product, $attributeCode);

    public function quantityAndStockStatus($attributes);
}
