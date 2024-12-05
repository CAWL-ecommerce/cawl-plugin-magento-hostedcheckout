<?php
declare(strict_types=1);

namespace Cawl\HostedCheckout\Service\CreateHostedCheckoutRequest;

use Magento\Framework\Event\ManagerInterface;
use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\SepaDirectDebitPaymentMethodSpecificInputBase;
use OnlinePayments\Sdk\Domain\SepaDirectDebitPaymentMethodSpecificInputBaseFactory;
use OnlinePayments\Sdk\Domain\SepaDirectDebitPaymentProduct771SpecificInputBase;
use OnlinePayments\Sdk\Domain\SepaDirectDebitPaymentProduct771SpecificInputBaseFactory;
use Cawl\HostedCheckout\Api\Service\Mandates\MandateDataBuilderInterface;
use Cawl\HostedCheckout\Api\TokenManagerInterface;
use Cawl\HostedCheckout\Gateway\Config\Config;
use Cawl\HostedCheckout\Ui\ConfigProvider;
use Cawl\PaymentCore\Api\Data\PaymentProductsDetailsInterface;

class SepaDirectDebitSIBuilder
{
    public const HC_SEPA_SPECIFIC_INPUT = 'sepa_direct_debit_specific_input';

    /**
     * @var SepaDirectDebitPaymentMethodSpecificInputBaseFactory
     */
    private $debitPaymentMethodSpecificInputBaseFactory;

    /**
     * @var SepaDirectDebitPaymentProduct771SpecificInputBaseFactory
     */
    private $debitPaymentProduct771SpecificInputBaseFactory;

    /**
     * @var MandateDataBuilderInterface
     */
    private $mandateDataBuilder;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var TokenManagerInterface
     */
    private $tokenManager;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    public function __construct(
        SepaDirectDebitPaymentMethodSpecificInputBaseFactory $debitPaymentMethodSpecificInputBaseFactory,
        SepaDirectDebitPaymentProduct771SpecificInputBaseFactory $debitPaymentProduct771SpecificInputBaseFactory,
        MandateDataBuilderInterface $mandateDataBuilder,
        Config $config,
        TokenManagerInterface $tokenManager,
        ManagerInterface $eventManager
    ) {
        $this->debitPaymentMethodSpecificInputBaseFactory = $debitPaymentMethodSpecificInputBaseFactory;
        $this->debitPaymentProduct771SpecificInputBaseFactory = $debitPaymentProduct771SpecificInputBaseFactory;
        $this->mandateDataBuilder = $mandateDataBuilder;
        $this->config = $config;
        $this->tokenManager = $tokenManager;
        $this->eventManager = $eventManager;
    }

    public function build(CartInterface $quote): SepaDirectDebitPaymentMethodSpecificInputBase
    {
        /** @var SepaDirectDebitPaymentMethodSpecificInputBase $debitPaymentMethodSpecificInput */
        $debitPaymentMethodSpecificInput = $this->debitPaymentMethodSpecificInputBaseFactory->create();

        /** @var SepaDirectDebitPaymentProduct771SpecificInputBase $paymentProduct */
        $paymentProduct = $this->debitPaymentProduct771SpecificInputBaseFactory->create();

        if ($token = $this->tokenManager->getToken($quote)) {
            if ($this->tokenManager->isSepaToken($token)) {
                $paymentProduct->setExistingUniqueMandateReference($token->getGatewayToken());
                $debitPaymentMethodSpecificInput->setPaymentProductId(
                    PaymentProductsDetailsInterface::SEPA_DIRECT_DEBIT_PRODUCT_ID
                );
            }
        } else {
            $paymentProduct->setMandate($this->mandateDataBuilder->getMandate($quote, $this->config));
        }

        $debitPaymentMethodSpecificInput->setPaymentProduct771SpecificInput($paymentProduct);

        $args = ['quote' => $quote, self::HC_SEPA_SPECIFIC_INPUT => $debitPaymentMethodSpecificInput];
        $this->eventManager->dispatch(ConfigProvider::HC_CODE . '_sepa_direct_debit_specific_input_builder', $args);

        return $debitPaymentMethodSpecificInput;
    }
}
