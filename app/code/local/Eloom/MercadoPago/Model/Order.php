<?php

##eloom.licenca##

class Eloom_MercadoPago_Model_Order extends Mage_Core_Model_Abstract {

  private $logger;
  private $_messages = array(
      'pending' => 'Pedido aguardando pagamento.',
      'approved' => 'O pagamento foi aprovado.',
      'authorized' => 'O pagamento foi autrizado e está pendente de captura.',
      'in_process' => 'O pagamento está sendo revisado pelo MercadoPago.',
      'in_mediation' => 'O pagamento está em Processo de Disputa no MercadoPago.',
      'rejected' => 'O pagamento foi rejeitado.',
      'cancelled' => 'O pagamento foi cancelado.',
      'refunded' => 'O pagamento foi reembolsado ao cliente.',
      'charged_back' => 'O pagamento foi estornado no cartão de crédito do cliente.'
  );

  /**
   * Initialize resource model
   */
  protected function _construct() {
    $this->logger = Eloom_Bootstrap_Logger::getLogger(__CLASS__);
  }

  /**
   * Initialize order model instance
   *
   * @return Mage_Sales_Model_Order || false
   */
  protected function _initOrder($orderId) {
    $order = Mage::getModel('sales/order')->load($orderId);
    if (!$order->getId()) {
      throw new Exception($this->__('This order no longer exists.'));
    }
    return $order;
  }

  public function cancel($order, $comment) {
    try {
      if ($order->getState() == Mage_Sales_Model_Order::STATE_CANCELED) {
        return true;
      }
      $c = trim($comment);
      $order->cancel();

      //força o cancelamento
      if ($order->getStatus() != Mage_Sales_Model_Order::STATE_CANCELED) {
        $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, $comment, true);
      } else {
        $order->addStatusToHistory($order->getStatus(), $comment);
      }

      if ($order->hasInvoices() != '') {
        $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, $this->__('Não foi possível retornar os produtos ao estoque pois há faturas relacionadas a este pedido.'), false);
      }
      $order->save();

      if ($order->getPayment()) {
        $order->getPayment()->setCcStatus(Eloom_MercadoPago_Enum_Transaction_Status::CANCELLED);
        $order->getPayment()->save();
      }

      try {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = "UPDATE " . Mage::getConfig()->getTablePrefix() . "sales_flat_order_grid SET status = 'canceled' WHERE increment_id = " . $order->getIncrementId();
        $connection->query($sql);
      } catch (Exception $ex) {
        
      }
      $this->logger->info(sprintf("Pedido %s [CANCELADO]. Motivo [%s]", $order->getIncrementId(), $c));
    } catch (Exception $e) {
      try {
        $this->logger->info(sprintf("Forçando cancelamento do pedido [%s].", $order->getIncrementId()));
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = "UPDATE " . Mage::getConfig()->getTablePrefix() . "sales_flat_order SET state = 'canceled', status = 'canceled' WHERE increment_id = " . $order->getIncrementId();
        $connection->query($sql);

        $sql = "UPDATE " . Mage::getConfig()->getTablePrefix() . "sales_flat_order_grid SET status = 'canceled' WHERE increment_id = " . $order->getIncrementId();
        $connection->query($sql);
      } catch (Exception $ex) {
        
      }
    }
  }

  public function processTransaction($order, $status) {
    $comment = $this->_messages[$status];
		$config = Mage::helper('eloom_mercadopago/config');

    switch ($status) {
      case Eloom_MercadoPago_Enum_Transaction_Status::PENDING:
      case Eloom_MercadoPago_Enum_Transaction_Status::IN_PROCESS:
				$order->addStatusHistoryComment($comment, $config->getNewOrderStatus());
				$order->setIsVisibleOnFront(true);
				$order->save();
				$order->sendOrderUpdateEmail(true, $comment);
				break;

      case Eloom_MercadoPago_Enum_Transaction_Status::APPROVED:
				$status = $config->getApprovedOrderStatus();

				if ($order->getState() != $status) {
					$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
					$order->addStatusHistoryComment($comment, $status);
					$order->setIsVisibleOnFront(true);
					$order->save();
					$order->sendOrderUpdateEmail(true, $comment);

          // invoice
          if ($order->canInvoice() && !$order->hasInvoices()) {
            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
            $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
            $invoice->register();

            $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());
            $transactionSave->save();

						$invoice->getOrder()->setIsInProcess(true);
						$invoice->getOrder()->addStatusHistoryComment('Fatura gerada automaticamente.');
						$invoice->sendEmail(true, '');

						$order->save();
          }
        }
        break;

      case Eloom_MercadoPago_Enum_Transaction_Status::REJECTED:
      case Eloom_MercadoPago_Enum_Transaction_Status::CANCELLED:
        $this->cancel($order, $comment);
        break;
    }
  }

}
