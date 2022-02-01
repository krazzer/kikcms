$(function () {
    $('.datatable').on('mouseup', '.table .actions .link', function () {
        var emailAddress        = $(this).parent().parent().parent().find('[data-column="email"]').text().trim();
        var protocolAndHostname = window.location.protocol + '//' + window.location.hostname;

        if(window.location.port && window.location.port !== 80 && window.location.port !== 443){
            protocolAndHostname += ':' + window.location.port;
        }

        alert(protocolAndHostname + '/cms/login/activate?email=' + emailAddress);
    });
});