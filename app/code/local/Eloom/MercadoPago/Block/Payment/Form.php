<?php

##eloom.licenca##

class Eloom_MercadoPago_Block_Payment_Form extends Mage_Payment_Block_Form {

  const ERROR = 'error';
  const NOTICE = 'notice';
  const SUCCESS = 'success';

  private $logger;

  protected function _construct() {
    parent::_construct();
    $this->logger = Eloom_Bootstrap_Logger::getLogger(__CLASS__);
  }

  public function isAllowCampaigns() {
    return Mage::helper('eloom_mercadopago/config')->isAllowCampaigns();
  }

  protected function _prepareLayout() {
    return parent::_prepareLayout();
  }

  public function getGrandTotal() {
    return Mage::getSingleton('checkout/session')->getQuote()->getBaseGrandTotal();
  }

  public function getCampaignsInstructions() {
    return Mage::helper('eloom_mercadopago/config')->getCampaignsInstructions();
  }

}
