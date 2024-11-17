$(document).ready(function () {
  if (typeof updateCounter !== 'function') {
    console.error('Funkcja updateCounter nie została zdefiniowana.');
    return;
  }

  const form = $('#counterForm');
  updateCounter('last_name', 'Kowalski', '#counterLastName');
  updateCounter('email', 'gmail.com', '#counterEmailDomain');

  form.validate({
    rules: {
      type: {
        required: true,
        normalizer: $.trim,
      },
      phrase: {
        required: true,
        normalizer: $.trim,
      },
    },
    messages: {
      phrase: {
        minlength: 'Wymagane są minimum 2 znaki',
        required: 'Pole jest wymagane',
      },
    },
    errorClass: 'col-xs-12 text-danger',
    pendingClass: 'help-block',
    validClass: 'col-xs-12 text-success',
    errorElement: 'span',
    focusInvalid: true,
    errorPlacement: function (error, element) {
      error.appendTo(element.closest('.form-inline'));
    },
    highlight: function (element) {
      $(element).closest('.form-group').addClass('has-error');
    },
    unhighlight: function (element) {
      $(element).closest('.form-group').removeClass('has-error');
    },
    submitHandler: function (form) {
      updateCounter(form.type.value, form.phrase.value, '#counterDisplay');
    },
  });

  form.on('input', 'input', function (e) {
    const input = $(e.target);
    let value = input.val();
    value = value.replace(/[^A-Za-zżźćńółęąśŻŹĆĄŚĘŁÓŃ0-9._@-]+/g, '').trim();
    input.val(value);
  });

  function updateCounter(type, phrase, selector) {
    if (!$(selector).length) return;
    const counter = $(selector);
    const url = 'ajax-handler.php?action=count_records';

    if (typeof type !== 'string' || typeof phrase !== 'string') {
      console.error('[type] i [phrase] muszą być ciągami znaków');
      return;
    }

    const validType = ['first_name', 'last_name', 'email'].includes(type);
    const validPhrase = phrase.replace(/[^A-Za-zżźćńółęąśŻŹĆĄŚĘŁÓŃ0-9._@-]+/g, '').trim();

    if (!validType || !validPhrase) {
      console.error('[type] i [phrase] są nieprawidłowe');
      return;
    }
    const params = `&type=${type}&phrase=${validPhrase}`;

    $.ajax({
      url: url + params,
      method: 'GET',
      success(response) {
        if (response.success) {
          counter.text(response.data);
        } else {
          console.error(response.message);
        }
      },
      error(error) {
        console.error(error);
      },
    });
  }
});
