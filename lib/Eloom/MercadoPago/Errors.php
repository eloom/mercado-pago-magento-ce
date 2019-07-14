<?php

##eloom.licenca##

class Eloom_MercadoPago_Errors {

  public static $list = array(
      'cc_rejected_other_reason' => array('message' => 'O pagamento foi rejeitado. Para mais detalhes, entre em contato com emissor de seu cartão de crédito.'),
      'cc_rejected_blacklist' => array('message' => 'O pagamento foi rejeitado. Para mais detalhes, entre em contato com emissor de seu cartão de crédito.'),
      'cc_rejected_bad_filled_card_number' => array('message' => 'Verifique se você informou o número do cartão de crédito corretamente.'),
      'cc_rejected_bad_filled_date' => array('message' => 'Verifique se você informou a data de validade do cartão de crédito corretamente.'),
      'cc_rejected_bad_filled_other' => array('message' => 'Verifique se você informou os dados do cartão de crédito corretamente.'),
      'cc_rejected_bad_filled_security_code' => array('message' => 'Verifique se você informou o código de segurança do cartão de crédito corretamente.'),
      'cc_rejected_call_for_authorize' => array('message' => 'O pagamento não foi autorizado. Entre em contato com o emissor de seu cartão de crédito e autorize o pagamento ao MercadoPago.'),
      'cc_rejected_card_disabled' => array('message' => 'Entre em contato com o emissor de seu cartão de crédito e verifique se o mesmo está ativo.'),
      'cc_rejected_card_error' => array('message' => 'Não foi possível processar o pagamento. Para mais detalhes, entre em contato com emissor de seu cartão de crédito.'),
      'cc_rejected_duplicated_payment' => array('message' => 'Você já fez o pagamento deste valor. Se você precisa pagar novamente use outro cartão de crédito ou outro meio de pagamento.'),
      'cc_rejected_high_risk' => array('message' => 'O pagamento foi recusado. Recomendamos que você pague com outro meio de pagamento oferecido.'),
      'cc_rejected_insufficient_amount' => array('message' => 'Entre em contato com o emissor de seu cartão de crédito e verifique se há saldo suficiente para realizar esta compra.'),
      'cc_rejected_invalid_installments' => array('message' => 'Este cartão de crédito não aceita este número de parcelas.'),
      'cc_rejected_max_attempts' => array('message' => 'Você atingiu o limite de tentativas permitidas. Use outro cartão de crédito ou outro meio de pagamento.'),
      '500' => array('message' => 'Ocorreu um erro interno ao enviar o pedido ao MercadoPago. Por favor, tente mais tarde.'),
			'504' => array('message' => 'Desculpe-nos o transtorno. O serviço de pagamento está indisponível no momento. Por favor, tente efetuar a compra novamente em alguns minutos.'),
		  '2000' => array('message' => 'Pagamento não encontrado.'),
      '301' => array('message' => 'Verifique se você informou a data de validade do cartão de crédito corretamente.'),
      '4' => array('message' => 'Acesso não autorizado.'),
      '2041' => array('message' => 'Acesso não autorizado.'),
      '3002' => array('message' => 'Acesso não autorizado.'),
      '1' => array('message' => 'Ops...Ocorreu um erro imprevisto no sistema.'),
      '3' => array('message' => 'O token enviado deve ser de teste.'),
      '324' => array('message' => 'CPF do Titular é inválido.'),
      '1000' => array('message' => 'Forneça seu token de acesso para prosseguir.'),
      '1001' => array('message' => 'O formato da data deve ser yyyy-MM-ddTHH:mm:ss.SSSZ'),
      '2001' => array('message' => 'Pedido já postado.'),
      '2004' => array('message' => 'Ocorreu uma falha ao enviar seu pedido ao MercadoPago. Por favor, tente mais tarde.'),
      '2002' => array('message' => 'Cliente não encontrado.'),
      '2006' => array('message' => 'Token do Cartão de Crédito não encontrado. Por favor, verifique se informou os dados do Cartão de Crédito corretamente.'),
      '2007' => array('message' => 'Ocorreu uma falha ao enviar seu pedido ao MercadoPago. Por favor, tente mais tarde.'),
      '2009' => array('message' => 'Ocorreu uma falha ao enviar o token do seu cartão de crédito ao MercadoPago. Por favor, tente mais tarde.'),
      '2010' => array('message' => 'Cartão de crédito não encontrado.'),
      '2043' => array('message' => 'Código da campanha do Mercado Pago é inválido.'),
      '2044' => array('message' => 'Código do cupom do Mercado Pago é inválido. Tente novamente informando o cupom corretamente.'),
      '2062' => array('message' => 'Não foi possível validar seu cartão de crédito. Verifique se você informou o número do cartão de crédito corretamente.'),
      '2067' => array('message' => 'CPF do Titular é inválido.'),
      '3017' => array('message' => 'O parâmetro card_number_id não pode ser nulo.'),
      '3018' => array('message' => 'O parâmetro expiration_month não pode ser nulo.'),
      '3019' => array('message' => 'O parâmetro expiration_year não pode ser nulo.'),
      '3020' => array('message' => 'O parâmetro cardholder.name não pode ser nulo.'),
      '3021' => array('message' => 'O parâmetro cardholder.document.number não pode ser nulo.'),
      '3022' => array('message' => 'O parâmetro cardholder.document.type não pode ser nulo.'),
      '3023' => array('message' => 'O parâmetro cardholder.document.subtype não pode ser nulo.'),
      '3032' => array('message' => 'Código de Segurança do Cartão de Crédito é inválido.'),
      '3034' => array('message' => 'Verifique se você informou o número do cartão de crédito corretamente.'),
      '4020' => array('message' => 'URL de Notificação inválida. Por favor, entre em contato com a loja e informe o problema.'),
      '4026' => array('message' => 'O valor do cupom de desconto do MercadoPago é inválido.'),
      '4035' => array('message' => 'Não foi possível identificar o método de pagamento. Por favor, entre em contato com a loja e informe o problema.'),
      '-999' => array('message' => 'Houve um erro ao processar sua requisição. Por favor tente novamente, ou escolha outra forma de pagamento.'),
  );

  /**
   *
   * @var string
   */
  private $code;

  /**
   *
   * @var string
   */
  private $message;

  public function __construct($code) {
    if (array_key_exists($code, self::$list)) {
      $v = self::$list[$code];
      $this->message = $v['message'];
    } else {
      $this->message = $code;
    }
  }

  public function getCode() {
    return $this->code;
  }

  public function getMessage() {
    return $this->message;
  }

  public function setCode($code) {
    $this->code = $code;
  }

  public function setMessage($message) {
    $this->message = $message;
  }

  public static function listAll() {
    return self::$list;
  }

  public function getFullMessage() {
    return $this->code . ' - ' . $this->message;
  }

}
