<?php

namespace Digitick\Common\TransferFile\Facade;

use Digitick\Common\Exception\InvalidArgumentException;
use Digitick\Common\PaymentInformation;
use Digitick\Common\TransferInformation\CustomerCreditTransferInformation;

/**
 * Class CustomerCreditFacade
 */
class CustomerCreditFacade extends BaseCustomerTransferFileFacade
{

    /**
     * @param string $paymentName
     * @param array $paymentInformation
     *
     * @throws InvalidArgumentException
     *
     * @return PaymentInformation
     */
    public function addPaymentInfo($paymentName, array $paymentInformation)
    {
        if (isset($this->payments[$paymentName])) {
            throw new InvalidArgumentException(sprintf('Payment with the name %s already exists', $paymentName));
        }

        $originAgentBic = isset($paymentInformation['debtorAgentBIC']) ? $paymentInformation['debtorAgentBIC'] : null;
        $payment = new PaymentInformation(
            $paymentInformation['id'],
            $paymentInformation['debtorAccountIBAN'],
            $originAgentBic,
            $paymentInformation['debtorName']
        );
        $payment->setDueDate($this->createDueDateFromPaymentInformation($paymentInformation));

        $this->payments[$paymentName] = $payment;

        return $payment;
    }

    /**
     * @param string $paymentName
     * @param array $transferInformation
     *
     * @throws InvalidArgumentException
     *
     * @return CustomerCreditTransferInformation
     */
    public function addTransfer($paymentName, array $transferInformation)
    {
        if (!isset($this->payments[$paymentName])) {
            throw new InvalidArgumentException(sprintf(
                'Payment with the name %s does not exists, create one first with addPaymentInfo',
                $paymentName
            ));
        }

        $transfer = new CustomerCreditTransferInformation(
            $transferInformation['amount'],
            $transferInformation['creditorIban'],
            $transferInformation['creditorName']
        );

        if (isset($transferInformation['creditorBic'])) {
            $transfer->setBic($transferInformation['creditorBic']);
        }

        if (isset($transferInformation['creditorReference'])) {
            $transfer->setCreditorReference($transferInformation['creditorReference']);
        } else {
            $transfer->setRemittanceInformation($transferInformation['remittanceInformation']);
        }

        if (isset($transferInformation['endToEndId'])) {
            $transfer->setEndToEndIdentification($transferInformation['endToEndId']);
        } else {
            $transfer->setEndToEndIdentification(
                $this->payments[$paymentName]->getId() . count($this->payments[$paymentName]->getTransfers())
            );
        }

        if (isset($transferInformation['instructionId'])) {
            $transfer->setInstructionId($transferInformation['instructionId']);
        }

        if (isset($transferInformation['currency'])) {
            $transfer->setCurrency($transferInformation['currency']);
        }

        if (isset($transferInformation['postalAddress'], $transferInformation['cityZip'])) {
            $transfer->setPostalAddress(array($transferInformation['postalAddress'],$transferInformation['cityZip']));
        }

        if (isset($transferInformation['instructionInformation'])) {
            $transfer->setInstructionInformation($transferInformation['instructionInformation']);
        }

        if (isset($transferInformation['country'])) {
            $transfer->setCountry($transferInformation['country']);
        }

        $this->payments[$paymentName]->addTransfer($transfer);

        return $transfer;
    }

}
