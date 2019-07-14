<?php

##eloom.licenca##

class Eloom_MercadoPago_Helper_Data extends Mage_Core_Helper_Abstract {

  public function isEnable() {
    return Mage::getStoreConfigFlag('eloom_mercadopago/general/active');
  }

  /**
   * Truncate a float number, example: <code>truncate(-1.49999, 2); // returns -1.49
   * truncate(.49999, 3); // returns 0.499
   * </code>
   * @param float $val Float number to be truncate
   * @param int f Number of precision
   * @return float
   */
  function truncate($val, $f = '2') {
    if (($p = strpos($val, '.')) !== false) {
      $val = floatval(substr($val, 0, $p + 1 + $f));
    }
    return $val;
  }

  public function getPayment() {
    $orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
    $order = Mage::getModel('sales/order')->load($orderId);
    return $order->getPayment();
  }

}
