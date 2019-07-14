<?php

##eloom.licenca##

class Eloom_MercadoPago_Campaign extends stdClass {

  /**
   *
   * @var type 
   */
  public $baseCurrencyCode;

  /**
   *
   * @var type 
   */
  public $totalAmount;

  /**
   *
   * @var type
   */
  public $baseSubtotalWithDiscount;

  /**
   *
   * @var type
   */
  public $baseTax;

  public function __construct() {
    $this->baseCurrencyCode = 0;
    $this->totalPercent = 0;
    $this->baseSubtotalWithDiscount = 0;
    $this->baseTax = 0;
  }

  public static function getInstance($baseCurrencyCode, $totalAmount, $baseSubtotalWithDiscount, $baseTax) {
    $paymentDiscount = new Eloom_MercadoPago_Campaign();
    $paymentDiscount->setBaseCurrencyCode($baseCurrencyCode);
    $paymentDiscount->setTotalAmount($totalAmount);
    $paymentDiscount->setBaseSubtotalWithDiscount($baseSubtotalWithDiscount);
    $paymentDiscount->setBaseTax($baseTax);

    return $paymentDiscount;
  }

  public function getTotalAmount() {
    return $this->totalAmount;
  }

  public function setTotalAmount($totalAmount) {
    $this->totalAmount = $totalAmount;
  }

  public function getBaseCurrencyCode() {
    return $this->baseCurrencyCode;
  }

  public function setBaseCurrencyCode($baseCurrencyCode) {
    $this->baseCurrencyCode = $baseCurrencyCode;
  }

  public function getBaseSubtotalWithDiscount() {
    return $this->baseSubtotalWithDiscount;
  }

  public function setBaseSubtotalWithDiscount($baseSubtotalWithDiscount) {
    $this->baseSubtotalWithDiscount = $baseSubtotalWithDiscount;
  }

  public function getBaseTax() {
    return $this->baseTax;
  }

  public function setBaseTax($baseTax) {
    $this->baseTax = $baseTax;
  }

}
