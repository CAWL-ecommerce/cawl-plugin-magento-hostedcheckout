<?php
declare(strict_types=1);

namespace Cawl\HostedCheckout\Model\Config\PaymentAction;

use Magento\Sales\Api\Data\OrderPaymentInterface;
use Cawl\HostedCheckout\Model\Config\PaymentActionReplaceHandlerInterface;
use Cawl\PaymentCore\Api\Data\PaymentProductsDetailsInterface;
use Cawl\PaymentCore\Api\PaymentRepositoryInterface;
use Cawl\PaymentCore\Api\TransactionRepositoryInterface;
use Cawl\PaymentCore\Model\Transaction\TransactionStatusInterface;

class SepaDirectDebitHandler implements PaymentActionReplaceHandlerInterface
{
    private const PAYMENT_ACTION = 'authorize';

    /**
     * @var PaymentRepositoryInterface
     */
    private $paymentRepository;

    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    public function __construct(
        PaymentRepositoryInterface $paymentRepository,
        TransactionRepositoryInterface $transactionRepository
    ) {
        $this->paymentRepository = $paymentRepository;
        $this->transactionRepository = $transactionRepository;
    }

    public function getPaymentAction(OrderPaymentInterface $payment): ?string
    {
        $incrementId = $payment->getOrder()->getIncrementId();

        $worldlinePayment = $this->paymentRepository->get($incrementId);
        $paymentProductId = (int) $worldlinePayment->getPaymentProductId();

        $lastTransaction = $this->transactionRepository->getLastTransaction($incrementId);
        if (!$lastTransaction || $lastTransaction->getStatusCode() !== TransactionStatusInterface::CAPTURE_REQUESTED) {
            return null;
        }

        return PaymentProductsDetailsInterface::SEPA_DIRECT_DEBIT_PRODUCT_ID === $paymentProductId
            ? self::PAYMENT_ACTION : null;
    }
}
