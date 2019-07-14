<?php

##eloom.licenca##

class Eloom_MercadoPago_Model_Sales_Quote_Address_Total_Campaign extends Mage_Sales_Model_Quote_Address_Total_Abstract {

  protected $_code = 'eloom_mercadopago_campaign';

  public function collect(Mage_Sales_Model_Quote_Address $address) {
    
    parent::collect($address);
    $this->_setAmount(0);
    $this->_setBaseAmount(0);
    $address->setMercadopagoCampaignAmount(0);
    $address->setMercadopagoBaseCampaignAmount(0);

    $items = $this->_getAddressItems($address);
    if (!count($items)) {
      return $this;
    }

    $discount = Mage::getSingleton('eloom_mercadopago/campaign');
    if ($discount->canApply($address)) {
      $paymentDiscount = $discount->getDiscount();
      
      $baseTotalDiscountAmount = $paymentDiscount->totalAmount;
      $baseTotalDiscountAmount = Mage::helper('eloom_mercadopago')->truncate($baseTotalDiscountAmount, 2);

      $totalDiscountAmount = Mage::helper('directory')->currencyConvert($baseTotalDiscountAmount, $paymentDiscount->baseCurrencyCode);
      $address->setMercadopagoCampaignAmount(-$totalDiscountAmount);
      $address->setMercadopagoBaseCampaignAmount(-$baseTotalDiscountAmount);
      
      //$address->setSubtotalWithDiscount((float) $address->getSubtotalWithDiscount()-$totalDiscountAmount);
      //$address->setBaseSubtotalWithDiscount((float) $address->getBaseSubtotalWithDiscount()-$baseTotalDiscountAmount);
      
      //$address->setDiscountAmount(-($address->getDiscountAmount()-$totalDiscountAmount));
      //$address->setBaseDiscountAmount(-($address->getBaseDiscountAmount()-$baseTotalDiscountAmount));
      
      $address->setGrandTotal($address->getGrandTotal() + $address->getMercadopagoCampaignAmount());
      $address->setBaseGrandTotal($address->getBaseGrandTotal() + $address->getMercadopagoBaseCampaignAmount());
      
      //$address->save();
    }

    return $this;
  }

  public function fetch(Mage_Sales_Model_Quote_Address $address) {
    $amount = $address->getMercadopagoCampaignAmount();
    if ($amount < 0) {
      $address->addTotal(array('code' => $this->getCode(),
          'title' => Mage::helper('eloom_mercadopago')->__('MercadoPago Campaign Discount'),
          'value' => $amount
      ));
    }
    
    return $this;
  }

}
