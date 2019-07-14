<?php

/**
 * Credit card generic payment info
 */
class Eloom_MercadoPago_Block_Payment_Boleto_Info extends Mage_Payment_Block_Info {

  protected function _construct() {
    parent::_construct();
    $this->setTemplate('eloom/mercadopago/payment/boleto/info.phtml');
  }

  /**
   * Prepare credit card related payment info
   *
   * @param Varien_Object|array $transport
   * @return Varien_Object
   */
  protected function _prepareSpecificInformation($transport = null) {
    if (null !== $this->_paymentSpecificInformation) {
      return $this->_paymentSpecificInformation;
    }
    $helper = Mage::helper('eloom_mercadopago');
    $transport = parent::_prepareSpecificInformation($transport);
    $info = $this->getInfo();
    $data = array();

    $status = $info->getCcStatus();
    if (!empty($status)) {
      $data[$helper->__('Status')] = $helper->__('Transaction.Status.' . $status);
    }

    if ($lastTransactionId = $info->getLastTransId()) {
      $data[$helper->__('MercadoPago Transaction ID')] = $lastTransactionId;
    }

    $additionalData = json_decode($info->getAdditionalData());
    if (!empty($additionalData)) {
      if (isset($additionalData->id)) {
        $data[$helper->__('MercadoPago Order ID')] = $additionalData->id;
      }
      if (isset($additionalData->barCode)) {
        $data[$helper->__('MercadoPago Bar Code')] = $additionalData->barCode;
      }
    }

    // errors
    $errors = json_decode($info->getCcDebugResponseBody());
    if (!empty($errors)) {
      foreach ($errors as $error) {
        $data[$helper->__('Error')] = $error;
      }
    }

    return $transport->setData(array_merge($data, $transport->getData()));
  }

  public function getBoletoLink() {
    $info = $this->getInfo();
    $lastTransId = $info->getLastTransId();

    if (!empty($lastTransId) && ($info->getOrder()->getStatus() == 'pending' || $info->getOrder()->getStatus() == 'pending_payment')) {
      $additionalData = json_decode($info->getAdditionalData());
      return $additionalData->paymentLink;
    }
  }

}
