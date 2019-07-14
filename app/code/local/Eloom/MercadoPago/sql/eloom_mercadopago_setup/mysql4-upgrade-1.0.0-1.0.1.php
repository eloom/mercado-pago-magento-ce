<?php

$installer = $this;
$installer->startSetup();
$conn = $installer->getConnection();

if (!$conn->tableColumnExists($this->getTable('sales/order_payment'), 'boleto_cancellation')) {
  $installer->run("ALTER TABLE {$this->getTable('sales/order_payment')} ADD `boleto_cancellation` DATETIME");
}
$installer->endSetup();