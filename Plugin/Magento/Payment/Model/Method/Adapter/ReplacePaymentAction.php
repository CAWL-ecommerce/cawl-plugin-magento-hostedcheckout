<?php
declare(strict_types=1);

namespace Cawl\HostedCheckout\Plugin\Magento\Payment\Model\Method\Adapter;

use Magento\Payment\Model\Method\Adapter;
use Cawl\HostedCheckout\Model\Config\PaymentActionReplaceHandlerInterface;
use Cawl\HostedCheckout\Model\Data\OrderPaymentContainer;
use Cawl\HostedCheckout\Gateway\Config\Config;

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

    public function __construct(OrderPaymentContainer $orderPaymentContainer, $handlers = [])
    {
        $this->handlers = $handlers;
        $this->orderPaymentContainer = $orderPaymentContainer;
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
        if ($subject->getCode() === self::WORLD_LINE_CHEQUE_VACANCES_ONLINE_METHOD) {
            return Config::AUTHORIZE_CAPTURE;
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
}
