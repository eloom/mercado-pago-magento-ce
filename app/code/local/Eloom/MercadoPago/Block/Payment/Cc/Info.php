<?php

/**
 * Credit card generic payment info
 */
class Eloom_MercadoPago_Block_Payment_Cc_Info extends Mage_Payment_Block_Info {

  protected function _construct() {
    parent::_construct();
    $this->setTemplate('eloom/mercadopago/payment/cc/info.phtml');
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

    $lastTransactionId = $info->getLastTransId();
    if (!empty($lastTransactionId)) {
      $data[$helper->__('MercadoPago Transaction ID')] = $lastTransactionId;
    }
    $additionalData = json_decode($info->getAdditionalData());
    if (!empty($additionalData)) {
      if (isset($additionalData->id)) {
        //$data[$helper->__('MercadoPago Order ID')] = $additionalData->id;
      }
    }

    if ($ccType = $info->getCcType()) {
      $data[$helper->__('Credit Card Type')] = $ccType;
    }
    if ($info->getCcLast4()) {
      $data[$helper->__('Last Credit Card Number')] = sprintf('xxxx xxxx xxxx %s', $info->getCcLast4());
    }

    if (!empty($additionalData)) {
      if (isset($additionalData->creditCardHolderName)) {
        $data[$helper->__('CC Owner')] = strtoupper($additionalData->creditCardHolderName);
      }
      if (isset($additionalData->installments)) {
        $data[$helper->__('Installments')] = sprintf("Em %sx de %s", $additionalData->installments, $additionalData->installmentAmount);
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

  /**
   * Format year/month on the credit card
   *
   * @param string $year
   * @param string $month
   * @return string
   */
  protected function _formatCardDate($year, $month) {
    return sprintf('%s/%s', sprintf('%02d', $month), $year);
  }

}
