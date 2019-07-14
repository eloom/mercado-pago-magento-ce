<?php

##eloom.licenca##

class Eloom_MercadoPago_Helper_Config extends Mage_Core_Helper_Abstract {

  /**
   * Public Key
   */
  const XML_PATH_PUBLIC_KEY = 'payment/eloom_mercadopago/public_key';

  /**
   * Access Token
   */
  const XML_PATH_ACCESS_TOKEN = 'payment/eloom_mercadopago/access_token';

  /**
   * Ambiente
   */
  const XML_PATH_ENVIRONMENT = 'payment/eloom_mercadopago/environment';

	/**
	 * Status para Novos Pedidos
	 */
	const XML_PATH_NEW_ORDER_STATUS = 'payment/eloom_mercadopago/new_order_status';

	/**
	 * Status para Pedidos Aprovados
	 */
	const XML_PATH_APPROVED_ORDER_STATUS = 'payment/eloom_mercadopago/approved_order_status';

  /**
   * Desconto à Vista
   */
  const XML_PATH_PAYMENT_CC_DISCOUNT = 'payment/eloom_mercadopago_cc/discount';

  /**
   * Parcela Mínima
   */
  const XML_PATH_PAYMENT_CC_MIN_INSTALLMENT = 'payment/eloom_mercadopago_cc/min_installment';

  /**
   * Expiração do Boleto
   */
  const XML_PATH_PAYMENT_BOLETO_EXPIRATION = 'payment/eloom_mercadopago_boleto/expiration';

  /**
   * Cancelamento do Boleto
   */
  const XML_PATH_PAYMENT_BOLETO_CANCEL = 'payment/eloom_mercadopago_boleto/cancel';

  /**
   * Prazo de Expiração para compras realizadas na Sexta-Feira via Boleto
   */
  const XML_PATH_PAYMENT_BOLETO_CANCEL_ON_FRIDAY = 'payment/eloom_mercadopago_boleto/cancel_on_friday';

  /**
   * Prazo de Expiração para compras realizadas no Sábado via Boleto
   */
  const XML_PATH_PAYMENT_BOLETO_CANCEL_ON_SATURDAY = 'payment/eloom_mercadopago_boleto/cancel_on_saturday';

  /**
   * Prazo de Expiração para compras realizadas entre Domingo e Quinta-Feira via Boleto
   */
  const XML_PATH_PAYMENT_BOLETO_CANCEL_ON_SUNDAY = 'payment/eloom_mercadopago_boleto/cancel_on_sunday';

  /**
   * Permitir Campanhas 
   */
  const XML_PATH_ALLOW_CAMPAIGNS = 'payment/eloom_mercadopago/allow_campaigns';

  /**
   * Instruções da Campanha
   */
  const XML_PATH_CAMPAIGNS_INSTRUCTIONS = 'payment/eloom_mercadopago/campaigns_instructions';

  /**
   * 
   */
  public function _construct() {
    parent::_construct();
  }

  /**
   * Retrieve store model instance
   *
   * @return Mage_Core_Model_Store
   */
  public function getStore() {
    return Mage::app()->getStore();
  }

  public function getConfig($path) {
    return Mage::getStoreConfig($path, Mage::app()->getStore()->getStoreId());
  }

  public function getConfigFlag($path) {
    return Mage::getStoreConfigFlag($path, Mage::app()->getStore()->getStoreId());
  }

  public function getPublicKey() {
    return trim($this->getConfig(self::XML_PATH_PUBLIC_KEY));
  }

  public function getAccessToken() {
    return trim($this->getConfig(self::XML_PATH_ACCESS_TOKEN));
  }

  public function getEnvironment() {
    return trim($this->getConfig(self::XML_PATH_ENVIRONMENT));
  }

  public function getPaymentCcDiscount() {
    return trim($this->getConfig(self::XML_PATH_PAYMENT_CC_DISCOUNT));
  }

  public function getPaymentCcMinInstallment() {
    return trim($this->getConfig(self::XML_PATH_PAYMENT_CC_MIN_INSTALLMENT));
  }

  public function getBoletoExpiration() {
    return (int) trim($this->getConfig(self::XML_PATH_PAYMENT_BOLETO_EXPIRATION));
  }

  public function isBoletoCancel() {
    return $this->getConfigFlag(self::XML_PATH_PAYMENT_BOLETO_CANCEL);
  }

  public function getBoletoCancelOnFriday() {
    return (int) trim($this->getConfig(self::XML_PATH_PAYMENT_BOLETO_CANCEL_ON_FRIDAY));
  }

  public function getBoletoCancelOnSaturday() {
    return (int) trim($this->getConfig(self::XML_PATH_PAYMENT_BOLETO_CANCEL_ON_FRIDAY));
  }

  public function getBoletoCancelOnSunday() {
    return (int) trim($this->getConfig(self::XML_PATH_PAYMENT_BOLETO_CANCEL_ON_SUNDAY));
  }

  public function isAllowCampaigns() {
    return $this->getConfigFlag(self::XML_PATH_ALLOW_CAMPAIGNS);
  }

  public function getCampaignsInstructions() {
    return (int) trim($this->getConfig(self::XML_PATH_CAMPAIGNS_INSTRUCTIONS));
  }

	public function getNewOrderStatus() {
		return $this->getConfig(self::XML_PATH_NEW_ORDER_STATUS);
	}

	public function getApprovedOrderStatus() {
		return $this->getConfig(self::XML_PATH_APPROVED_ORDER_STATUS);
	}

}
