<?php

##eloom.licenca##

class Eloom_MercadoPago_Model_Sales_Order_Invoice_Total_Campaign extends Mage_Sales_Model_Order_Invoice_Total_Abstract {

  public function collect(Mage_Sales_Model_Order_Invoice $invoice) {
    parent::collect($invoice);
    $order = $invoice->getOrder();
    $baseTotalDiscountAmount = $order->getMercadopagoBaseCampaignAmount();
    $totalDiscountAmount = Mage::app()->getStore()->convertPrice($baseTotalDiscountAmount);

    $invoice->setMercadopagoCampaignAmount($totalDiscountAmount);
    $invoice->setMercadopagoBaseCampaignAmount($baseTotalDiscountAmount);

    $invoice->setGrandTotal($invoice->getGrandTotal() + $invoice->getMercadopagoCampaignAmount());
    $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $invoice->getMercadopagoBaseCampaignAmount());

    return $this;
  }

}
