<?php

##eloom.licenca##

class Eloom_MercadoPago_Enum_Transaction_Status extends Eloom_MercadoPago_Enum_Enum {

  /**
   * Transação pendente ou em validação
   */
  const PENDING = 'pending';
  const APPROVED = 'approved';
  const AUTHORIZED = 'authorized';
  const IN_PROCESS = 'in_process';
  const IN_MEDIATION = 'in_mediation';
  const REJECTED = 'rejected';
  const CANCELLED = 'cancelled';
  const REFUNDED = 'refunded';
  const CHARGED_BACK = 'charged_back';

}
