<?php

declare(strict_types=1);

namespace UnzerPayment6\Components\PaymentHandler\Traits;

use Shopware\Core\Checkout\Payment\Cart\AsyncPaymentTransactionStruct;
use Shopware\Core\Checkout\Payment\PaymentException;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Throwable;
use UnzerPayment6\Components\PaymentHandler\Exception\UnzerPaymentProcessException;
use UnzerSDK\Exceptions\UnzerApiException;
use UnzerSDK\Resources\PaymentTypes\BasePaymentType;

trait IsBasicPaymentMethod
{
    use CanCharge;
    abstract protected function getUnzerPaymentTypeObject(): ?BasePaymentType;

    public function pay(
        AsyncPaymentTransactionStruct $transaction,
        RequestDataBag                $dataBag,
        SalesChannelContext           $salesChannelContext
    ): RedirectResponse
    {
        parent::pay($transaction, $dataBag, $salesChannelContext);

        try {
            if (empty($this->paymentType) && ($unzerPaymentType = $this->getUnzerPaymentTypeObject()) !== null) {
                $this->paymentType = $this->unzerClient->createPaymentType($unzerPaymentType);
            }
            $returnUrl = $this->charge($transaction->getReturnUrl());

            return new RedirectResponse($returnUrl);
        } catch (UnzerApiException $apiException) {
            $this->logger->error(
                sprintf('Caught an API exception in %s of %s', __METHOD__, __CLASS__),
                [
                    'dataBag' => $dataBag,
                    'transaction' => $transaction,
                    'exception' => $apiException,
                ]
            );

            $this->executeFailTransition(
                $transaction->getOrderTransaction()->getId(),
                $salesChannelContext->getContext()
            );

            throw new UnzerPaymentProcessException($transaction->getOrder()->getId(), $transaction->getOrderTransaction()->getId(), $apiException);
        } catch (Throwable $exception) {
            $this->logger->error(
                sprintf('Caught a generic exception in %s of %s', __METHOD__, __CLASS__),
                [
                    'dataBag' => $dataBag,
                    'transaction' => $transaction,
                    'exception' => $exception,
                ]
            );
            throw PaymentException::asyncProcessInterrupted($transaction->getOrderTransaction()->getId(), $exception->getMessage());
        }
    }
}
