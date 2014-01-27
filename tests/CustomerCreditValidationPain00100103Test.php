<?php
/**
 * SEPA file generator.
 *
 * @copyright © Digitick <www.digitick.net> 2012-2013
 * @copyright © Blage <www.blage.net> 2013
 * @license GNU Lesser General Public License v3.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Lesser Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Tests;

use Digitick\Sepa\DomBuilder\CustomerCreditTransferDomBuilder;
use Digitick\Sepa\GroupHeader;
use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\TransferFile\CustomerCreditTransferFile;
use Digitick\Sepa\TransferInformation\CustomerCreditTransferInformation;

/**
 * Various schema validation tests.
 */
class CustomerCreditValidationPain00100103Test extends \PHPUnit_Framework_TestCase
{
    protected $schema;

    /**
     * @var \DOMDocument
     */
    protected $dom;

    protected function setUp()
    {
        $this->schema = __DIR__ . "/pain.001.001.03.xsd";
        $this->dom = new \DOMDocument('1.0', 'UTF-8');
    }

    /**
     * Sanity check: test reference file with XSD.
     */
    public function testSanity()
    {
        $this->dom->load(__DIR__ . '/pain.001.001.03.xml');
        $validated = $this->dom->schemaValidate($this->schema);
        $this->assertTrue($validated);
    }

    /**
     * Test a transfer file with one payment and one transaction.
     */
    public function testSinglePaymentSingleTransWithInitiatingPartyId()
    {
        $groupHeader = new GroupHeader('transferID', 'Me');
        $groupHeader->setInitiatingPartyId('XXXXXXXXXX');
        $sepaFile = new CustomerCreditTransferFile($groupHeader);

        $transfer = new CustomerCreditTransferInformation('0.02', 'FI1350001540000056', 'Their Corp');
        $transfer->setBic('OKOYFIHH');
        $transfer->setRemittanceInformation('Transaction Description');
        $transfer->setEndToEndIdentification(uniqid());

        $payment = new PaymentInformation('Payment Info ID', 'FR1420041010050500013M02606', 'PSSTFRPPMON', 'My Corp');
        $payment->setValidPaymentMethods(array('TRANSFER'));
        $payment->setPaymentMethod('TRANSFER');
        $payment->addTransfer($transfer);

        $sepaFile->addPaymentInformation($payment);

        $domBuilder = new CustomerCreditTransferDomBuilder('pain.001.001.03');
        $sepaFile->accept($domBuilder);
        $xml = $domBuilder->asXml();
        $this->dom->loadXML($xml);

        $validated = $this->dom->schemaValidate($this->schema);
        $this->assertTrue($validated);
    }
}
