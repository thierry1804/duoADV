$('.expense-paid, .sale-received').click(function() {
    let cible = $(this);
    let cog = $(this).closest('td').find('svg');
    let url = cible.data('path');

    cible.addClass('hidden');
    cog.removeClass('hidden');
    $.ajax({
        url: url,
        type: 'POST',
        success: function(data) {
            cible.removeClass('hidden');
            cog.addClass('hidden');
        },
        error: function(data) {
            cible.removeClass('hidden');
            cog.addClass('hidden');
        }
    });
});

$('#dashboard-sales tr[id^="sale-"]').on('click', function() {
    let id = $(this).attr('id');

    $('tr#' + id + ' svg').toggleClass('rotate-90');
    $('tr:not("#' + id + '") svg.rotate-90').removeClass('rotate-90');

    $('tr#' + id).toggleClass('border-2 border-sky-300');
    $('tr:not("#' + id + '")').removeClass('border-2 border-sky-300');

    $('tr#detail-' + id).toggleClass('hidden border-2 border-sky-300');
    $('tr[id^="detail-"]:not("#detail-' + id + '")').addClass('hidden').removeClass('border-2 border-sky-300');
});

if ($('button[name=save]').length) {
    $('button[name=save]').on('click', function() {
        let cible = $(this);
        let url = $(this).data('path');
        let bankId = $(this).closest('tr').find('input[name=bankAccount]').val();
        let operationDate = $(this).closest('tr').find('input[name=operationDate]').val();
        let lebel = $(this).closest('tr').find('input[name=label]').val();
        let credit = $(this).closest('tr').find('input[name=credit]').val();
        let debit = $(this).closest('tr').find('input[name=debit]').val();
        let data = 'operationDate=' + operationDate + '&label=' + lebel + '&credit=' + credit + '&debit=' + debit + '&bankAccount=' + bankId;

        cible.addClass('hidden');
        cible.closest('tr').find('svg.animate-spin').removeClass('hidden');

        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            success: function(response) {
                cible.removeClass('hidden');
                cible.closest('tr').find('svg.animate-spin').addClass('hidden');
                let res = $(response);
                cible.closest('tbody').append(res);

                let totalCredit = 0;
                $('td.credit').map(function() {
                    let credit = parseFloat($(this).text().replace(' ', '').replace(',', '.'));
                    totalCredit += credit;
                });
                $('#totalCredit').text((totalCredit).toLocaleString(undefined, { minimumFractionDigits: 2 }));

                let totalDebit = 0;
                $('td.debit').map(function() {
                    let debit = parseFloat($(this).text().replace(' ', '').replace(',', '.'));
                    totalDebit += debit;
                });
                $('#totalDebit').text((totalDebit).toLocaleString(undefined, { minimumFractionDigits: 2 }));

                $('#solde').text((totalCredit - totalDebit).toLocaleString(undefined, { minimumFractionDigits: 2 }));

                cible.closest('tr').find('input[name=label]').val('');
                cible.closest('tr').find('input[name=credit]').val(0);
                cible.closest('tr').find('input[name=debit]').val(0);
            },
            error: function(data) {
                cible.removeClass('hidden');
                cible.closest('tr').find('svg.animate-spin').addClass('hidden');
                alert(data);
            }
        });
    });
}