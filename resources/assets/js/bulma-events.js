$(document).ready(function() {
    $('.burger').click(function() {
        var target = $(this).data('target');
        var $target = $(document).find('#'+target);
        $(this).toggleClass('is-active');
        $target.toggleClass('is-active');
    });
});
