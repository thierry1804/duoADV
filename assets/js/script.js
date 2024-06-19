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
