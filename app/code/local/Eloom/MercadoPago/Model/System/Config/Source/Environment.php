<?php

##eloom.licenca##

class Eloom_MercadoPago_Model_System_Config_Source_Environment {

  public function toOptionArray() {
    return array(
        array(
            'value' => 'PRODUCTION',
            'label' => Mage::helper('adminhtml')->__('Production')
        ),
        array(
            'value' => 'TEST',
            'label' => Mage::helper('adminhtml')->__('Test')
        )
    );
  }
}