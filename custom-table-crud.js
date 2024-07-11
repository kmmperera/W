jQuery(document).ready(function($) {
    $('.ceymulticall-open-modal').on('click', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var email = $(this).data('email');

        $('#ceymulticall-modal-id').val(id);
        $('#ceymulticall-modal-name').val(name);
        $('#ceymulticall-modal-email').val(email);

        $('#ceymulticall-update-modal').dialog({
            modal: true,
            width: 400
        });
    });
});
