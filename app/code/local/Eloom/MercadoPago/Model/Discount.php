<?php

##eloom.licenca##

class Eloom_MercadoPago_Model_Discount extends Mage_Core_Model_Abstract {

  public function canApply($address) {
    $data = Mage::app()->getRequest()->getPost('payment', array());
    if (!count($data) || !isset($data['mp_cc_installments']) || Mage::helper('eloom_mercadopago/config')->isAllowCampaigns()) {
      return false;
    }

    $currentPaymentMethod = null;
    $installments = $data['mp_cc_installments'];

    $sessionQuote = Mage::getSingleton('checkout/session')->getQuote();
    if ($sessionQuote->getPayment() != null && $sessionQuote->getPayment()->hasMethodInstance()) {
      $currentPaymentMethod = $sessionQuote->getPayment()->getMethodInstance()->getCode();
    } elseif (isset($data['method'])) {
      $currentPaymentMethod = $data['method'];
    }

    if ($currentPaymentMethod == 'eloom_mercadopago_cc' && $installments != null && $installments == 1) {
      return true;
    }

    return false;
  }

  public function getDiscount() {
    $discount = str_replace(',', '.', Mage::helper('eloom_mercadopago/config')->getPaymentCcDiscount());
    $baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
    $baseSubtotalWithDiscount = 0;
    $baseTax = 0;

    $sessionQuote = Mage::getSingleton('checkout/session')->getQuote();
    if ($sessionQuote->isVirtual()) {
      $address = $sessionQuote->getBillingAddress();
    } else {
      $address = $sessionQuote->getShippingAddress();
    }
    if ($address) {
      $baseSubtotalWithDiscount = $address->getBaseSubtotalWithDiscount();
      $baseTax = $address->getBaseTaxAmount();
    }

    return Eloom_MercadoPago_Discount::getInstance($baseCurrencyCode, $discount, $baseSubtotalWithDiscount, $baseTax);
  }

  public function getModuleDiscount($order) {
    return $order->getMercadopagoDiscountAmount();
  }

  public function getModuleBaseDiscount($order) {
    return $order->getMercadopagoBaseDiscountAmount();
  }

  public function getModuleDiscountCode() {
    return 'discount_eloom_mercadopago';
  }

}
