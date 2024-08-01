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

window.addEventListener('DOMContentLoaded', (event) => {
    const now = new Date();
    const dayOfWeek = now.getDay();
    const numDay = now.getDate();

    const startOfWeek = new Date(now);
    // If it's Saturday or Sunday, set to previous Saturday
    if (dayOfWeek === 0 || dayOfWeek === 6) {
        startOfWeek.setDate(numDay - (dayOfWeek === 0 ? 1 : 6));
    } else {
        // Otherwise, set to next Friday
        startOfWeek.setDate(numDay + (5 - dayOfWeek));
    }

    const firstDayOfYear = new Date(startOfWeek.getFullYear(), 0, 1);
    const pastDaysOfYear = (startOfWeek - firstDayOfYear) / 86400000;
    const weekNum = Math.ceil((pastDaysOfYear + firstDayOfYear.getDay() + 1) / 7);
    const weekInput = document.getElementById('week');

    if (weekInput) {
        //weekInput.value = `${startOfWeek.getFullYear()}-W${weekNum < 10 ? '0' + weekNum : weekNum}`;
    }
});

window.addEventListener('DOMContentLoaded', (event) => {
    const weekInput = document.getElementById('week');
    if (weekInput) {
        weekInput.addEventListener('change', function() {
            this.form.submit();
        });
    }
});

document.addEventListener('DOMContentLoaded', (event) => {
    // Select all the buttons
    const buttons = document.querySelectorAll('button.backToStock');

    // Loop over all buttons
    buttons.forEach((button) => {
        // Add an event listener to each button
        button.addEventListener('click', (event) => {
            // Prevent the default action of the button
            event.preventDefault();

            // Select the input field that is in the same container as the button
            const input = event.target.parentNode.parentNode.querySelector('input.returnedQty');
            const saleId = event.target.parentNode.parentNode.querySelector('input.saleId');
            //get the path
            const btn = event.target.parentNode.parentNode.querySelector('button.backToStock');
            let path = btn.dataset.path;

            // Check if the value of the input field is greater than 0
            if (parseInt(input.value) > 0) {
                // mark the button as disabled
                event.target.parentNode.disabled = true;
                // mark the input field as disabled
                input.disabled = true;
                // spin the button
                event.target.classList.add('hidden');
                event.target.parentNode.parentNode.querySelector('svg.waiting').classList.remove('hidden');

                // Make an AJAX request
                $.ajax({
                    url: path,
                    type: 'POST',
                    data: {
                        saleId: saleId.value,
                        returnedQty: input.value
                    },
                    success: function(data) {
                        event.target.parentNode.parentNode.querySelector('svg.waiting').classList.add('hidden');
                        event.target.classList.remove('hidden');
                        event.target.parentNode.disabled = false;
                        input.disabled = false;
                        if (data.error) {
                            alert(data.error);
                            input.value = 0;
                        }
                        else {
                            //refresh the page
                            location.reload();
                        }
                    },
                    error: function(data) {
                        // If the request fails, remove the disabled attribute from the button and input field
                        event.target.parentNode.disabled = false;
                        input.disabled = false;
                        event.target.classList.remove('hidden');
                        event.target.parentNode.parentNode.querySelector('svg.waiting').classList.add('hidden');
                    }
                });
            }
        });
    });
});