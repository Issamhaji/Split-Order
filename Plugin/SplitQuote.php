<?php
declare(strict_types=1);

namespace Training\SplitOrder\Plugin;

use Magento\Quote\Model\QuoteManagement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Framework\Event\ManagerInterface;
use Training\SplitOrder\Api\QuoteInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;

class SplitQuote
{
    /**
     * @var PsrLoggerInterface
     */
    private $logger;
    private $quoteRepository;
    private $quoteFactory;
    private $eventManager;
    private $quote;


    public function __construct(
        PsrLoggerInterface $logger,
        CartRepositoryInterface $quoteRepository,
        QuoteFactory $quoteFactory,
        ManagerInterface $eventManager,
        QuoteInterface $quote
    ) {
        $this->logger = $logger;
        $this->quoteRepository = $quoteRepository;
        $this->quoteFactory = $quoteFactory;
        $this->eventManager = $eventManager;
        $this->quote = $quote;
    }


    public function aroundPlaceOrder(QuoteManagement $subject, callable $proceed, $cartId, $payment = null)
    {
        $currentQuote = $this->quoteRepository->getActive($cartId);

        // Separate all items in quote into new quotes.
        $quotes = $this->quote->normalizeQuotes($currentQuote);
        if (empty($quotes)) {
            $this->logger->info('No quotes found for splitting.');
            return $result = array_values([($proceed($cartId, $payment))]);
        }
        // Collect list of data addresses.
        $addresses = $this->quote->collectAddressesData($currentQuote);

        $orders = [];
        $orderIds = [];
        foreach ($quotes as $items) {
            $split = $this->quoteFactory->create();

            // Set all customer definition data.
            $this->quote->setCustomerData($currentQuote, $split);
            $this->toSaveQuote($split);

            // Map quote items.
            foreach ($items as $item) {
                // Add item by item.
                $item->setId(null);
                $split->addItem($item);
            }
            $this->quote->populateQuote($quotes, $split, $items, $addresses, $payment);

            // Dispatch event as Magento standard once per each quote split.
            $this->eventManager->dispatch(
                'checkout_submit_before',
                ['quote' => $split]
            );

            $this->toSaveQuote($split);
            $order = $subject->submit($split);

            $orders[] = $order;
            $orderIds[$order->getId()] = $order->getIncrementId();

            if (null == $order) {
                throw new LocalizedException(__('Please try to place the order again.'));
            }
            $this->logger->info('Order ID: ' . $order->getId());
        }
        $currentQuote->setIsActive(false);
        $this->toSaveQuote($currentQuote);

        $this->quote->defineSessions($split, $order, $orderIds);

        $this->eventManager->dispatch(
            'checkout_submit_all_after',
            ['orders' => $orders, 'quote' => $currentQuote]
        );

        $this->logger->info('Order are splited successfully');
    }

    private function toSaveQuote($quote)
    {
        $this->quoteRepository->save($quote);

        return $this;
    }
}
