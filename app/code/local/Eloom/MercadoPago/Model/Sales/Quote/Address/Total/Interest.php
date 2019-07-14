<?php

##eloom.licenca##

class Eloom_MercadoPago_Model_Sales_Quote_Address_Total_Interest extends Mage_Sales_Model_Quote_Address_Total_Abstract {

  protected $_code = 'eloom_mercadopago_interest';

  public function collect(Mage_Sales_Model_Quote_Address $address) {
    parent::collect($address);

    $this->_setAmount(0);
    $this->_setBaseAmount(0);
    $address->setMercadopagoInterestAmount(0);
    $address->setMercadopagoBaseInterestAmount(0);

    $items = $this->_getAddressItems($address);
    if (!count($items)) {
      return $this;
    }

    $interest = Mage::getSingleton('eloom_mercadopago/interest');
    if ($interest->canApply($address)) {
      $paymentInterest = $interest->getInterest();
			$store = $address->getQuote()->getStore();

			$shippingAmount = $address->getShippingAmount();
      $amount = ($paymentInterest->baseSubtotalWithDiscount + $paymentInterest->baseTax + $shippingAmount);

      $baseTotalInterestAmount = Mage::helper('eloom_mercadopago/math')->calculateInterest($amount, $paymentInterest->getTotalPercent());
			$baseTotalInterestAmount = $store->roundPrice($baseTotalInterestAmount);

      $totalInterestAmount = Mage::helper('directory')->currencyConvert($baseTotalInterestAmount, $paymentInterest->baseCurrencyCode);

			$address->setMercadopagoInterestAmount($totalInterestAmount);
      $address->setMercadopagoBaseInterestAmount($baseTotalInterestAmount);

      $address->setGrandTotal($address->getGrandTotal() + $address->getMercadopagoInterestAmount());
      $address->setBaseGrandTotal($address->getBaseGrandTotal() + $address->getMercadopagoBaseInterestAmount());
    }

    return $this;
  }

  public function fetch(Mage_Sales_Model_Quote_Address $address) {
    $amount = $address->getMercadopagoInterestAmount();
    if ($amount != 0) {
      $address->addTotal(array('code' => $this->getCode(),
          'title' => Mage::helper('eloom_mercadopago')->__('Interest'),
          'value' => $amount
      ));
    }
    return $this;
  }

}
