<?php
declare(strict_types=1);

namespace Cawl\HostedCheckout\Model\Config\PaymentAction;

use Magento\Sales\Api\Data\OrderPaymentInterface;
use Cawl\HostedCheckout\Model\Config\PaymentActionReplaceHandlerInterface;
use Cawl\PaymentCore\Api\Data\PaymentProductsDetailsInterface;
use Cawl\PaymentCore\Api\PaymentRepositoryInterface;

class IntersolveHandler implements PaymentActionReplaceHandlerInterface
{
    private const PAYMENT_ACTION = 'authorize_capture';

    /**
     * @var PaymentRepositoryInterface
     */
    private $paymentRepository;

    public function __construct(PaymentRepositoryInterface $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
    }

    public function getPaymentAction(OrderPaymentInterface $payment): ?string
    {
        $incrementId = $payment->getOrder()->getIncrementId();

        $worldlinePayment = $this->paymentRepository->get($incrementId);
        $paymentProductId = (int) $worldlinePayment->getPaymentProductId();

        return PaymentProductsDetailsInterface::INTERSOLVE_PRODUCT_ID === $paymentProductId
            ? self::PAYMENT_ACTION : null;
    }
}
