<?php

##eloom.licenca##

class Eloom_MercadoPago_Model_Cron extends Mage_Core_Model_Abstract {

  private $logger;

  /**
   * Initialize resource model
   */
  protected function _construct() {
    $this->logger = Eloom_Bootstrap_Logger::getLogger(__CLASS__);
    parent::_construct();
  }

  public function waitingPaymentTransaction() {
    $this->logger->info('MercadoPago - Sonda - Pedidos com Status [pending] OR [NULL] - início');

    $collection = Mage::getModel('sales/order_payment')->getCollection();
    $collection->getSelect()->join(array('o' => $collection->getResource()->getTable('sales/order')), 'o.entity_id = main_table.parent_id', array());

    $collection->addFieldToSelect('*');
    $collection->addFieldToFilter('method', array('in' => array(Eloom_MercadoPago_Model_Method_Cc::PAYMENT_METHOD_CC_CODE, Eloom_MercadoPago_Model_Method_Boleto::PAYMENT_METHOD_BOLETO_CODE)));
    $collection->addFieldToFilter('cc_status', array(
      array('eq' => Eloom_MercadoPago_Enum_Transaction_Status::PENDING),
      array('null' => true)
    ));
    $collection->addAttributeToFilter('o.created_at', array('lt' => date('Y-m-d H:i:s', strtotime('-30 minutes'))));

    if ($collection->getSize()) {
      $config = Mage::helper('eloom_mercadopago/config');

      foreach ($collection as $payment) {
        try {
          $order = Mage::getModel('sales/order')->load($payment->getParentId());
          $transactionId = $payment->getLastTransId();
          if (empty($transactionId)) {
            Mage::getModel('eloom_mercadopago/order')->cancel($order, 'Transação não localizada no MercadoPago.');
            continue;
          }
          Mage::getModel('eloom_mercadopago/transaction_code')->synchronizeTransaction($config->getAccessToken(), $payment, $order);
        } catch (Exception $exc) {
          $this->logger->error(sprintf("Erro ao verificar pagamento. Pedido [%s], Code [%s], [%s]", $order->getIncrementId(), $exc->getCode(), $exc->getMessage()));
        }
      }
    }

    $this->logger->info('MercadoPago - Sonda - Pedidos com Status [pending] OR [NULL] - fim');
  }

  public function cancelOrderWithPaymentExpired() {
    $this->logger->info('MercadoPago - Sonda - Pagamento Expirado - Inicio');
    $config = Mage::helper('eloom_mercadopago/config');
    if ($config->isBoletoCancel()) {
      $collection = Mage::getModel('sales/order')->getCollection();
      $collection->getSelect()->join(array('p' => $collection->getResource()->getTable('sales/order_payment')), 'p.parent_id = main_table.entity_id', array());

      $collection->addFieldToSelect('increment_id');
      $collection->addFieldToFilter('status', array('eq' => Mage_Sales_Model_Order::STATE_PENDING_PAYMENT));
      $collection->addFieldToFilter('method', Eloom_MercadoPago_Model_Method_Boleto::PAYMENT_METHOD_BOLETO_CODE);
      $collection->addAttributeToFilter('p.boleto_cancellation', array('lt' => date('Y-m-d H:i:s', strtotime('now'))));

      if ($collection->getSize()) {
        foreach ($collection as $order) {
          try {
            $order = Mage::getModel('sales/order')->loadByIncrementId($order->getIncrementId());
            Mage::getModel('eloom_mercadopago/order')->cancel($order, 'Prazo de pagamento expirado.');
          } catch (Exception $e) {
            $this->logger->error($e->getMessage());
          }
        }
      }
    }

    $this->logger->info('MercadoPago - Sonda - Pagamento Expirado - Fim');
  }

}
