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

$('.tabs ul li a').on('click', function(e) {
    e.preventDefault();

    let cible = $(this).attr('href');
    $('.tab-content').addClass('hidden');
    $(cible).removeClass('hidden');

    $('.tabs ul li a').prop('class', 'flex items-center justify-center gap-2 px-1 py-3 hover:text-blue-700 text-gray-500 transition-all transform');
    $(this).prop('class', 'flex items-center justify-center gap-2 px-1 py-3 hover:text-blue-700 relative text-blue-700 after:absolute after:left-0 after:bottom-0 after:h-0.5 after:w-full after:bg-blue-700 transition-all transform');
});