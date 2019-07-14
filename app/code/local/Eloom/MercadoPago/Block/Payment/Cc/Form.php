<?php

##eloom.licenca##

class Eloom_MercadoPago_Block_Payment_Cc_Form extends Eloom_MercadoPago_Block_Payment_Form {

  protected $_installmentNoInterest;

  protected function _construct() {
    parent::_construct();
    $this->setTemplate('eloom/mercadopago/payment/cc/form.phtml');
  }

  public function getPercentualDiscount() {
    $value = Mage::helper('eloom_mercadopago/config')->getPaymentCcDiscount();
    if ($value) {
      return str_replace(',', '.', $value);
    }

    return 0.00;
  }

  public function getMinInstallment() {
    $value = Mage::helper('eloom_mercadopago/config')->getPaymentCcMinInstallment();
    if ($value) {
      return str_replace(',', '.', $value);
    }

    return 0.00;
  }

}
