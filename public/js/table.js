$(document).ready(function () {
  if (typeof getClientsTable !== 'function') {
    console.error('Funkcja getClientsTable nie została zdefiniowana.');
    return;
  }

  getClientsTable('first_name', 'ASC');

  function getClientsTable(sortBy, sortOrder) {
    const table = $('#clientsTable');
    const loading = $('#loadingTable');

    if (table.length === 0 || loading.length === 0) {
      console.error('Elementy #clientsTable lub #loadingTable nie zostały znalezione.');
      return;
    }

    table.empty();
    loading.removeClass('hide').addClass('show');

    const url = 'ajax-handler.php?action=get_clients';
    if (typeof sortBy !== 'string' || typeof sortOrder !== 'string') {
      console.error('[sortBy] i [sortOrder] muszą być ciągami znaków.');
    } else {
      const validSortBy = ['first_name', 'last_name', 'email', 'phone', 'client_no'].includes(sortBy);
      const validSortOrder = ['ASC', 'DESC'].includes(sortOrder);

      if (!validSortBy || !validSortOrder) {
        console.error('[sortBy] i [sortOrder] są nieprawidłowe.');
      } else {
        const params = `&sort_by=${sortBy}&sort_order=${sortOrder}`;
        $.ajax({
          type: 'GET',
          url: url + params,
          dataType: 'json',
          success: function (response) {
            if (!response || !response.data || !Array.isArray(response.data)) {
              console.error('Odpowiedź była nieprawidłowa.');
              return;
            }

            $.each(response.data, function (index, client) {
              table.append(
                '<tr><td>' +
                  client.first_name +
                  '</td><td>' +
                  client.last_name +
                  '</td><td>' +
                  client.email +
                  '</td><td>' +
                  client.phone +
                  '</td><td>' +
                  client.client_no +
                  '</td><td>' +
                  client.choose +
                  '</td><td>' +
                  client.bank_account +
                  '</td><td>' +
                  (client.agreement_gdpr ? 'Tak' : 'Nie') +
                  '</td><td>' +
                  (client.agreement_terms ? 'Tak' : 'Nie') +
                  '</td><td>' +
                  (client.agreement_ads ? 'Tak' : 'Nie') +
                  '</td></tr>'
              );
            });
            loading.removeClass('show').addClass('hide');
          },
          error: function (xhr, status, error) {
            console.error(error);
            loading.text('Ładowanie danych nie powiodło się.');
          },
        });
      }
    }
  }

  $('th[data-sort]').on('click', function () {
    const th = $(this);
    const sortBy = th.data('sort');
    const orderBy = th.data('order') === 'ASC' ? 'DESC' : 'ASC';
    th.data('sortable', true).addClass('active').siblings().removeClass('active').data('sortable', false);
    th.data('order', orderBy);
    getClientsTable(sortBy, orderBy);
  });
});
