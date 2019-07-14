<?php

##eloom.licenca##

class Eloom_MercadoPago_Model_Interest extends Mage_Core_Model_Abstract {

  public function canApply($address) {
    $data = Mage::app()->getRequest()->getPost('payment', array());
    if (!count($data) || !isset($data['mp_cc_installments'])) {
      return false;
    }

    $currentPaymentMethod = null;

    $sessionQuote = Mage::getSingleton('checkout/session')->getQuote();
    if ($sessionQuote->getPayment() != null && $sessionQuote->getPayment()->hasMethodInstance()) {
      $currentPaymentMethod = $sessionQuote->getPayment()->getMethodInstance()->getCode();
    } elseif (isset($data['method'])) {
      $currentPaymentMethod = $data['method'];
    }

    if ($currentPaymentMethod == 'eloom_mercadopago_cc') {
			$installments = $data['mp_cc_installments'];

			$interest = false;
			$arrayex = explode('-', $data['mp_cc_installments']);
			if (isset($arrayex[0]) && isset($arrayex[1])) {
				$installments = $arrayex[0];
				$interest = $arrayex[2];
			}

			if ($installments != null && $interest > 0) {
				return true;
			}
    }

    return false;
  }

  public function getInterest() {
		$data = Mage::app()->getRequest()->getPost('payment', array());
		$installments = 1;
    $interest = 0;
		$arrayex = explode('-', $data['mp_cc_installments']);
		if (isset($arrayex[0])) {
			$installments = $arrayex[0];
			$interest = $arrayex[2];
		}

    $baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
		$baseSubtotalWithDiscount = 0;
    $baseTax = 0;

    $quote = Mage::getSingleton('checkout/session')->getQuote();
    if ($quote->isVirtual()) {
      $address = $quote->getBillingAddress();
    } else {
      $address = $quote->getShippingAddress();
    }
    if ($address) {
			$baseSubtotalWithDiscount = $address->getBaseSubtotalWithDiscount();
			$baseTax = $address->getBaseTaxAmount();
    }

    return Eloom_MercadoPago_Interest::getInstance($baseCurrencyCode, $interest, $baseSubtotalWithDiscount, $baseTax, $installments);
  }

  public function getModuleInterest($order) {
    return $order->getMercadopagoInterestAmount();
  }

  public function getModuleBaseInterest($order) {
    return $order->getMercadopagoBaseInterestAmount();
  }

  public function getModuleInterestCode() {
    return 'eloom_mercadopago_interest';
  }

}
