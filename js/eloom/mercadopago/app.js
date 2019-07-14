var Eloom = Eloom || {};
Eloom.MercadoPago = {
  config: null,
  init: function () {
    if (this.config == null) {
      return;
    }
  },
  CampaignsCode: {
    apply: function (obj) {
      var campaignsUrl = Eloom.MercadoPago.config.campaignsUrl;
      var code = $j(obj).prev('input').val();
      $j.ajax({
        method: 'POST',
        data: {'code': code},
        url: campaignsUrl,
        context: document.body
      }).done(function (response) {
        if (response) {
          $j('div.message-campaings').removeClass().addClass('message-campaings').addClass(response.type).html(response.message);
          payment.update();
        }
      });
    }
  },
  PaymentBoleto: {
    config: null,
    init: function () {
      if (this.config == null) {
        return;
      }
      $j('#p_method_' + Eloom.MercadoPago.PaymentBoleto.config.code).after('<img src="' + Eloom.MercadoPago.config.logo + '">');
      if (payment.currentMethod === Eloom.MercadoPago.PaymentBoleto.config.code) {
        $j('#p_method_' + Eloom.MercadoPago.PaymentBoleto.config.code).click();
      }
      $j('#p_method_' + Eloom.MercadoPago.PaymentBoleto.config.code).on('click', function () {
        $j('#' + Eloom.MercadoPago.PaymentCc.config.code + '-campaigns-code').val('');
        $j('div.message-campaings').removeClass('error notice success').html('');
      });
    }
  },
  PaymentCc: {
    config: null,
    isValidToken: null,
    methodId: null,
    cardNumberId: null,
    cardSecurityCodeId: null,
    cardExpirationId: null,
    cardBrandId: null,
    cardHolderNameId: null,
    cardDocNumberId: null,
    holderAnotherId: null,
    init: function () {
      if (this.config == null) {
        return;
      }
      this.methodId = 'p_method_' + Eloom.MercadoPago.PaymentCc.config.code;
      this.cardNumberId = '#' + Eloom.MercadoPago.PaymentCc.config.code + '-mp-cc-number';
      this.cardSecurityCodeId = '#' + Eloom.MercadoPago.PaymentCc.config.code + '-mp-cc-cvc';
      this.cardExpirationId = '#' + Eloom.MercadoPago.PaymentCc.config.code + '-mp-cc-expiry';
      this.cardBrandId = '#' + Eloom.MercadoPago.PaymentCc.config.code + '-mp-cc-type';
      this.cardHolderNameId = '#' + Eloom.MercadoPago.PaymentCc.config.code + '-mp-cc-owner';
      this.cardDocNumberId = '#' + Eloom.MercadoPago.PaymentCc.config.code + '-mp-cc-holder-cpf';
      this.holderAnotherId = '#' + Eloom.MercadoPago.PaymentCc.config.code + '-mp-cc-holder-another';
      //Eloom.MercadoPago.PaymentCc.Token._makeDefault();
      $j('#p_method_' + Eloom.MercadoPago.PaymentCc.config.code).after('<img src="' + Eloom.MercadoPago.config.logo + '">');
      if (payment.currentMethod === Eloom.MercadoPago.PaymentCc.config.code) {
        $j(Eloom.MercadoPago.PaymentCc.methodId).click();
      }

      this._bindPaymentOption();
      this._bindCreditCardAssistance();
      this._bindSenderInformation();
      this._bindMercadoPagoBehavior();
      this._bindTokenAssistance();
      $j('#' + Eloom.MercadoPago.PaymentCc.config.code + '-installments').on('change', function () {
        payment.update();
      });
      if (typeof payment.addBeforeValidateFunction === 'function') {
        payment.addBeforeValidateFunction('eloommercadopago', Eloom.MercadoPago.PaymentCc.Token.validate);
      }
    },
    _bindMercadoPagoBehavior: function () {
      $j(Eloom.MercadoPago.PaymentCc.cardNumberId).on('blur', function (event) {
        var value = $j(this).val().replace(/\D/g, '');
        if (value.length < 6) {
          return true;
        }
        value = value.substring(0, 6);
        var cardType = '';
        Mercadopago.getPaymentMethod({'bin': value}, function (status, response) {
          if (status == 200 && response.length > 0) {
						cardType = response[0].id;
            $j(Eloom.MercadoPago.PaymentCc.cardBrandId).val(cardType);
            $j(Eloom.MercadoPago.PaymentCc.cardHolderNameId).focus();
            $j(Eloom.MercadoPago.PaymentCc.cardNumberId).css('background-image', 'url("' + Eloom.MercadoPagoCardTypes.getPath(cardType) + '")');
            var installmentsUrl = Eloom.MercadoPago.config.installmentsUrl;
            var installmentsBox = $j('#' + Eloom.MercadoPago.PaymentCc.config.code + '-installments');
            installmentsBox.empty();
            $j.ajax({
              method: 'POST',
              data: {'bin': value, 'type': cardType, 'method': Eloom.MercadoPago.PaymentCc.config.code},
              url: installmentsUrl,
              context: document.body
            }).done(function (response) {
              if (response.length > 0) {
                var installmentAmount = null;
                var text = null;
                var totalAmount = null;
	              var hiddenInstallmentAmount = null;

                $j(response).each(function (index, element) {
                  installmentAmount = element.installment_amount;
                  if (Eloom.MercadoPago.PaymentCc.config.minInstallment > 0) {
                    if (installmentAmount < Eloom.MercadoPago.PaymentCc.config.minInstallment) {
                      return true;
                    }
                  }
	                hiddenInstallmentAmount = installmentAmount;
                  installmentAmount = numeral(installmentAmount).format('0,0.00');
                  totalAmount = numeral(element.total_amount).format('0,0.00');

	                text = element.installments + 'x de R$ '.concat(installmentAmount).concat(' = R$ ' + totalAmount).concat((element.installments === 1 && Eloom.MercadoPago.PaymentCc.config.percentualDiscount > 0 ? ' (' + numeral(Eloom.MercadoPago.PaymentCc.config.percentualDiscount).format('0,0.00') + '% off)' : '')).concat(element.interest > 0 ? ' c/ juros' : '');
	                value = element.installments + '-'.concat(hiddenInstallmentAmount) + '-'.concat(element.interest);

                  //text = element.installments + 'x de R$ '.concat(installmentAmount).concat(' = R$ ' + totalAmount);
                  //value = element.installments;

                  installmentsBox.append($j('<option />').text(text).val(value));
                });
                payment.update();
              }
            });
          }
        });
      });
    }
    ,
    _bindSenderInformation: function () {
      $j(Eloom.MercadoPago.PaymentCc.holderAnotherId).on('click', function () {
        var elements = $j('#payment_form_' + Eloom.MercadoPago.PaymentCc.config.code + ' li.mp-cc-holder');
        if (elements.hasClass('open')) {
          elements.slideToggle().removeClass('open');
          $j('#payment_form_' + Eloom.MercadoPago.PaymentCc.config.code + ' li.mp-cc-holder input[type="text"]').removeClass('required-entry');
        } else {
          elements.slideToggle().addClass('open');
          $j('#' + Eloom.MercadoPago.PaymentCc.config.code + '-mp-cc-holder-cpf').focus();
          $j('#payment_form_' + Eloom.MercadoPago.PaymentCc.config.code + ' li.mp-cc-holder input[type="text"]').addClass('required-entry');
        }
      });
    }
    ,
    _bindPaymentOption: function () {
      $j('#p_method_' + Eloom.MercadoPago.PaymentCc.config.code).on('click', function () {
        $j('#' + Eloom.MercadoPago.PaymentBoleto.config.code + '-campaigns-code').val('');
        $j('div.message-campaings').removeClass('error notice success').html('');
        if ($j('#' + Eloom.MercadoPago.PaymentCc.config.code + '-campaigns-code')) {
          $j('#' + Eloom.MercadoPago.PaymentCc.config.code + '-campaigns-code').focus();
        } else {
          $j(Eloom.MercadoPago.PaymentCc.cardNumberId).focus();
        }
        payment.update();
      });
    },
    _bindCreditCardAssistance: function () {
      $j(Eloom.MercadoPago.PaymentCc.cardNumberId).payment('formatCardNumber');
      $j(Eloom.MercadoPago.PaymentCc.cardExpirationId).payment('formatCardExpiry');
      $j(Eloom.MercadoPago.PaymentCc.cardSecurityCodeId).payment('formatCardCVC');
    },
    _bindTokenAssistance: function () {
      $j(Eloom.MercadoPago.PaymentCc.cardNumberId).on('blur', function (event) {
        Eloom.MercadoPago.PaymentCc.Token.create();
      });
      $j(Eloom.MercadoPago.PaymentCc.cardExpirationId).on('blur', function (event) {
        Eloom.MercadoPago.PaymentCc.Token.create();
      });
      $j(Eloom.MercadoPago.PaymentCc.cardHolderNameId).on('blur', function (event) {
        Eloom.MercadoPago.PaymentCc.Token.create();
      });
      $j(Eloom.MercadoPago.PaymentCc.cardSecurityCodeId).on('blur', function (event) {
        Eloom.MercadoPago.PaymentCc.Token.create();
      });
      $j(Eloom.MercadoPago.PaymentCc.cardDocNumberId).on('blur', function (event) {
        Eloom.MercadoPago.PaymentCc.Token.create();
      });
    },
    Token: {
      ERROR: 'error',
      NOTICE: 'notice',
      SUCCESS: 'success',
      _makeDefault: function () {
        $j('div.message-token').removeClass().addClass('message-token').addClass(Eloom.MercadoPago.PaymentCc.Token.NOTICE).html("Por favor, preencha corretamente todos os dados do Cartão de Crédito.");
      },
      _makeValid: function () {
        $j('div.message-token').removeClass().addClass('message-token').addClass(Eloom.MercadoPago.PaymentCc.Token.SUCCESS).html("Cartão de Crédito validado com sucesso.");
      },
      _makeInvalid: function (messages) {
        $j('div.message-token').removeClass().addClass('message-token').addClass(Eloom.MercadoPago.PaymentCc.Token.ERROR).html(messages.join("<br/>"));
      },
      validate: function () {
        if (Eloom.MercadoPago.PaymentCc.isValidToken === false) {
          var requireInfo = Eloom.MercadoPago.PaymentCc.Token._requireInfo();
          var messages = [];
          messages.push("Não foi possível validar seu Cartão de Crédito.");
          /*
          if (requireInfo.cardNumber == null || requireInfo.cardNumber == '') {
            messages.push("O Número do Cartão de Crédito está incorreto.");
          }
          if (requireInfo.cardHolderName == null || requireInfo.cardHolderName == '') {
            messages.push("O Nome do Portador do Cartão de Crédito está incorreto.");
          }
          if (requireInfo.cardSecurityCode == null || requireInfo.cardSecurityCode == '') {
            messages.push("O Código de segurança do Cartão de Crédito está incorreto.");
          }
          */
          if (requireInfo.cardExpirationMonth == null || requireInfo.cardExpirationMonth == '') {
            messages.push("O mês da validade do Cartão de Crédido está incorreta.");
          }
          if (requireInfo.cardExpirationYear == null || requireInfo.cardExpirationYear == '') {
            messages.push("O ano da validade do Cartão de Crédido está incorreta.");
          }
          if (requireInfo.cardBrand == null || requireInfo.cardBrand == '') {
            messages.push("Não conseguimos identificar a bandeira de seu Cartão de Crédito. Por favor, informe um Cartão de Crédito válido.");
          }

          if (messages.length > 0) {
            Eloom.MercadoPago.PaymentCc.Token._makeInvalid(messages);
            return false;
          }
        }

        return Eloom.MercadoPago.PaymentCc.isValidToken;
      },
      _requireInfo: function () {
        var cardNumber = $j(Eloom.MercadoPago.PaymentCc.cardNumberId).val().replace(/\D/g, '');
        var cardSecurityCode = $j(Eloom.MercadoPago.PaymentCc.cardSecurityCodeId).val();
        var cardExpiration = $j(Eloom.MercadoPago.PaymentCc.cardExpirationId).val().split("/");
        var cardExpirationMonth = null;
        var cardExpirationYear = null;
        if (cardExpiration.length == 2) {
          cardExpirationMonth = cardExpiration[0].trim();
          cardExpirationYear = cardExpiration[1].trim();
          if (cardExpirationYear.trim().length == 2) {
            cardExpirationYear = '20' + cardExpirationYear;
          }
        }
        var cardBrand = $j(Eloom.MercadoPago.PaymentCc.cardBrandId).val();
        var cardHolderName = $j(Eloom.MercadoPago.PaymentCc.cardHolderNameId).val();
        var docNumber = null;
        if ($j('#' + Eloom.MercadoPago.PaymentCc.config.code + '-mp-cc-holder-another:checked').length) {
          docNumber = $j('#' + Eloom.MercadoPago.PaymentCc.config.code + '-mp-cc-holder-cpf').val();
        } else {
          if ($('billing:taxvat') && $('billing:taxvat').getValue() != '') {
            docNumber = $('billing:taxvat').getValue().replace(/\D/g, '');
          } else {
            docNumber = Eloom.MercadoPago.config.customerTaxVat.replace(/\D/g, '');
          }
        }
        var customerEmail = null;
        if ($('billing:email') && $('billing:email').getValue() != '') {
          customerEmail = $('billing:email').getValue();
        } else {
          customerEmail = Eloom.MercadoPago.config.customerEmail;
        }

        return {
          cardNumber: cardNumber,
          cardSecurityCode: cardSecurityCode,
          cardExpirationMonth: cardExpirationMonth,
          cardExpirationYear: cardExpirationYear,
          cardBrand: cardBrand,
          cardHolderName: cardHolderName,
          docNumber: docNumber,
          customerEmail: customerEmail
        };
      },
      create: function () {
        var innerToken = function () {
          var isCreditCardMP = false;
          $$('[name="payment[method]"]').each(function (element) {
            if (element.readAttribute('id') == Eloom.MercadoPago.PaymentCc.methodId) {
              if (element.checked) {
                isCreditCardMP = true;
              }
            }
          });
          if (!isCreditCardMP) {
            return true;
          }

          var requireInfo = Eloom.MercadoPago.PaymentCc.Token._requireInfo();
          var messages = [];
          
          if (requireInfo.customerEmail == null || requireInfo.customerEmail == '') {
            messages.push("Por favor, informe seu email. ");
          }
          if (requireInfo.docNumber == null || requireInfo.docNumber == '') {
            messages.push("Por favor, informe seu CPF.");
          }
          if (messages.length > 0) {
            Eloom.MercadoPago.PaymentCc.Token._makeInvalid(messages);
            return false;
          }
          
          if ((requireInfo.customerEmail == null || requireInfo.customerEmail == '')
                  || (requireInfo.cardNumber == null || requireInfo.cardNumber == '')
                  || (requireInfo.cardSecurityCode == null || requireInfo.cardSecurityCode == '')
                  || (requireInfo.cardExpirationMonth == null || requireInfo.cardExpirationMonth == '')
                  || (requireInfo.cardExpirationYear == null || requireInfo.cardExpirationYear == '')
                  || (requireInfo.cardHolderName == null || requireInfo.cardHolderName == '')
                  || (requireInfo.docNumber == null || requireInfo.docNumber == '')
                  || (requireInfo.cardBrand == null || requireInfo.cardBrand == '')) {
            return false;
          }
          var form = new Element('div', {id: 'mercadopago-form'});
          form.insert(new Element('input', {type: 'text', id: 'email'}).writeAttribute('value', requireInfo.customerEmail));
          form.insert(new Element('input', {type: 'text', id: 'cardNumber'}).writeAttribute('data-checkout', 'cardNumber').writeAttribute('value', requireInfo.cardNumber));
          form.insert(new Element('input', {type: 'text', id: 'securityCode'}).writeAttribute('data-checkout', 'securityCode').writeAttribute('value', requireInfo.cardSecurityCode));
          form.insert(new Element('input', {type: 'text', id: 'cardExpirationMonth'}).writeAttribute('data-checkout', 'cardExpirationMonth').writeAttribute('value', requireInfo.cardExpirationMonth));
          form.insert(new Element('input', {type: 'text', id: 'cardExpirationYear'}).writeAttribute('data-checkout', 'cardExpirationYear').writeAttribute('value', requireInfo.cardExpirationYear));
          form.insert(new Element('input', {type: 'text', id: 'cardholderName'}).writeAttribute('data-checkout', 'cardholderName').writeAttribute('value', requireInfo.cardHolderName));
          form.insert(new Element('select', {id: 'docType'}).writeAttribute('data-checkout', 'docType').insert(new Element('option', {value: 'CPF'}).update('CPF')));
          form.insert(new Element('input', {type: 'text', id: 'docNumber'}).writeAttribute('data-checkout', 'docNumber').writeAttribute('value', requireInfo.docNumber));
          form.insert(new Element('input', {type: 'hidden', name: 'paymentMethodId'}).writeAttribute('value', requireInfo.cardBrand));
          var callMp = function (form) {
            var writeToken = function (status, response) {
              if (status != 200 && status != 201) {
                Eloom.MercadoPago.PaymentCc.isValidToken = false;
                Eloom.MercadoPago.PaymentCc.Token._makeInvalid(['Ops! Alguma informação do Cartão de Crédito está incorreta.']);
                $j('#' + Eloom.MercadoPago.PaymentCc.config.code + '-mp-cc-token').val('');
                $j('#' + Eloom.MercadoPago.PaymentCc.config.code + '-mp-cc-last-four-digits').val('');
              } else {
                $j('#' + Eloom.MercadoPago.PaymentCc.config.code + '-mp-cc-token').val(response.id);
                $j('#' + Eloom.MercadoPago.PaymentCc.config.code + '-mp-cc-last-four-digits').val(response.last_four_digits);
                Eloom.MercadoPago.PaymentCc.isValidToken = true;
                Eloom.MercadoPago.PaymentCc.Token._makeValid();
              }
            }

            Mercadopago.createToken(form, writeToken);
          }

          callMp(form);
        }
        innerToken();
        setTimeout(function () {
          //console.log('isValidToken: ' + Eloom.MercadoPago.PaymentCc.isValidToken);
        }, 1000);
      }
    }
  },
  Errors: {
    config: null,
    init: function () {
      if (this.config == null) {
        return;
      }
    },
    getError: function (response) {
      var m = null;
      try {
        var key = null;
        $j(response.errors).each(function (index, element) {
          for (var k in element) {
            if (!element.hasOwnProperty(k)) {
              continue;
            }
            key = k;
          }
        });
        m = Eloom.MercadoPago.Errors.config[key].message;
      } catch (exception) {
        m = Eloom.MercadoPago.Errors.config[ -999].message;
      }

      return m;
    }
  }
};

Eloom.MercadoPagoCardTypes = {
  path: null,
  url: [],
	cardTypes: ['visa', 'master', 'mastercard', 'amex', 'aura', 'brasilcard', 'cabal', 'cardban', 'diners', 'elo', 'fortbrasil', 'grandcard', 'hipercard', 'mais', 'personalcard', 'plenocard', 'sorocred', 'valecard'],

  init: function (opt) {
    this.path = opt.mediaUrl + 'idecheckoutvm/payment/';
    jQuery(this.cardTypes).each(function (index, element) {
      Eloom.MercadoPagoCardTypes.url[element] = Eloom.MercadoPagoCardTypes.path + element + '.png';
    });
  },

  getPath: function (cardType) {
		cardType = cardType.toLowerCase();
    return Eloom.MercadoPagoCardTypes.url[cardType];
  }
}