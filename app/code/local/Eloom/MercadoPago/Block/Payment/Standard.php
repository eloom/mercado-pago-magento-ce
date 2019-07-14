<?php

##eloom.licenca##

class Eloom_MercadoPago_Block_Payment_Standard extends Mage_Payment_Block_Form {

  protected function _construct() {
    $this->setTemplate('eloom/mercadopago/payment/standard.phtml');
    parent::_construct();
  }

  protected function _prepareLayout() {
    return parent::_prepareLayout();
  }

  public function listJsonErrors() {
    return Mage::helper('core')->jsonEncode(Eloom_MercadoPago_Errors::listAll());
  }

  public function getPublicKey() {
    return Mage::helper('eloom_mercadopago/config')->getPublicKey();
  }
  
  public function getAccessToken() {
    return Mage::helper('eloom_mercadopago/config')->getAccessToken();
  }
  
  public function getCustomerTaxvat() {
      return Mage::getSingleton('checkout/session')->getQuote()->getCustomerTaxvat();
  }
  
  public function getCustomerEmail() {
      return Mage::getSingleton('checkout/session')->getQuote()->getCustomerEmail();
  }

}
