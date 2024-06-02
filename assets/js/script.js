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