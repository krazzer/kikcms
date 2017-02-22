var KikCmsClass = function () {
};

KikCmsClass.prototype =
{
    translations: {},
    errorMessages: {},
    isDev: false,
    maxFileUploads: null,
    maxFileSize: null,
    maxFileSizeString: null,

    action: function (actionUrl, parameters, onSuccess, onError, xhr) {
        var ajaxCompleted = false;
        var self          = this;
        var retries       = 0;

        setTimeout(function () {
            if (ajaxCompleted == false) {
                KikCMS.showLoader();
            }
        }, 250);

        var ajaxRequestSettings = {
            url: actionUrl,
            type: 'post',
            dataType: 'json',
            xhr: xhr,
            data: parameters,
            success: function (result, responseText, response) {
                ajaxCompleted = true;
                self.hideLoader();

                onSuccess(result, responseText, response);
            },
            error: function (result) {
                // try again on connection failure
                if (result.readyState == 0 && result.status == 0 && retries < 2) {
                    retries++;
                    xmlHttpRequest();
                    return;
                }

                ajaxCompleted = true;
                self.showError(result, onError);
            }
        };

        if (typeof xhr !== 'undefined') {
            ajaxRequestSettings.cache       = false;
            ajaxRequestSettings.contentType = false;
            ajaxRequestSettings.processData = false;
        }

        var xmlHttpRequest = function () {
            $.ajax(ajaxRequestSettings);
        };

        xmlHttpRequest();
    },

    showError: function (result, onError) {
        if (typeof(onError) != 'undefined') {
            onError();
        }

        this.hideLoader();

        var key = this.translations.error[result.status] ? result.status : 'unknown';

        if (this.isDev && result.status != 440) {
            $("#ajaxDebugger").html(result.responseText).show();
        } else {
            alert(this.translations.error[key].title + "\n\n" + this.translations.error[key].description);
        }
    },

    showLoader: function () {
        this.getLoader().addClass('show');
    },

    hideLoader: function () {
        this.getLoader().removeClass('show');
    },

    getLoader: function () {
        return $('#cmsLoader');
    },

    removeExtension: function (filename) {
        return filename.replace(/\.[^/.]+$/, "");
    },

    tl: function (key, params) {
        var translation = this.translations[key];

        $.each(params, function (key, value) {
            translation = translation.replace(new RegExp(':' + key, 'g'), value);
        });

        return translation;
    }
};

var KikCMS = new KikCmsClass();