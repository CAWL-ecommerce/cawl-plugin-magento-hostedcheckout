<?php
declare(strict_types=1);

namespace Cawl\HostedCheckout\Plugin\Magento\Payment\Model\Method\Adapter;

use Cawl\PaymentCore\Model\Order\ValidatorPool\DiscrepancyValidator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\Method\Adapter;
use Cawl\HostedCheckout\Model\Config\PaymentActionReplaceHandlerInterface;
use Cawl\HostedCheckout\Model\Data\OrderPaymentContainer;
use Cawl\HostedCheckout\Gateway\Config\Config;
use Cawl\HostedCheckout\Service\CreateHostedCheckoutRequest\Order\ShoppingCartDataBuilder;
use Magento\Sales\Model\Order\Payment;

class ReplacePaymentAction
{
    public const WORLD_LINE_CHEQUE_VACANCES_ONLINE_METHOD = 'worldline_redirect_payment_5403';

    /**
     * @var PaymentActionReplaceHandlerInterface[]
     */
    private $handlers;

    /**
     * @var OrderPaymentContainer
     */
    private $orderPaymentContainer;

    /**
     * @var DiscrepancyValidator
     */
    private $discrepancyValidator;

    public function __construct(
        OrderPaymentContainer $orderPaymentContainer,
        DiscrepancyValidator $discrepancyValidator,
        $handlers = []
    ) {
        $this->handlers = $handlers;
        $this->orderPaymentContainer = $orderPaymentContainer;
        $this->discrepancyValidator = $discrepancyValidator;
    }

    /**
     * Change the payment action value for some WL payments
     *
     * @param Adapter $subject
     * @param string|null $result
     * @return string|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetConfigPaymentAction(Adapter $subject, ?string $result = null): ?string
    {
        if ($subject->getCode() === self::WORLD_LINE_CHEQUE_VACANCES_ONLINE_METHOD ||
            $subject->getCode() === ShoppingCartDataBuilder::WORLD_LINE_MEAL_VAUCHER_METHOD) {
            return Config::AUTHORIZE_CAPTURE;
        }

        if ($this->isOrderWithDiscrepancy($subject)) {
            return Config::AUTHORIZE;
        }

        if (!$payment = $this->orderPaymentContainer->getPayment()) {
            return $result;
        }

        foreach ($this->handlers as $handler) {
            if (!$handler instanceof PaymentActionReplaceHandlerInterface) {
                continue;
            }

            if ($paymentAction = $handler->getPaymentAction($payment)) {
                return $paymentAction;
            }
        }

        return $result;
    }

    /**
     * @param Adapter $subject
     *
     * @return bool
     * @throws LocalizedException
     */
    private function isOrderWithDiscrepancy(Adapter $subject): bool
    {
        $payment = $subject->getInfoInstance();

        if ($payment instanceof Payment) {
            $order = $payment->getOrder();
            $incrementId = $order->getIncrementId();

            return $this->discrepancyValidator->compareAmounts((float)$order->getGrandTotal(), $incrementId);
        }

        return false;
    }
}
