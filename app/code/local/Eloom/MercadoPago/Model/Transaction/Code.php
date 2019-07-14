<?php

##eloom.licenca##

class Eloom_MercadoPago_Model_Transaction_Code extends Mage_Core_Model_Abstract {

  private $logger;

  /**
   * Initialize resource model
   */
  protected function _construct() {
    $this->logger = Eloom_Bootstrap_Logger::getLogger(__CLASS__);
  }

  public function synchronizeTransaction($accessToken, $payment, $order) {
    $transactionId = $payment->getLastTransId();
    if (empty($transactionId)) {
      throw new InvalidArgumentException(sprintf("Transação não encontrada. Pedido %s", $order->getIncrementId()));
    }
    $this->logger->info(sprintf("Verificando Status do Pedido [%s]", $order->getIncrementId()));
    if ($order->isCanceled()) {
      $this->logger->info(sprintf("Pedido [%s] está cancelado. Sistema irá cancelar o status do pagamento.", $order->getIncrementId()));

      $payment->setCcStatus(Eloom_MercadoPago_Enum_Transaction_Status::CANCELLED);
      $payment->save();

      return true;
    }

    $request = array(
        'uri' => '/v1/payments/' . $payment->getLastTransId(),
        'params' => array(
            'access_token' => $accessToken
        ),
        'authenticate' => false
    );

    $api = new Eloom_MercadoPago_Api($accessToken);
    $response = $api->get($request);
    $response = $response['response'];

    $this->logger->info(sprintf("Pedido [%s] - Status Loja [%s] - Status MP [%s].", $order->getIncrementId(), $payment->getCcStatus(), $response['status']));

    if ($response['status'] != $payment->getCcStatus()) {
      $payment->setCcStatus($response['status']);
      $payment->save();

      Mage::dispatchEvent('eloom_mercadopago_process_transaction', array('order' => $order, 'status' => $response['status']));
    }

    return true;
  }

}
