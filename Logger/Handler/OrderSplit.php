<?php
declare(strict_types=1);

namespace Training\SplitOrder\Logger\Handler;


use Magento\Framework\Logger\Handler\Base as BaseHandler;
use Monolog\Logger as MonologLogger;

/**
 * Class OrderSplit
 */
class OrderSplit extends BaseHandler
{
    /**
     * Logging level
     *
     * @var int
     */
    protected $loggerType = MonologLogger::INFO;

    /**
     * File name
     *
     * @var string
     */
    protected $fileName = '/var/log/my_custom_logger/order.log';
}
