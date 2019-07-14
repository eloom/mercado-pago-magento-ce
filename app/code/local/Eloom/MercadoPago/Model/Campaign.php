<?php

##eloom.licenca##

class Eloom_MercadoPago_Model_Campaign extends Mage_Core_Model_Abstract {

  const CC = 'eloom_mercadopago_cc';
  const BOLETO = 'eloom_mercadopago_boleto';

  private $_methods = array(self::CC, self::BOLETO);
  private $logger;

  /**
   * Initialize resource model
   */
  protected function _construct() {
    $this->logger = Eloom_Bootstrap_Logger::getLogger(__CLASS__);
  }

  public function canApply($address) {
    if (!Mage::helper('eloom_mercadopago/config')->isAllowCampaigns()) {
      return false;
    }
    $data = Mage::app()->getRequest()->getPost('payment', array());
    $currentPaymentMethod = null;

    $sessionQuote = Mage::getSingleton('checkout/session')->getQuote();
    if ($sessionQuote->getPayment() != null && $sessionQuote->getPayment()->hasMethodInstance()) {
      $currentPaymentMethod = $sessionQuote->getPayment()->getMethodInstance()->getCode();
    } elseif (isset($data['method'])) {
      $currentPaymentMethod = $data['method'];
    }

    $campaignsCode = null;
    if ($currentPaymentMethod == self::CC) {
      if (isset($data['mp_cc_campaigns_code']) && !empty($data['mp_cc_campaigns_code'])) {
        $campaignsCode = $data['mp_cc_campaigns_code'];
      }
    } else if ($currentPaymentMethod == self::BOLETO) {
      if (isset($data['boleto_campaigns_code']) && !empty($data['boleto_campaigns_code'])) {
        $campaignsCode = $data['boleto_campaigns_code'];
      }
    }
    if (in_array($currentPaymentMethod, $this->_methods) && $campaignsCode != null) {
      return true;
    }

    return false;
  }

  public function getDiscount() {
    $discount = 0;
    $baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
    $baseSubtotalWithDiscount = 0;
    $baseTax = 0;

    $sessionQuote = Mage::getSingleton('checkout/session')->getQuote();
    $currentPaymentMethod = $sessionQuote->getPayment()->getMethodInstance()->getCode();

    if ($sessionQuote->isVirtual()) {
      $address = $sessionQuote->getBillingAddress();
    } else {
      $address = $sessionQuote->getShippingAddress();
    }
    if ($address) {
      $baseSubtotalWithDiscount = $address->getBaseSubtotalWithDiscount() + $address->getShippingAmount();
      $baseTax = $address->getBaseTaxAmount();
    }
    $config = Mage::helper('eloom_mercadopago/config');
    $accessToken = $config->getAccessToken();

    $api = new Eloom_MercadoPago_Api($accessToken);

    $data = Mage::app()->getRequest()->getPost('payment', array());
    $campaignsCode = null;
    if ($currentPaymentMethod == self::CC) {
      if (isset($data['mp_cc_campaigns_code']) && !empty($data['mp_cc_campaigns_code'])) {
        $campaignsCode = $data['mp_cc_campaigns_code'];
      }
    } else if ($currentPaymentMethod == self::BOLETO) {
      if (isset($data['boleto_campaigns_code']) && !empty($data['boleto_campaigns_code'])) {
        $campaignsCode = $data['boleto_campaigns_code'];
      }
    }

    try {
      if ($sessionQuote->getCustomerEmail()) {
        $params = array(
            "transaction_amount" => $baseSubtotalWithDiscount,
            "payer_email" => $sessionQuote->getCustomerEmail(),
            "coupon_code" => $campaignsCode
        );

        $response = $api->get("/discount_campaigns", $params);
        if (isset($response['response']['coupon_amount'])) {
          $discount = $response['response']['coupon_amount'];
        }
      }
    } catch (Exception $e) {
      $this->logger->error(sprintf("[%s] ao buscar cupom [%s], cliente [%s].", $e->getMessage(), $campaignsCode, $sessionQuote->getCustomerEmail()));
    }

    return Eloom_MercadoPago_Campaign::getInstance($baseCurrencyCode, $discount, $baseSubtotalWithDiscount, $baseTax);
  }

  public function getModuleDiscount($order) {
    return $order->getMercadopagoCampaignAmount();
  }

  public function getModuleBaseDiscount($order) {
    return $order->getMercadopagoBaseCampaignAmount();
  }

  public function getModuleDiscountCode() {
    return 'campaign_eloom_mercadopago';
  }

}
