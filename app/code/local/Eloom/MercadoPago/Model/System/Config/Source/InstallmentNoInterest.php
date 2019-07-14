<?php

##eloom.licenca##

class Eloom_MercadoPago_Model_System_Config_Source_InstallmentNoInterest {

  public function toOptionArray() {
    $options = array();
    for ($i = 1; $i < 18; $i++) {
      $options[] = array('value' => $i, 'label' => $i);
    }
    return $options;
  }

}
