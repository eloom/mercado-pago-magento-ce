<?php

##eloom.licenca##

class Eloom_MercadoPago_IndexController extends Mage_Core_Controller_Front_Action {

  const ERROR = 'error';
  const NOTICE = 'notice';
  const SUCCESS = 'success';

  private $logger;

  /**
   * Initialize resource model
   */
  protected function _construct() {
    $this->logger = Eloom_Bootstrap_Logger::getLogger(__CLASS__);
    parent::_construct();
  }

  public function campaignsAction() {
    if (!$this->getRequest()->isPost()) {
      return;
    }
    $campaignsCode = trim($this->getRequest()->getParam('code'));

    $message = null;
    $type = self::ERROR;
    $sessionQuote = Mage::getSingleton('checkout/session')->getQuote();
    $email = $sessionQuote->getCustomerEmail();
    if (empty($campaignsCode)) {
      $message = sprintf($this->__("Informe o código da campanha para visualizar o desconto do MercadoPago."));
      $type = self::NOTICE;
    } else if (empty($email)) {
      $message = sprintf($this->__("Informe seu email e CPF para visualizar o desconto do MercadoPago."));
      $type = self::NOTICE;
    } else {
      if ($sessionQuote->isVirtual()) {
        $address = $sessionQuote->getBillingAddress();
      } else {
        $address = $sessionQuote->getShippingAddress();
      }
      if ($address) {
        $baseSubtotalWithDiscount = $address->getBaseSubtotalWithDiscount() + $address->getShippingAmount();
      }
      $config = Mage::helper('eloom_mercadopago/config');
      $accessToken = $config->getAccessToken();

      $api = new Eloom_MercadoPago_Api($accessToken);

      try {
        if ($sessionQuote->getCustomerEmail()) {
          $params = array(
              "transaction_amount" => $baseSubtotalWithDiscount,
              "payer_email" => $sessionQuote->getCustomerEmail(),
              "coupon_code" => $campaignsCode
          );

          $response = $api->get("/discount_campaigns", $params);

          if (isset($response['response']['coupon_amount'])) {
            $discount = $response['response']['coupon_amount'];
            $formattedPrice = Mage::helper('core')->currency($discount, true, false);
            $message = sprintf($this->__("Você ganhou %s de desconto do MercadoPago.", $formattedPrice));
            $type = self::SUCCESS;
          }
        }
      } catch (Exception $e) {
        switch ($e->getMessage()) {
          case "Run Out of uses per user":
            $message = sprintf($this->__("Ops! Parece que você já usou esse cupom para o email %s.", $sessionQuote->getCustomerEmail()));
            break;

          case "doesn't find a campaign with the given code":
            $message = sprintf($this->__("Ops! A campanha com código %s não foi encontrada.", $campaignsCode));
            break;

          default:
            $this->logger->error(sprintf("[%s] ao buscar cupom [%s], cliente [%s].", $e->getMessage(), $campaignsCode, $sessionQuote->getCustomerEmail()));
            $message = sprintf($this->__("Ops! Houve um erro ao recurepar as informações da campanha %s.", $campaignsCode));
            break;
        }
      }
    }

    $this->getResponse()->setHeader('Content-type', 'application/json', true);
    $this->getResponse()->setBody($this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array('message' => $message, 'type' => $type))));
  }

  public function installmentsAction() {
    $bin = $this->getRequest()->getParam('bin');
    $method = $this->getRequest()->getParam('method');
    try {
      $quote = Mage::getSingleton('checkout/session')->getQuote();
      $quote->getPayment()->setMethod($method)->getMethodInstance();
      //$quote->collectTotals();
      //$quote->save();

      $config = Mage::helper('eloom_mercadopago/config');
      $accessToken = $config->getAccessToken();

      $api = new Eloom_MercadoPago_Api($accessToken);

      if ($quote->isVirtual()) {
        $address = $quote->getBillingAddress();
      } else {
        $address = $quote->getShippingAddress();
      }
      $total = $quote->getBaseGrandTotal();

      $params = array(
          'bin' => $bin,
          'amount' => $total
      );
      $response = $api->get('/v1/payment_methods/installments', $params);

      $installments = array();
      if ($response['status'] == 200) {
        foreach ($response['response'][0]['payer_costs'] as $key => $value) {
          if (1 == (int) $value['installments']) {
            $v = $this->getFirstInstallmentAmount($total, $quote->getShippingAddress()->getBaseShippingAmount());
            $value['installment_amount'] = (double) $v;
            $value['total_amount'] = (double) $v;
          }

          $installments[] = array('installments' => $value['installments'],
																	'installment_amount' => $value['installment_amount'],
																	'total_amount' => $value['total_amount'],
																	'interest' => $value['installment_rate']);
        }
      }

      $this->getResponse()->setHeader('Content-type', 'application/json', true);
      $this->getResponse()->setBody($this->getResponse()->setBody(Mage::helper('core')->jsonEncode($installments)));
    } catch (Exception $e) {
      $this->logger->fatal(sprintf("Erro ao buscar parcelas [%s].", $e->getMessage()));
    }
  }

  private function getFirstInstallmentAmount($paymentAmount, $baseShippingAmount) {
    $installmentAmount = $paymentAmount;

    if (!Mage::helper('eloom_mercadopago/config')->isAllowCampaigns()) {
      $percentualDiscount = Mage::helper('eloom_mercadopago/config')->getPaymentCcDiscount();
      if ($percentualDiscount) {
        $percentualDiscount = str_replace(',', '.', $percentualDiscount);
      }

      if ($percentualDiscount > 0) {
        $paymentAmount = $paymentAmount - (($paymentAmount - $baseShippingAmount) * $percentualDiscount) / 100;

        $installmentAmount = $paymentAmount;
      }
    }

    return Zend_Locale_Math::round($installmentAmount, 2);
  }

}
