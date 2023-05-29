<?php
declare(strict_types=1);

namespace Training\SplitOrder\Model;

use Training\SplitOrder\Helper\Data as HelperData;
use Training\SplitOrder\Api\AttributesInterface;

class ExtensionAttributes implements AttributesInterface
{
    private $helperData;
    public function __construct(
        HelperData $helperData
    ) {
        $this->helperData = $helperData;
    }

    public function loadValue($product, $attributeCode)
    {
        $attributes = $product->getExtensionAttributes();
        if ($attributeCode === self::QUANTITY_AND_STOCK_STATUS) {
            return (string) $this->quantityAndStockStatus($attributes);
        }
        return false;
    }

    public function quantityAndStockStatus($attributes)
    {
        if ($this->helperData->getQtyType() === 'qty') {
            return $attributes->getStockItem()->getQty();
        }
        if ($this->helperData->getBackorder() && $attributes->getStockItem()->getQty() < 1) {
            return 'out';
        }
        return ($attributes->getStockItem()->getIsInStock()) ? 'in' : 'out';
    }
}
