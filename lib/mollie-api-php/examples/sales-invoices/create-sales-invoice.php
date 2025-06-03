<?php

namespace _PhpScoperfb65c95ebc2e;

use Mollie\Api\Types\PaymentTerm;
use Mollie\Api\Types\SalesInvoiceStatus;
/*
 * Create a sales invoice using the Mollie API.
 */
try {
    /*
     * Initialize the Mollie API library with your API key or OAuth access token.
     */
    require "../initialize.php";
    /*
     * Create a sales invoice
     */
    $salesInvoice = $mollie->salesInvoices->create(['currency' => 'EUR', 'status' => \Mollie\Api\Types\SalesInvoiceStatus::DRAFT, 'vatScheme' => 'standard', 'vatMode' => 'inclusive', 'paymentTerm' => \Mollie\Api\Types\PaymentTerm::DAYS_30, 'recipientIdentifier' => 'XXXXX', 'recipient' => ['type' => 'consumer', 'email' => 'darth@vader.deathstar', 'streetAndNumber' => 'Sample Street 12b', 'postalCode' => '2000 AA', 'city' => 'Amsterdam', 'country' => 'NL', 'locale' => 'nl_NL'], 'lines' => [['description' => 'Monthly subscription fee', 'quantity' => 1, 'vatRate' => '21', 'unitPrice' => ['currency' => 'EUR', 'value' => '10.00']]]]);
    echo "<p>New sales invoice created with ID: " . \htmlspecialchars($salesInvoice->id) . "</p>";
} catch (\Mollie\Api\Exceptions\ApiException $e) {
    echo "API call failed: " . \htmlspecialchars($e->getMessage());
}
