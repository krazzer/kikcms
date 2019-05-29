var WebForm = Class.extend({
    renderableInstance: null,
    renderableClass: null,
    parent: null,

    actionGetFinder: function ($field) {
        var self              = this;
        var $filePicker       = $field.find('.file-picker');
        var $uploadButton     = $field.find('.btn.upload');
        var $finderPickButton = $filePicker.find('.pick-file');

        KikCMS.action('/cms/webform/getFinder', {}, function (result) {
            $filePicker.find('.finder-container').html(result.finder);
            $filePicker.slideDown();

            $filePicker.on("pick", '.file', function (e, onComplete) {
                var $file          = $(this);
                var selectedFileId = $file.attr('data-id');

                self.actionPickFile($field, selectedFileId, onComplete);

                $filePicker.slideUp(function () {
                    $file.removeClass('selected');
                    $finderPickButton.addClass('disabled');
                });

                $uploadButton.removeClass('disabled');
            });

            $filePicker.on("selectionChange", '.file', function () {
                if ($filePicker.find('.file.selected:not(.folder)').length >= 1) {
                    $finderPickButton.removeClass('disabled');
                } else {
                    $finderPickButton.addClass('disabled');
                }
            });

            $finderPickButton.click(function () {
                if ($finderPickButton.hasClass('disabled')) {
                    return;
                }

                var $file          = $filePicker.find('.file.selected');
                var selectedFileId = $file.attr('data-id');

                self.actionPickFile($field, selectedFileId);

                $filePicker.slideUp(function () {
                    $file.removeClass('selected');
                    $finderPickButton.addClass('disabled');
                });

                $uploadButton.removeClass('disabled');
            });

            $uploadButton.addClass('disabled');
        });
    },

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
     * @param fileId
     * @param onComplete
     */
    actionPickFile: function ($field, fileId, onComplete) {
        var self = this;

        KikCMS.action('/cms/webform/getFilePreview', {fileId: fileId}, function (result) {
            self.actionPreview($field, fileId, result, onComplete);
        });
    },

    getWebForm: function () {
        return $('[data-instance=' + this.renderableInstance + ']');
    },

    init: function () {
        this.initAutocompleteFields();
        this.initDateFields();
        this.initFileFields();
        this.initWysiwyg();
        this.initPopovers();
    },

    initAutocompleteFields: function () {
        var self     = this;
        var $webForm = this.getWebForm();

        $webForm.find('.autocomplete').each(function () {
            var $field   = $(this);
            var fieldKey = $field.attr('data-field-key');
            var route    = $field.attr('data-route');

            KikCMS.action(route, {
                field: fieldKey,
                renderableInstance: self.renderableInstance,
                renderableClass: self.renderableClass
            }, function (data) {
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
            });
        });
    },

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
                if (!value) {
                    $field.val('');
                }
            }
        });
    },

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

            $filePicker.find('.buttons .cancel').click(function () {
                $filePicker.slideUp();
                $uploadButton.removeClass('disabled');
            });

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

                self.actionGetFinder($field);
            });
        });
    },

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

    initTinyMCE: function () {
        var self = this;

        tinymce.init({
            selector: this.getWysiwygSelector(),
            setup: function (editor) {
                editor.on('change', function () {
                    tinymce.triggerSave();
                });
            },
            language_url: '/cmsassets/js/tinymce/' + KikCMS.tl('system.langCode') + '.js',
            language: KikCMS.tl('system.langCode'),
            theme: 'modern',
            relative_urls: false,
            remove_script_host: true,
            document_base_url: KikCMS.baseUri,
            plugins: [
                'advlist autolink lists link image charmap print preview hr anchor pagebreak searchreplace visualblocks',
                'visualchars code insertdatetime media nonbreaking save table contextmenu directionality template paste',
                'textcolor colorpicker textpattern codesample toc'
            ],
            image_advtab: true,
            content_css: ['/cmsassets/css/tinymce/content.css'],
            link_list: this.getLinkListUrl(),
            file_picker_callback: function (callback) {
                self.getFilePicker(callback);
            }
        });
    },

    initUploader: function ($field) {
        var self = this;

        var uploader = new FileUploader({
            $container: $field,
            action: '/cms/webform/uploadAndPreview',
            addParametersBeforeUpload: function (formData) {
                formData.append('folderId', $field.find('.btn.upload').attr('data-folder-id'));
                formData.append('renderableInstance', self.renderableInstance);
                formData.append('renderableClass', self.renderableClass);
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

    initWysiwyg: function () {
        var self = this;

        if ($(this.getWysiwygSelector()).length == 0) {
            return;
        }

        if (typeof tinymce == 'undefined') {
            $.getScript('//cdn.tinymce.com/4/tinymce.min.js', function () {
                window.tinymce.dom.Event.domLoaded = true;
                tinymce.baseURL                    = "//cdn.tinymce.com/4";
                tinymce.suffix                     = ".min";

                self.initTinyMCE();
            });
        } else {
            this.initTinyMCE();
        }
    },

    getFilePicker: function (callback) {
        var callBackAction = function ($file) {
            var fileId = $file.attr('data-id');

            KikCMS.action('/cms/file/url/' + fileId, {}, function (result) {
                callback(result.url, {alt: $file.find('.name span').text()});
                window.close();
            });
        };

        var windowHeight = this.getWindowHeight() < 768 ? this.getWindowHeight() - 130 : 768;

        var window = tinymce.activeEditor.windowManager.open({
            title: 'Image Picker',
            url: '/cms/filePicker',
            width: 952,
            height: windowHeight,
            buttons: [{
                text: 'Insert',
                onclick: function () {
                    var $filePicker = $(window.$el).find('iframe')[0].contentWindow.$('.filePicker');

                    var $file = $filePicker.find('.file.selected');

                    if (!$file.length) {
                        return false;
                    }

                    callBackAction($file);
                }
            }, {
                text: 'Close',
                onclick: 'close'
            }]
        });

        window.on('open', function () {
            var $iframe = $(window.$el).find('iframe');

            $iframe.on('load', function () {
                var $filePicker = this.contentWindow.$('.filePicker');

                $filePicker.on("pick", '.file', function () {
                    callBackAction($(this));
                });
            });
        });
    },

    getLinkListUrl: function () {
        var linkListUrl = '/cms/getTinyMceLinks/';

        if (!this.parent) {
            return linkListUrl;
        }

        var languageCode = this.parent.getWindowLanguageCode();

        if (!languageCode) {
            return linkListUrl;
        }

        return linkListUrl + this.parent.getWindowLanguageCode() + '/';
    },

    /**
     * @return int
     */
    getWindowHeight: function () {
        return $(window).height();
    },

    getWysiwygSelector: function () {
        var webformId = this.getWebForm().attr("id");
        return '#' + webformId + ' textarea.wysiwyg';
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
});