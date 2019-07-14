<?php

##eloom.licenca##

class Eloom_MercadoPago_Model_Method_Boleto extends Mage_Payment_Model_Method_Abstract {

  const PAYMENT_METHOD_BOLETO_CODE = 'eloom_mercadopago_boleto';

  protected $_formBlockType = 'eloom_mercadopago/payment_boleto_form';
  protected $_infoBlockType = 'eloom_mercadopago/payment_boleto_info';

  /**
   *
   */
  protected $_code = self::PAYMENT_METHOD_BOLETO_CODE;

  /**
   * Payment Method features
   * @var bool
   */
  protected $_isGateway = false;
  protected $_canOrder = true;
  protected $_canAuthorize = true;
  protected $_canCapture = true;
  protected $_canCapturePartial = false;
  protected $_canCaptureOnce = false;
  protected $_canRefund = true;
  protected $_canRefundInvoicePartial = false;
  protected $_canVoid = true;
  protected $_canUseInternal = false;
  protected $_canUseCheckout = true;
  protected $_canUseForMultishipping = false;
  protected $_isInitializeNeeded = false;
  protected $_canFetchTransactionInfo = false;
  protected $_canReviewPayment = false;
  protected $_canCreateBillingAgreement = false;
  protected $_canManageRecurringProfiles = false;
  protected $_canCancelInvoice = true;

  protected function _construct() {
    parent::_construct();
  }

  public function getOrderPlaceRedirectUrl() {
    return Mage::getUrl('eloommercadopago/boleto/payment', array('_secure' => true));
  }

  /**
   * Get instructions text from config
   *
   * @return string
   */
  public function getInstructions() {
    return trim($this->getConfigData('instructions'));
  }

  /**
   * Prepare info instance for save
   *
   * @return Mage_Payment_Model_Abstract
   */
  public function prepareSave() {
    $info = $this->getInfoInstance();
    if ($info->getBoletoCampaignsCode()) {
      $additional = new stdClass();
      $additional->campaignsCode = $info->getBoletoCampaignsCode();

      $serializedValue = json_encode($additional);
      $info->setAdditionalData($serializedValue);
    }

    return $this;
  }

  /**
   * Assign data to info model instance
   *
   * @param   mixed $data
   * @return  Mage_Payment_Model_Info
   */
  public function assignData($data) {
    if (!($data instanceof Varien_Object)) {
      $data = new Varien_Object($data);
    }
    $info = $this->getInfoInstance();
    if ($data->getBoletoCampaignsCode()) {
      $info->setBoletoCampaignsCode($data->getBoletoCampaignsCode());
    }

    return $this;
  }

}
