<?php
declare(strict_types=1);

namespace Cawl\HostedCheckout\Service\HostedCheckout;

use Magento\Quote\Api\Data\CartInterface;
use OnlinePayments\Sdk\Domain\CreateHostedCheckoutRequest;
use OnlinePayments\Sdk\Domain\CreateHostedCheckoutRequestFactory;
use Cawl\HostedCheckout\Service\CreateHostedCheckoutRequest\CardPaymentMethodSIDBuilder;
use Cawl\HostedCheckout\Service\CreateHostedCheckoutRequest\OrderDataBuilder;
use Cawl\HostedCheckout\Service\CreateHostedCheckoutRequest\RedirectPaymentMethodSpecificInputDataBuilder;
use Cawl\HostedCheckout\Service\CreateHostedCheckoutRequest\SepaDirectDebitSIBuilder;
use Cawl\HostedCheckout\Service\CreateHostedCheckoutRequest\SpecificInputDataBuilder;
use Cawl\HostedCheckout\Service\CreateHostedCheckoutRequest\HostedMobilePaymentMethodSpecificInputDataBuilder;

class CreateHostedCheckoutRequestBuilder
{
    /**
     * @var CreateHostedCheckoutRequestFactory
     */
    private $createHostedCheckoutRequestFactory;

    /**
     * @var OrderDataBuilder
     */
    private $orderDataBuilder;

    /**
     * @var SpecificInputDataBuilder
     */
    private $specificInputDataBuilder;

    /**
     * @var RedirectPaymentMethodSpecificInputDataBuilder
     */
    private $redirectPaymentMethodSpecificInputDataBuilder;

    /**
     * @var CardPaymentMethodSIDBuilder
     */
    private $cardPaymentMethodSIDBuilder;

    /**
     * @var SepaDirectDebitSIBuilder
     */
    private $debitPaymentMethodSpecificInputBuilder;

    /**
     * @var HostedMobilePaymentMethodSpecificInputDataBuilder
     */
    private $hostedMobilePaymentMethodSpecificInputBuilder;

    public function __construct(
        CreateHostedCheckoutRequestFactory $createHostedCheckoutRequestFactory,
        OrderDataBuilder $orderDataBuilder,
        SpecificInputDataBuilder $specificInputDataBuilder,
        RedirectPaymentMethodSpecificInputDataBuilder $redirectPaymentMethodSpecificInputDataBuilder,
        CardPaymentMethodSIDBuilder $cardPaymentMethodSIDBuilder,
        SepaDirectDebitSIBuilder $debitPaymentMethodSpecificInputBuilder,
        HostedMobilePaymentMethodSpecificInputDataBuilder $hostedMobilePaymentMethodSpecificInputBuilder
    ) {
        $this->createHostedCheckoutRequestFactory = $createHostedCheckoutRequestFactory;
        $this->orderDataBuilder = $orderDataBuilder;
        $this->specificInputDataBuilder = $specificInputDataBuilder;
        $this->redirectPaymentMethodSpecificInputDataBuilder = $redirectPaymentMethodSpecificInputDataBuilder;
        $this->cardPaymentMethodSIDBuilder = $cardPaymentMethodSIDBuilder;
        $this->debitPaymentMethodSpecificInputBuilder = $debitPaymentMethodSpecificInputBuilder;
        $this->hostedMobilePaymentMethodSpecificInputBuilder = $hostedMobilePaymentMethodSpecificInputBuilder;
    }

    public function build(CartInterface $quote): CreateHostedCheckoutRequest
    {
        $createHostedCheckoutRequest = $this->createHostedCheckoutRequestFactory->create();
        $createHostedCheckoutRequest->setOrder($this->orderDataBuilder->build($quote));
        $createHostedCheckoutRequest->setHostedCheckoutSpecificInput($this->specificInputDataBuilder->build($quote));
        $createHostedCheckoutRequest->setRedirectPaymentMethodSpecificInput(
            $this->redirectPaymentMethodSpecificInputDataBuilder->build($quote)
        );
        $createHostedCheckoutRequest->setCardPaymentMethodSpecificInput(
            $this->cardPaymentMethodSIDBuilder->build($quote)
        );
        $createHostedCheckoutRequest->setSepaDirectDebitPaymentMethodSpecificInput(
            $this->debitPaymentMethodSpecificInputBuilder->build($quote)
        );

        $createHostedCheckoutRequest->setMobilePaymentMethodSpecificInput(
            $this->hostedMobilePaymentMethodSpecificInputBuilder->build($quote)
        );

        $createHostedCheckoutRequest->getOrder()->getCustomer()->getDevice()->setIpAddress(null);

        return $createHostedCheckoutRequest;
    }
}
