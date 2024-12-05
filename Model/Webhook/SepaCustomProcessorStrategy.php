<?php
declare(strict_types=1);

namespace Cawl\HostedCheckout\Model\Webhook;

use OnlinePayments\Sdk\Domain\WebhooksEvent;
use Cawl\PaymentCore\Api\Data\PaymentProductsDetailsInterface;
use Cawl\PaymentCore\Api\Webhook\CustomProcessorStrategyInterface;
use Cawl\PaymentCore\Api\Webhook\ProcessorInterface;
use Cawl\PaymentCore\Model\Transaction\TransactionStatusInterface;

class SepaCustomProcessorStrategy implements CustomProcessorStrategyInterface
{
    /**
     * @var SepaOrderProcessor
     */
    private $sepaOrderProcessor;

    public function __construct(SepaOrderProcessor $sepaOrderProcessor)
    {
        $this->sepaOrderProcessor = $sepaOrderProcessor;
    }

    public function getProcessor(WebhooksEvent $webhookEvent): ?ProcessorInterface
    {
        if (!$payment = $webhookEvent->getPayment()) {
            return null;
        }

        $sepaOutput = $payment->getPaymentOutput()->getSepaDirectDebitPaymentMethodSpecificOutput();
        $statusCode = $payment->getStatusOutput()->getStatusCode();
        if (!$sepaOutput
            || $statusCode !== TransactionStatusInterface::CAPTURED_CODE
            || PaymentProductsDetailsInterface::SEPA_DIRECT_DEBIT_PRODUCT_ID !== $sepaOutput->getPaymentProductId()
        ) {
            return null;
        }

        return $this->sepaOrderProcessor;
    }
}
