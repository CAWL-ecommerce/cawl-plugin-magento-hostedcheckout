<?php
declare(strict_types=1);

namespace Cawl\HostedCheckout\Model\Config\PaymentAction;

use Magento\Sales\Api\Data\OrderPaymentInterface;
use Cawl\HostedCheckout\Model\Config\PaymentActionReplaceHandlerInterface;
use Cawl\PaymentCore\Api\Data\PaymentProductsDetailsInterface;
use Cawl\PaymentCore\Api\PaymentRepositoryInterface;
use Cawl\PaymentCore\Api\TransactionRepositoryInterface;
use Cawl\PaymentCore\Model\Transaction\TransactionStatusInterface;

class BankTransferHandler implements PaymentActionReplaceHandlerInterface
{
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
        $paymentAction = 'authorize';
        $incrementId = $payment->getOrder()->getIncrementId();

        $worldlinePayment = $this->paymentRepository->get($incrementId);
        $paymentProductId = (int) $worldlinePayment->getPaymentProductId();

        $lastTransaction = $this->transactionRepository->getLastTransaction($incrementId);
        if (!$lastTransaction || $lastTransaction->getStatusCode() !== TransactionStatusInterface::CAPTURE_REQUESTED) {
            $paymentAction = 'authorize_capture';
        }

        return PaymentProductsDetailsInterface::BANK_TRANSFER_PRODUCT_ID === $paymentProductId
            ? $paymentAction : null;
    }
}
