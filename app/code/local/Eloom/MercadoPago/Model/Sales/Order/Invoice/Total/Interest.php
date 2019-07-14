<?php

##eloom.licenca##

class Eloom_MercadoPago_Model_Sales_Order_Invoice_Total_Interest extends Mage_Sales_Model_Order_Invoice_Total_Abstract {

  protected $_code = 'eloom_mercadopago_interest';

  public function collect(Mage_Sales_Model_Order_Invoice $invoice) {
    parent::collect($invoice);
    $order = $invoice->getOrder();
    $baseTotalInterestAmount = $order->getMercadopagoBaseInterestAmount();
    $totalInterestAmount = Mage::app()->getStore()->convertPrice($baseTotalInterestAmount);

    $invoice->setMercadopagoInterestAmount($totalInterestAmount);
    $invoice->setMercadopagoBaseInterestAmount($baseTotalInterestAmount);

    $invoice->setGrandTotal($invoice->getGrandTotal() + $invoice->getMercadopagoInterestAmount());
    $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $invoice->getMercadopagoBaseInterestAmount());

    return $this;
  }

}
