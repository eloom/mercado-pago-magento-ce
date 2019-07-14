<?php

##eloom.licenca##

$installer = $this;
$installer->startSetup();
$conn = $installer->getConnection();

$salesOrderTable = $installer->getTable('sales/order');
if (!$conn->tableColumnExists($salesOrderTable, 'mercadopago_interest_amount')) {
	$conn->addColumn($salesOrderTable, 'mercadopago_interest_amount', 'DECIMAL(10,4) NOT NULL');
}
if (!$conn->tableColumnExists($salesOrderTable, 'mercadopago_base_interest_amount')) {
	$conn->addColumn($salesOrderTable, 'mercadopago_base_interest_amount', 'DECIMAL(10,4) NOT NULL');
}

$quoteTableAddress = $installer->getTable('sales/quote_address');
if (!$conn->tableColumnExists($quoteTableAddress, 'mercadopago_interest_amount')) {
	$conn->addColumn($quoteTableAddress, 'mercadopago_interest_amount', 'DECIMAL(10,4) NOT NULL');
}
if (!$conn->tableColumnExists($quoteTableAddress, 'mercadopago_base_interest_amount')) {
	$conn->addColumn($quoteTableAddress, 'mercadopago_base_interest_amount', 'DECIMAL(10,4) NOT NULL');
}

$invoiceTable = $installer->getTable('sales/invoice');
if (!$conn->tableColumnExists($invoiceTable, 'mercadopago_interest_amount')) {
	$conn->addColumn($invoiceTable, 'mercadopago_interest_amount', 'DECIMAL(10,4) NOT NULL');
}
if (!$conn->tableColumnExists($invoiceTable, 'mercadopago_base_interest_amount')) {
	$conn->addColumn($invoiceTable, 'mercadopago_base_interest_amount', 'DECIMAL(10,4) NOT NULL');
}

$installer->endSetup();