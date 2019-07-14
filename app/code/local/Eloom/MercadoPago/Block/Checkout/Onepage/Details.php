<?php

##eloom.licenca##

class Eloom_MercadoPago_Block_Checkout_Onepage_Details extends Mage_Core_Block_Template {

  protected $additionalData;

  protected function _construct() {
    parent::_construct();
    if(!$this->isBoleto() && !$this->isCc()) {
      return;
    }
    $this->setTemplate('eloom/mercadopago/checkout/onepage/success-details.phtml');

    $info = $this->getPayment();
    $this->additionalData = json_decode($info->getAdditionalData());
  }

  public function isBoleto() {
    $method = $this->getPayment()->getMethodInstance()->getCode();
    if ($method == Eloom_MercadoPago_Model_Method_Boleto::PAYMENT_METHOD_BOLETO_CODE) {
      return true;
    }

    return false;
  }

  public function getBoletoLink() {
    if (isset($this->additionalData->paymentLink)) {
      return $this->additionalData->paymentLink;
    }

    return null;
  }

  public function getBoletoDateOfExpiration() {
    if (isset($this->additionalData->dateOfExpiration)) {
      return $this->additionalData->dateOfExpiration;
    }

    return null;
  }

  public function getBoletoBarcode() {
    if (isset($this->additionalData->barcode)) {
      return $this->additionalData->barcode;
    }

    return null;
  }

  public function isCc() {
    $method = $this->getPayment()->getMethodInstance()->getCode();
    if ($method == Eloom_MercadoPago_Model_Method_Cc::PAYMENT_METHOD_CC_CODE) {
      return true;
    }

    return false;
  }

  public function getPayment() {
    return Mage::helper('eloom_mercadopago')->getPayment();
  }

}
