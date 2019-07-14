<?php

##eloom.licenca##

class Eloom_MercadoPago_BoletoController extends Mage_Core_Controller_Front_Action {

  private $logger;

  /**
   * Initialize resource model
   */
  protected function _construct() {
    $this->logger = Eloom_Bootstrap_Logger::getLogger(__CLASS__);
    parent::_construct();
  }

  /**
   * Send expire header to ajax response
   *
   */
  protected function _expireAjax() {
    if (!Mage::getSingleton('checkout/session')->getQuote()->hasItems()) {
      $this->getResponse()->setHeader('HTTP/1.1', '403 Session Expired');
      exit;
    }
  }

  public function paymentAction() {
    $session = Mage::getSingleton('checkout/session');
    $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
    if ($order->getId() == '') {
      $this->_redirect('checkout/onepage/failure', array('_secure' => true));
      return;
    }
    try {
      $resp = Mage::getModel('eloom_mercadopago/boleto_request')->generatePaymentRequest($order);
      $response = $resp['response'];

      $additionalData = new stdClass();
      $additionalData->id = $response['id'];
      if (isset($response['transaction_details'])) {
        $additionalData->paymentLink = $response['transaction_details']['external_resource_url'];
        $additionalData->verificationCode = $response['transaction_details']['verification_code'];
        $additionalData->dateOfExpiration = $response['date_of_expiration'];
        $additionalData->barcode = $response['barcode']['content'];
      }

      $payment = $order->getPayment();
      $payment->setAdditionalData(json_encode($additionalData));

      $payment->setCcStatus($response['status']);
      $payment->setLastTransId($response['id']);

      // calcular data para cancelar boleto
      $orderCreatedAt = $order->getCreatedAt();
      $config = Mage::helper('eloom_mercadopago/config');
      $dayOfWeek = date("w", strtotime($orderCreatedAt));
      $incrementDays = null;

      switch ($dayOfWeek) {
        case 5: // Sexta-Feira
          $incrementDays = $config->getBoletoCancelOnFriday();
          break;

        case 6: // Sabado
          $incrementDays = $config->getBoletoCancelOnSaturday();
          break;

        default:
          $incrementDays = $config->getBoletoCancelOnSunday();
          break;
      }

      $totalDays = $config->getBoletoExpiration() + $incrementDays;

      $cancellationDate = strftime("%Y-%m-%d %H:%M:%S", strtotime("$orderCreatedAt +$totalDays day"));
      $payment->setBoletoCancellation($cancellationDate);
      $payment->save();

      Mage::dispatchEvent('eloom_mercadopago_process_transaction', array('order' => $order, 'status' => $response['status']));

      switch ($response['status']) {
        case Eloom_MercadoPago_Enum_Transaction_Status::PENDING:
          Mage::getSingleton('checkout/type_onepage')->getCheckout()->setLastSuccessQuoteId(true);
          if ($order->getCanSendNewEmailFlag() && !$order->getEmailSent()) {
            try {
              $order->sendNewOrderEmail();
            } catch (Exception $e) {
              $this->logger->fatal($e->getTraceAsString());
            }
          }
          $this->_redirect('checkout/onepage/success', array('_secure' => true));
          break;
      }
    } catch (Exception $e) {
      $this->logger->fatal($e->getCode() . ' - ' . $e->getMessage());
      $this->logger->fatal($e->getTraceAsString());

      $error = new Eloom_MercadoPago_Errors($e->getCode());
      $errors = array($error->getMessage());

      $order->getPayment()->setCcStatus(Eloom_MercadoPago_Enum_Transaction_Status::CANCELLED);
      $order->getPayment()->setCcDebugResponseBody(json_encode($errors));
      $order->getPayment()->save();

      Mage::dispatchEvent('eloom_mercadopago_cancel_order', array('order' => $order, 'comment' => 'Falha no Pagamento.'));

      Mage::getSingleton('checkout/session')->setErrorMessage("<ul><li>" . implode("</li><li>", $errors) . "</li></ul>");
      $this->_redirect('checkout/onepage/failure', array('_secure' => true));
    }
  }

}
