<?php

##eloom.licenca##

class Eloom_MercadoPago_Block_Payment_Boleto_Form extends Eloom_MercadoPago_Block_Payment_Form {

  /**
   * Instructions text
   *
   * @var string
   */
  protected $_instructions;

  protected function _construct() {
    parent::_construct();
    $this->setTemplate('eloom/mercadopago/payment/boleto/form.phtml');
  }

  /**
   * Get instructions text from config
   *
   * @return string
   */
  public function getInstructions() {
    if (is_null($this->_instructions)) {
      $this->_instructions = $this->getMethod()->getInstructions();
    }
    return $this->_instructions;
  }

}
