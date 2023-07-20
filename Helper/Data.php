<?php
declare(strict_types=1);

namespace Training\SplitOrder\Helper;

use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    public function isActive($storeId = null):bool
    {
        return (bool) $this->scopeConfig->isSetFlag(
            'training_split_order/module/enabled',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getAttributes($storeId = null)
    {
        return $this->scopeConfig->getValue(
            'training_split_order/options/attributes',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getShippingSplit($storeId = null):bool
    {
        return (bool) $this->scopeConfig->isSetFlag(
            'training_split_order/options/shipping',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
