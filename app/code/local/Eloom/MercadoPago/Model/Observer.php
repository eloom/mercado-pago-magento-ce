<?php

##eloom.licenca##

class Eloom_MercadoPago_Model_Observer {

  public function cancelOrder(Varien_Event_Observer $observer) {
    $order = $observer->getEvent()->getOrder();
    $comment = $observer->getEvent()->getComment();

    Mage::getModel('eloom_mercadopago/order')->cancel($order, $comment);
  }

  public function processTransaction(Varien_Event_Observer $observer) {
    $order = $observer->getEvent()->getOrder();
    $status = $observer->getEvent()->getStatus();

    Mage::getModel('eloom_mercadopago/order')->processTransaction($order, $status);
  }

}
