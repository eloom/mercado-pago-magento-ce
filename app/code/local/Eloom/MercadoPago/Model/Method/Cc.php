<?php

##eloom.licenca##

class Eloom_MercadoPago_Model_Method_Cc extends Mage_Payment_Model_Method_Abstract {

  const PAYMENT_METHOD_CC_CODE = 'eloom_mercadopago_cc';

  protected $_formBlockType = 'eloom_mercadopago/payment_cc_form';
  protected $_infoBlockType = 'eloom_mercadopago/payment_cc_info';

  /**
   *
   */
  protected $_code = self::PAYMENT_METHOD_CC_CODE;

  /**
   * Payment Method features
   * @var bool
   */
  protected $_isGateway = true;
  protected $_canOrder = false;
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
    return Mage::getUrl('eloommercadopago/cc/payment', array('_secure' => true));
  }

  /**
   * Prepare info instance for save
   *
   * @return Mage_Payment_Model_Abstract
   */
  public function prepareSave() {
    $info = $this->getInfoInstance();

    $additional = new stdClass();
    $additional->creditCardToken = $info->getCcToken();
    $additional->creditCardHolderName = $info->getCcOwner();

		$installments = 0;
		$installmentAmount = 0;

		$arrayex = explode('-', $info->getCcInstallments());
		if (isset($arrayex[0])) {
			$installments = $arrayex[0];
			$installmentAmount = $arrayex[1];
		}

		$additional->installments = $installments;
		$additional->installmentAmount = $installmentAmount;

    if($info->getCcCampaignsCode() && $info->getCcCampaignsCode() != '') {
      $additional->campaignsCode = $info->getCcCampaignsCode();
    }
    if ($info->getCcHolderAnother() && $info->getCcHolderAnother() == 1) {
      $additional->creditCardHolderAnother = $info->getCcHolderAnother();
      $additional->creditCardHolderCpf = $info->getCcHolderCpf();
      $additional->creditCardHolderPhone = $info->getCcHolderPhone();
    }

    $serializedValue = json_encode($additional);
    $info->setAdditionalData($serializedValue);

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
    $lastDigits = preg_replace('/\D/', '', $data->getMpCcLastFourDigits());
    $info = $this->getInfoInstance();
    $info->setCcName($data->getMpCcName())
            ->setCcOwner($data->getMpCcOwner())
            ->setCcLast4($lastDigits)
            ->setCcType($data->getMpCcType())
            ->setCcInstallments($data->getMpCcInstallments())
            ->setCcToken($data->getMpCcToken());

    if ($data->getMpCcHolderAnother() && $data->getMpCcHolderAnother() == 1) {
      $info->setCcHolderAnother($data->getMpCcHolderAnother())
              ->setCcHolderCpf($data->getMpCcHolderCpf())
              ->setCcHolderPhone($data->getMpCcHolderPhone());
    } else {
      $info->setCcHolderAnother(0);
    }
    if ($data->getMpCcCampaignsCode()) {
      $info->setCcCampaignsCode($data->getMpCcCampaignsCode());
    }

    return $this;
  }

}
