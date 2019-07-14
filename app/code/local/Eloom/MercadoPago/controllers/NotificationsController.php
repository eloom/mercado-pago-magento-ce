<?php

##eloom.licenca##

class Eloom_MercadoPago_NotificationsController extends Mage_Core_Controller_Front_Action {

  private $logger;

  /**
   * Initialize resource model
   */
  protected function _construct() {
    $this->logger = Eloom_Bootstrap_Logger::getLogger(__CLASS__);
    parent::_construct();
  }

  public function indexAction() {
    $data = $this->getRequest()->getParams();

    $this->logger->info(sprintf("Recebendo notificação - Parâmetros %s", json_encode($data)));

    if ($data) {
      /*
        if (isset($data['type']) && $data['type'] == 'payment') {
        try {
        $payment = Mage::getModel('sales/order_payment')->load($data['data_id'], 'last_trans_id');
        if ($payment && $payment->getId() == '') {
        $this->logger->info(sprintf("Pagamento não encontrado. Transação [%s]", $data['data_id']));
        } else {
        $config = Mage::helper('eloom_mercadopago/config');
        $order = Mage::getModel('sales/order')->load($payment->getParentId());
        Mage::getModel('eloom_mercadopago/transaction_code')->synchronizeTransaction($config->getAccessToken(), $payment, $order);
        }
        $this->getResponse()->setHttpResponseCode(200);
        } catch (Exception $exc) {
        $this->logger->error(sprintf("Erro ao sincronizar pagamento. Transação [%s]", $data['data_id'], $exc->getMessage()));
        $this->getResponse()->setHttpResponseCode(500);
        }
        } else
       */
      if (isset($data['topic']) && $data['topic'] == 'payment') {
        try {
          $payment = Mage::getModel('sales/order_payment')->load($data['id'], 'last_trans_id');
          if ($payment && $payment->getId() == '') {
            $this->logger->info(sprintf("Pagamento não encontrado. Transação [%s]", $data['id']));
          } else {
            $config = Mage::helper('eloom_mercadopago/config');
            $order = Mage::getModel('sales/order')->load($payment->getParentId());
            Mage::getModel('eloom_mercadopago/transaction_code')->synchronizeTransaction($config->getAccessToken(), $payment, $order);
          }
          $this->getResponse()->setHttpResponseCode(200);
        } catch (Exception $exc) {
          $this->logger->error(sprintf("Erro ao sincronizar pagamento. Transação [%s]", $data['id'], $exc->getMessage()));
          $this->getResponse()->setHttpResponseCode(500);
        }
      }
    }
  }

}
