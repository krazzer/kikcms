$(function () {
    $('.datatable').on('click', '.action.link', function () {
        var emailAddress        = $(this).prev().prev().prev().text().trim();
        var protocolAndHostname = window.location.protocol + '//' + window.location.hostname;

        if(window.location.port && window.location.port !== 80 && window.location.port !== 443){
            protocolAndHostname += ':' + window.location.port;
        }

        alert(protocolAndHostname + '/cms/login/activate?email=' + emailAddress);
    });
});