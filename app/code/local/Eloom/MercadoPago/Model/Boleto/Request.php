<?php

##eloom.licenca##

class Eloom_MercadoPago_Model_Boleto_Request extends Mage_Core_Model_Abstract {

  private $logger;

  protected function _construct() {
    $this->logger = Eloom_Bootstrap_Logger::getLogger(__CLASS__);
  }

  public function generatePaymentRequest(Mage_Sales_Model_Order $order) {
    $payment = $order->getPayment();
    $additionalData = json_decode($payment->getAdditionalData());

    $billingAddress = $order->getBillingAddress();
    $shippingAddress = null;
    $config = Mage::helper('eloom_mercadopago/config');

    if ($order->getIsVirtual()) {
      $shippingAddress = $order->getBillingAddress();
    } else {
      $shippingAddress = $order->getShippingAddress();
    }

    /* ------- Sender ------- */
    $taxVat = preg_replace('/\D/', '', $order->getCustomerTaxvat());
    $telephone = preg_replace('/\D/', '', $billingAddress->getTelephone());

    /* ------- itens ------- */
    $items = array();
    foreach ($order->getAllItems() as $item) {
      $qtd = $item->getQtyToInvoice();
      $basePrice = round($item->getBasePrice(), 2);
      if (!empty($qtd) && $basePrice > 0) {
        $items[] = array(
            'id' => $item->getSku(),
            'title' => substr($item->getName(), 0, 255),
            'description' => substr($item->getName(), 0, 255),
            'quantity' => $qtd,
            'unit_price' => (float) round($basePrice, 2),
        );
      }
    }
    if ($order->getBaseTaxAmount() > 0) {
      $items[] = array(
          'id' => 'TX',
          'title' => 'Taxas',
          'description' => 'Taxas',
          'quantity' => 1,
          'unit_price' => round($order->getBaseTaxAmount(), 2),
      );
    }
    if ($order->getBaseDiscountAmount() < 0) {
      $items[] = array(
          'id' => 'DESC-LV',
          'title' => 'Desconto da Loja Virtual',
          'description' => 'Desconto da Loja Virtual',
          'quantity' => 1,
          'unit_price' => round($order->getBaseDiscountAmount(), 2),
      );
    }
    if ($order->getBaseAffiliateplusDiscount() < 0) {
      $items[] = array(
          'id' => 'DESC-AFP',
          'title' => 'Desconto Affiliate Plus',
          'description' => 'Desconto Affiliate Plus',
          'quantity' => 1,
          'unit_price' => round($order->getBaseAffiliateplusDiscount(), 2),
      );
    }
    /* Payer */
    $payer = array(
        'entity_type' => 'individual',
        'type' => 'customer',
        'email' => $order->getCustomerEmail(),
        'identification' => array(
            'type' => (strlen($taxVat) > 11 ? 'CNPJ' : 'CPF'),
            'number' => preg_replace('/\D/', '', $taxVat)
        ),
        'first_name' => substr(trim($order->getCustomerFirstname()), 0, 150),
        'last_name' => substr(trim($order->getCustomerLastname()), 0, 150),
        'address' => array(
            'zip_code' => $billingAddress->getPostcode(),
            'street_name' => substr($billingAddress->getStreet(1), 0, 100),
            'street_number' => $billingAddress->getStreet(2),
            'neighborhood' => $billingAddress->getStreet(4),
            'city' => $billingAddress->getCity(),
            'federal_unit' => $billingAddress->getRegionCode()
        )
    );

    $transactionAmount = $order->getBaseGrandTotal() + abs($order->getMercadopagoBaseCampaignAmount());
    $data = array(
        'external_reference' => $order->getIncrementId(),
        'description' => sprintf("Pedido %s", $order->getIncrementId()),
        'transaction_amount' => (float) number_format($transactionAmount, 2, '.', ''),
        'date_of_expiration' => date('Y-m-d', strtotime('+' . $config->getBoletoExpiration() . ' days')) . "T00:00:01.000-03:00",
        'payment_method_id' => 'bolbradesco',
        'notification_url' => Mage::getUrl('eloommercadopago/notifications', array('_secure' => true)),
        'payer' => $payer,
        'additional_info' => array(
            'ip_address' => $order->getRemoteIp(),
            'items' => $items,
            'shipments' => array('receiver_address' => array(
                    'zip_code' => $shippingAddress->getPostcode(),
                    'street_name' => substr($shippingAddress->getStreet(1), 0, 100),
                    'street_number' => $shippingAddress->getStreet(2),
                )),
            'payer' => array(
                'first_name' => substr(trim($order->getCustomerFirstname()), 0, 150),
                'last_name' => substr(trim($order->getCustomerLastname()), 0, 150),
                'phone' => array('area_code' => substr($telephone, 0, 2), 'number' => substr($telephone, 2)),
                'address' => array('zip_code' => $billingAddress->getPostcode(), 'street_name' => substr($billingAddress->getStreet(1), 0, 100), 'street_number' => $billingAddress->getStreet(2))
            ),
        ),
    );

    /* Api Call */
    $api = new Eloom_MercadoPago_Api($config->getAccessToken());

    if ($order->getMercadopagoBaseCampaignAmount() < 0) {
      try {
        $params = array(
            "transaction_amount" => (float) number_format($transactionAmount, 2, '.', ''),
            "payer_email" => $order->getCustomerEmail(),
            "coupon_code" => strtoupper($additionalData->campaignsCode)
        );
        $couponResponse = $api->get("/discount_campaigns", $params);
        if (isset($couponResponse['status']) && $couponResponse['status'] == 200 && isset($couponResponse['response']['coupon_amount'])) {
          $data['coupon_code'] = strtoupper($additionalData->campaignsCode);
          $data['coupon_amount'] = (float) abs($order->getMercadopagoBaseCampaignAmount());
          $data['campaign_id'] = $couponResponse['response']['id'];
        }
      } catch (Exception $e) {
        $this->logger->error(sprintf("[%s] ao buscar cupom [%s], cliente [%s].", $e->getMessage(), $additionalData->campaignsCode, $order->getCustomerEmail()));
      }
    }
    $request = array(
        'uri' => '/v1/payments',
        'params' => array(
            'access_token' => $config->getAccessToken()
        ),
        'data' => $data,
        'authenticate' => false
    );

    $this->logger->info(sprintf("Pedido [%s] - Request [%s]", $order->getIncrementId(), json_encode($request)));

    // send order
    return $api->post($request);
  }

}
