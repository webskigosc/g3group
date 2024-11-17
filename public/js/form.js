$(document).ready(function () {
  const form = $('#clientForm');
  const alert = $('#alert');

  form.validate({
    rules: {
      firstname: {
        required: true,
        regex: /^[\-A-Za-zżźćńółęąśŻŹĆĄŚĘŁÓŃ ]{2,255}$/,
        minlength: 2,
        normalizer: $.trim,
      },
      lastname: {
        required: true,
        regex: /^[\-A-Za-zżźćńółęąśŻŹĆĄŚĘŁÓŃ ]{2,255}$/,
        normalizer: $.trim,
      },
      email: {
        required: true,
        email: true,
        regex: /^([a-zA-Z0-9._%+-]+)@([a-zA-Z0-9.-]+\.[a-zA-Z]{2,})$/,
        normalizer: $.trim,
      },
      phone: {
        required: false,
        regex: /^[0-9]{3} [0-9]{3} [0-9]{3}$/,
        normalizer: $.trim,
      },
      client: {
        required: true,
        regex: /^[0]{3}[0-9]{3}\-[A-Z]{5}$/,
        normalizer: $.trim,
      },
      choose: {
        required: true,
      },
      account: {
        required: '#chooseOne:checked',
        regex: /^[0-9]{2} [0-9]{4} [0-9]{4} [0-9]{4} [0-9]{4} [0-9]{4} [0-9]{4}$/,
        normalizer: $.trim,
      },
      agreementterms: {
        required: true,
      },
      agreementgdpr: {
        required: true,
      },
      agreementads: {
        required: false,
      },
    },
    messages: {
      firstname: {
        required: 'Imię jest wymagane',
        minlength: 'Imię musi zawierać minimum 2 litery',
        regex: 'Imię musi zawierać minimum 2 litery',
      },
      lastname: {
        required: 'Nazwisko jest wymagane',
        minlength: 'Nazwisko musi zawierać minimum 2 litery',
        regex: 'Nazwisko musi zawierać minimum 2 litery',
      },
      email: {
        required: 'Adres email jest wymagany',
        email: 'Niepoprawny adres email',
        regex: 'Wymagany jest poprawny adres email (np: imie.nazwisko@domena.pl).',
      },
      phone: {
        minlength: 'Numer telefonu musi zawierać 9 cyfr',
        regex: 'Numer telefonu musi zawierać 9 cyfr',
      },
      client: {
        required: 'Numer klienta jest wymagany',
        regex: 'Numer klienta musi zawierać 3 zera, 3 cyfry i 5 liter (np. 000123-ABCDE)',
      },
      choose: 'Wybór jednej z opcji jest wymagany',
      account: {
        required: 'Numer konta bankowego jest wymagany',
        minlength: 'Numer polskiego konta bankowego musi zawierać 26 cyfr',
        regex: 'Numer polskiego konta bankowego musi zawierać 26 cyfr',
      },
      agreementgdpr: 'Zgoda na przetwarzania danych jest wymagana',
      agreementterms: 'Akceptacja regulaminu jest wymagana',
    },
    errorClass: 'help-block error',
    pendingClass: 'help-block pending',
    validClass: 'help-block success',
    errorElement: 'span',
    focusInvalid: true,
    ignore: ':hidden, .hide',
    errorPlacement: function (error, element) {
      element.parents('.form-group').addClass('has-error');
      if (element.is(':radio')) {
        element.parents('.radio-inline').addClass('has-error');
        error.appendTo(element.parents('.radio-inline'));
      } else if (element.is(':checkbox')) {
        element.parents('.checkbox').addClass('has-error');
        error.appendTo(element.parents('.checkbox'));
      } else if (element.parent('.input-group').length) {
        error.appendTo(element.parents('.form-group'));
      } else {
        error.insertAfter(element);
      }
    },
    highlight: function (element) {
      $(element).closest('.form-group, .radio-inline, .checkbox').addClass('has-error');
    },
    unhighlight: function (element) {
      $(element).closest('.form-group, .radio-inline, .checkbox').removeClass('has-error');
    },

    submitHandler: function (form) {
      $.ajax({
        url: $(form).attr('action'),
        type: $(form).attr('method'),
        data: $(form).serialize(),
        success: function (response) {
          if (response.success) {
            displayAlert(response.message, 'success');
            $(form)[0].reset();
          } else {
            displayAlert(response.message, 'warning', response.errors);
          }
        },
        error: function (xhr, status, error) {
          console.error('Przesyłanie formularza nie powiodło się:', error);
          displayAlert(response.message, 'error', response.errors);
        },
      });
    },
  });

  $.validator.addMethod('regex', function (value, element, regexp) {
    if (regexp && regexp.constructor != RegExp) {
      regexp = new RegExp(regexp);
    } else if (regexp.global) regexp.lastIndex = 0;

    return this.optional(element) || regexp.test(value);
  });

  function formatName(value) {
    return value.replace(/[^\s\-A-Za-zżźćńółęąśŻŹĆĄŚĘŁÓŃ]/g, '').trim();
  }

  function formatEmail(value) {
    return value.replace(/[^a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}/g, '').trim();
  }

  function formatPhoneNumber(value) {
    value = value.replace(/[^0-9]/g, '').trim();
    let formattedValue = '';
    for (var i = 0; i < value.length; i++) {
      if (i === 3 || i === 6) {
        formattedValue += ' ';
      }
      formattedValue += value[i];
    }
    return formattedValue;
  }

  function formatClientNumber(value) {
    const sanitizedValue = value.replace(/[^0-9a-zA-Z-]/g, '').trim();
    const parts = sanitizedValue.split('-');
    let numericPart = parts[0].replace(/[^0-9]/g, '');
    let alphaPart = parts[1] ? parts[1].replace(/[^a-zA-Z]/g, '').toUpperCase() : '';
    if (numericPart.length > 6 && sanitizedValue.indexOf('-') === -1) {
      numericPart = numericPart.substring(0, 6);
    }
    if (sanitizedValue.length === 7 && sanitizedValue.indexOf('-') === -1) {
      numericPart += '-';
    }
    if (alphaPart.length > 5) {
      alphaPart = alphaPart.substring(0, 5);
    }
    let formattedValue = numericPart + (alphaPart ? '-' + alphaPart : '');
    if (sanitizedValue.indexOf('-') !== -1 && numericPart.length === 6) {
      formattedValue = numericPart + '-' + alphaPart;
    }
    return formattedValue;
  }

  function formatBankAccount(value) {
    value = value.replace(/[^0-9]/g, '').trim();
    let formattedValue = '';
    for (var i = 0; i < value.length; i++) {
      if (i === 2 || i === 6 || i === 10 || i === 14 || i === 18 || i === 22) {
        formattedValue += ' ';
      }
      formattedValue += value[i];
    }
    return formattedValue;
  }

  function formatString(value) {
    return value.replace(/[^a-zA-Z0-9]/g, '').trim();
  }

  function displayAlert(message = '', type = 'success', details = [], timer = 5000) {
    if (typeof message !== 'string' || message === '') {
      return;
    }
    alert.removeClass('hide alert-danger alert-success alert-warning');
    alert.addClass('alert-' + type);
    alert.find('.alert-text').text(message);
    if (typeof details === 'object' && Object.keys(details).length > 0) {
      const detailsHtml = Object.entries(details).reduce((listHtml, [key, value]) => {
        if (value) {
          listHtml += (listHtml ? '<br>' : '') + value;
        }
        return listHtml;
      }, '');
      if (detailsHtml) {
        alert.find('.alert-text').html(`${message}<br>${detailsHtml}`);
      }
    }
    alert.show();
    setTimeout(() => alert.hide(), timer);
  }

  $('#clientForm :input').on('input', (e) => {
    const input = $(e.target);
    let value = input.val();

    if (input.is(':radio') && input.attr('name') === 'choose') {
      const inputAccount = $('#bankAccount');
      input.is(':checked') && input.attr('id') === 'chooseOne'
        ? (inputAccount.parents('.row').removeClass('hide'), inputAccount.prop('disabled', false))
        : (inputAccount.parents('.row').addClass('hide'), inputAccount.prop('disabled', true), inputAccount.val(''));
    }

    switch (input.attr('name')) {
      case 'firstname':
        value = formatName(value);
        break;
      case 'lastname':
        value = formatName(value);
        break;
      case 'email':
        value = formatEmail(value);
        break;
      case 'phone':
        value = formatPhoneNumber(value);
        break;
      case 'client':
        value = formatClientNumber(value);
        break;
      case 'account':
        value = formatBankAccount(value);
        break;
      default:
        value = formatString(value);
        break;
    }

    input.val(value);
  });
});
