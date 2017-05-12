$(function () {
    $('.action.link').click(function () {
        var emailAddress        = $(this).prev().prev().prev().text().trim();
        var protocolAndHostname = window.location.protocol + '//' + window.location.hostname;

        alert(protocolAndHostname + '/cms/login/reset?email=' + emailAddress);
    });
});