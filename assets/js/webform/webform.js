var WebForm = Class.extend({
    renderableInstance: null,
    renderableClass: null,
    parent: null,

    /**
     * @param $field
     * @param fileId
     * @param result
     * @param onComplete
     */
    actionPreview: function ($field, fileId, result, onComplete) {
        var $preview      = $field.find('.preview');
        var $previewThumb = $field.find('.preview .thumb');
        var $buttonPick   = $field.find('.buttons .pick');
        var $buttonDelete = $field.find('.buttons .delete');
        var $fileName     = $field.find('.filename');

        if (result.dimensions) {
            $previewThumb.css('width', result.dimensions[0] / 2);
            $previewThumb.css('height', result.dimensions[1] / 2);
        } else {
            $previewThumb.css('width', 'auto');
            $previewThumb.css('height', 'auto');
        }

        $preview.removeClass('hidden');
        $previewThumb.html(result.preview);
        $fileName.html('(' + result.name + ')');
        $field.find(' > input[type=hidden].fileId').val(fileId);

        $buttonPick.addClass('hidden');
        $buttonDelete.removeClass('hidden');

        if (typeof onComplete !== "undefined") {
            onComplete();
        }
    },

    /**
     * @param $field
     */
    createFilePicker: function ($field) {
        var self = this;

        var onPickFile = function ($file) {
            self.onPickFile($file, $field);
        };

        return new FilePicker(this.renderableInstance, this.getWebForm(), onPickFile);
    },

    /**
     * Initialize the WebForm
     */
    init: function () {
        this.initAutocompleteFields();
        this.initDateFields();
        this.initFileFields();
        this.initWysiwyg();
        this.initPopovers();
        this.initCsrf();
        this.initTranslate();
    },

    /**
     * Initialize autocomplete fields
     */
    initAutocompleteFields: function () {
        var self     = this;
        var $webForm = this.getWebForm();

        $webForm.find('.autocomplete').each(function () {
            var $field   = $(this);
            var fieldKey = $field.attr('data-field-key');
            var route    = $field.attr('data-route');

            var params = {
                field: fieldKey,
                renderableInstance: self.renderableInstance,
                renderableClass: self.renderableClass
            };

            KikCMS.action(route, params, function (data) {
                self.initAutocompleteData(data, $field);
            });
        });
    },

    /**
     * @param data
     * @param $field
     */
    initAutocompleteData: function (data, $field) {
        var substringMatcher = function (strs) {
            return function findMatches(q, cb) {
                var matches     = [];
                var substrRegex = new RegExp(q, 'i');

                $.each(strs, function (i, str) {
                    if (substrRegex.test(str)) {
                        matches.push(str);
                    }
                });

                cb(matches);
            };
        };

        $field.typeahead({hint: true, highlight: true}, {
            limit: 10,
            source: substringMatcher(data)
        });
    },

    initCsrf: function () {
        var self = this;

        setTimeout(function () {
            KikCMS.action('/webform/token/', {}, function (result) {
                var key   = result[0];
                var token = result[1];

                var tokenField = '<input class="webform-token" type="hidden" name="' + key + '" value="' + token + '" />';
                var $form      = self.getWebForm().find('form');

                if ( ! $form.find('input[name=' + key + ']').length) {
                    $form.prepend(tokenField);
                }
            });
        }, 1500);
    },

    /**
     * Initialize date fields
     */
    initDateFields: function () {
        this.getWebForm().find('.type-date input').each(function () {
            var $field = $(this);

            $field.datetimepicker({
                format: $field.attr('data-format'),
                locale: moment.locale(),
                useCurrent: false
            });

            if ($field.attr('data-default-date')) {
                var value = $field.val();

                // set default date
                var defaultDate = moment($field.attr('data-default-date'), $field.attr('data-format'));
                $field.datetimepicker('defaultDate', defaultDate);

                // setting the default date also sets the value, so clear if it was empty before
                if ( ! value) {
                    $field.val('');
                }
            }

            if ($field.attr('data-viewmode')) {
                $field.datetimepicker('viewMode', $field.attr('data-viewmode'));
            }
        });
    },

    /**
     * Initialize file fields
     */
    initFileFields: function () {
        var self = this;

        this.getWebForm().find('.type-file').each(function () {
            var $field         = $(this);
            var $filePicker    = $field.find('.file-picker');
            var $uploadButton  = $field.find('.btn.upload');
            var $deleteButton  = $field.find('.btn.delete');
            var $pickButton    = $field.find('.btn.pick');
            var $previewButton = $field.find('.btn.preview');
            var $pickAbles     = $field.find('.btn.pick, .btn.preview');

            self.initUploader($field);

            $deleteButton.click(function () {
                $field.find('.filename').html('');
                $field.find(' > input[type=hidden].fileId').val('');

                $pickButton.removeClass('hidden');
                $deleteButton.addClass('hidden');
                $previewButton.find('img').remove();
                $previewButton.addClass('hidden');
            });

            $pickAbles.click(function () {
                if ($(this).attr('data-finder') == 0) {
                    return;
                }

                if ($filePicker.find('.finder').length >= 1) {
                    $filePicker.slideToggle();
                    $uploadButton.toggleClass('disabled');
                    return;
                }

                self.filePicker = self.createFilePicker($field);
                self.filePicker.open();
            });
        });
    },

    /**
     * Initialize popovers
     */
    initPopovers: function () {
        this.getWebForm().find('[data-toggle="popover"]').each(function () {
            var content = $(this).attr('data-content');

            $(this).popover({
                placement: 'auto bottom',
                html: true,
                content: content,
                container: 'body'
            });
        });
    },

    /**
     * Initialize TinyMCE
     */
    initTinyMCE: function () {
        var self = this;

        tinymce.init({
            selector: this.getWysiwygSelector(),
            setup: function (editor) {
                editor.on('change', function () {
                    tinymce.triggerSave();
                });
            },
            language: KikCMS.tl('system.langCode'),
            relative_urls: false,
            remove_script_host: true,
            branding: false,
            elementpath: false,
            document_base_url: KikCMS.baseUri,
            toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | outdent indent | bullist numlist',
            plugins: [
                'advlist autolink lists link image charmap print preview hr anchor pagebreak searchreplace visualblocks',
                'visualchars code insertdatetime media nonbreaking save table directionality template paste',
                'textpattern codesample'
            ],
            image_advtab: true,
            content_css: ['/cmsassets/css/tinymce_content.css'],
            link_list: this.getLinkListUrl(),
            file_picker_callback: function (callback) {
                self.getFilePicker(callback);
            }
        });
    },

    initTranslate: function () {
        this.getWebForm().find('a.translate').click(function () {
            let content = $(this).data('value');
            navigator.clipboard.writeText(content).then(() => {
                $(this).html('✔︎');
                setTimeout(() => $(this).html('#'), 500);
            });
        });
    },

    /**
     * Init uploader for direct upload file fields
     * @param $field
     */
    initUploader: function ($field) {
        var self = this;

        var $tokenField = (this.getWebForm().find('.webform-token'));

        var uploader = new FileUploader({
            $container: $field,
            action: '/webform/uploadAndPreview',
            addParametersBeforeUpload: function (formData) {
                formData.append('folderId', $field.find('.btn.upload').attr('data-folder-id'));
                formData.append('renderableInstance', self.renderableInstance);
                formData.append('renderableClass', self.renderableClass);
                formData.append('tokenKey', $tokenField.attr('name'));
                formData.append('tokenValue', $tokenField.val());
                return formData;
            },
            onSuccess: function (result) {
                if (result.fileId) {
                    self.actionPreview($field, result.fileId, result);
                }
            }
        });

        uploader.init();
    },

    /**
     * Init TinyMCE fields
     */
    initWysiwyg: function () {
        var self = this;

        let initTinyMce = function () {
            if (typeof tinymce == 'undefined') {
                var baseUrl = "https://cdn.tiny.cloud/1/" + KikCMS.tinyMceApiKey + "/tinymce/5";
                $.getScript(baseUrl + '/tinymce.min.js', function () {
                    window.tinymce.dom.Event.domLoaded = true;
                    tinymce.baseURL                    = baseUrl;
                    tinymce.suffix                     = ".min";

                    self.initTinyMCE();
                });
            } else {
                self.initTinyMCE();
            }
        };

        $(this.getWysiwygSelector()).each(function () {
            $(this).click(function () {
                initTinyMce();
            })
        });

        // only enable tinymce on click
        if (KikCMS.tinyMceClick) {
            return;
        }

        if ($(this.getWysiwygSelector()).length == 0) {
            return;
        }

        initTinyMce();
    },

    /**
     * Get the filepicker for TinyMCE
     * @param callback
     */
    getFilePicker: function (callback) {
        var callBackAction = function ($file) {
            var fileId = $file.attr('data-id');

            KikCMS.action('/cms/file/url/' + fileId, {}, function (result) {
                callback(result.url, {alt: $file.find('.name span').text()});
                window.close();
            });
        };

        var windowHeight = this.getWindowHeight() < 768 ? this.getWindowHeight() - 130 : 768;

        var window = tinymce.activeEditor.windowManager.openUrl({
            title: 'Image Picker',
            url: '/cms/filePicker',
            width: 952,
            height: windowHeight,
            buttons: [{
                type: 'cancel',
                text: 'Close',
                onclick: 'close'
            }, {
                text: 'Insert',
                type: 'custom',
                onclick: function () {
                    var $filePicker = $(window.$el).find('iframe')[0].contentWindow.$('.filePicker');

                    var $file = $filePicker.find('.file.selected');

                    if ( ! $file.length) {
                        return false;
                    }

                    callBackAction($file);
                }
            }]
        });

        var $iframe = $('.tox-navobj iframe');

        $iframe.on('load', function () {
            var $filePicker = this.contentWindow.$('.filePicker');

            $filePicker.on("pick", '.file', function () {
                callBackAction($(this));
            });
        });
    },

    /**
     * Get URL for TinyMCE links
     * @return {string}
     */
    getLinkListUrl: function () {
        var linkListUrl = '/cms/getTinyMceLinks/';

        if ( ! this.parent) {
            return linkListUrl;
        }

        var languageCode = this.parent.getWindowLanguageCode();

        if ( ! languageCode) {
            return linkListUrl;
        }

        return linkListUrl + this.parent.getWindowLanguageCode() + '/';
    },

    /**
     * Get WebForm jQuery object
     * @return {jQuery|HTMLElement}
     */
    getWebForm: function () {
        return $('[data-instance=' + this.renderableInstance + ']');
    },

    /**
     * Get window height
     * @return int
     */
    getWindowHeight: function () {
        return $(window).height();
    },

    /**
     * Get query for Wysiwyg fields
     * @return {string}
     */
    getWysiwygSelector: function () {
        var webformId = this.getWebForm().attr("id");
        return '#' + webformId + ' textarea.wysiwyg';
    },

    /**
     * @param $field
     * @return {*}
     */
    getUploadButtonForFileField: function ($field) {
        return $field.find('.btn.upload');
    },

    /**
     * Action when a file is picked
     * @param $file
     * @param $field
     */
    onPickFile: function ($file, $field) {
        $file.removeClass('selected');
        this.pickFile($file.data('id'), $field);
    },

    /**
     * @param fileId
     * @param $field
     */
    pickFile: function (fileId, $field) {
        var self          = this;
        var $uploadButton = this.getUploadButtonForFileField($field);

        $uploadButton.removeClass('disabled');

        KikCMS.action('/cms/webform/filepreview/' + fileId, {}, function (result) {
            self.actionPreview($field, fileId, result);
        });
    },

    /**
     * Remove extention from a filename
     *
     * @param filename
     * @return {*}
     */
    removeExtension: function (filename) {
        return filename.replace(/\.[^/.]+$/, "");
    }
});