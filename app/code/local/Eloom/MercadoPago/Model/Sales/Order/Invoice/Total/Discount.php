<?php

##eloom.licenca##

class Eloom_MercadoPago_Model_Sales_Order_Invoice_Total_Discount extends Mage_Sales_Model_Order_Invoice_Total_Abstract {

  public function collect(Mage_Sales_Model_Order_Invoice $invoice) {
    parent::collect($invoice);
    $order = $invoice->getOrder();
    $baseTotalDiscountAmount = $order->getMercadopagoBaseDiscountAmount();
    $totalDiscountAmount = Mage::app()->getStore()->convertPrice($baseTotalDiscountAmount);

    $invoice->setMercadopagoDiscountAmount($totalDiscountAmount);
    $invoice->setMercadopagoBaseDiscountAmount($baseTotalDiscountAmount);

    $invoice->setGrandTotal($invoice->getGrandTotal() + $invoice->getMercadopagoDiscountAmount());
    $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $invoice->getMercadopagoBaseDiscountAmount());

    return $this;
  }

}
