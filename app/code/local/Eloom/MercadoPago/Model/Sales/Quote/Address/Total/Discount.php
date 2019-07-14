<?php

##eloom.licenca##

class Eloom_MercadoPago_Model_Sales_Quote_Address_Total_Discount extends Mage_Sales_Model_Quote_Address_Total_Abstract {

  protected $_code = 'eloom_mercadopago_discount';

  public function collect(Mage_Sales_Model_Quote_Address $address) {
    parent::collect($address);
    $this->_setAmount(0);
    $this->_setBaseAmount(0);
    $address->setMercadopagoDiscountAmount(0);
    $address->setMercadopagoBaseDiscountAmount(0);

    $items = $this->_getAddressItems($address);
    if (!count($items)) {
      return $this;
    }

    $discount = Mage::getSingleton('eloom_mercadopago/discount');
    if ($discount->canApply($address)) {
      $paymentDiscount = $discount->getDiscount();
      
      //$baseTotalDiscountAmount = ((($paymentDiscount->baseSubtotalWithDiscount - abs($address->getMercadopagoBaseCampaignAmount())) + $paymentDiscount->baseTax) * $paymentDiscount->totalPercent) / 100;
      $baseTotalDiscountAmount = (($paymentDiscount->baseSubtotalWithDiscount + $paymentDiscount->baseTax) * $paymentDiscount->totalPercent) / 100;
      $baseTotalDiscountAmount = Mage::helper('eloom_mercadopago')->truncate($baseTotalDiscountAmount, 2);

      $totalDiscountAmount = Mage::helper('directory')->currencyConvert($baseTotalDiscountAmount, $paymentDiscount->baseCurrencyCode);
      $address->setMercadopagoDiscountAmount(-$totalDiscountAmount);
      $address->setMercadopagoBaseDiscountAmount(-$baseTotalDiscountAmount);
      $address->setGrandTotal($address->getGrandTotal() + $address->getMercadopagoDiscountAmount());
      $address->setBaseGrandTotal($address->getBaseGrandTotal() + $address->getMercadopagoBaseDiscountAmount());
    }
    
    return $this;
  }

  public function fetch(Mage_Sales_Model_Quote_Address $address) {
    $amount = $address->getMercadopagoDiscountAmount();
    if ($amount < 0) {
      $address->addTotal(array('code' => $this->getCode(),
          'title' => Mage::helper('eloom_mercadopago')->__('Discount'),
          'value' => $amount
      ));
    }
    return $this;
  }

}
