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

            $filePicker.on("pick", '.file', function () {
                var $file          = $(this);
                var selectedFileId = $file.attr('data-id');

                self.actionPickFile($field, selectedFileId);

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

    actionPreview: function ($field, fileId, result) {
        var $preview      = $field.find('.preview');
        var $previewThumb = $field.find('.preview .thumb');
        var $buttonPick   = $field.find('.buttons .pick');
        var $buttonDelete = $field.find('.buttons .delete');

        if (result.dimensions) {
            $previewThumb.css('width', result.dimensions[0] / 2);
            $previewThumb.css('height', result.dimensions[1] / 2);
        } else {
            $previewThumb.css('width', 'auto');
            $previewThumb.css('height', 'auto');
        }

        $preview.removeClass('hidden');
        $previewThumb.html(result.preview);
        $field.find(' > input[type=hidden].fileId').val(fileId);

        $buttonPick.addClass('hidden');
        $buttonDelete.removeClass('hidden');
    },

    actionPickFile: function ($field, fileId) {
        var self = this;

        KikCMS.action('/cms/webform/getFilePreview', {fileId: fileId}, function (result) {
            self.actionPreview($field, fileId, result);
        });
    },

    getWebForm: function () {
        return $('#' + this.renderableInstance);
    },

    init: function () {
        this.initAutocompleteFields();
        this.initDateFields();
        this.initFileFields();
        this.initWysiwyg();
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
                $field.typeahead({
                    items: 10,
                    source: data
                });
            });
        });
    },

    initDateFields: function () {
        this.getWebForm().find('.type-date input[type=date]').each(function () {
            var $field = $(this);

            $field.datetimepicker({
                format: $field.attr('data-format')
            });
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
                $field.find('input[type=hidden]').val('');

                $pickButton.removeClass('hidden');
                $deleteButton.addClass('hidden');
                $previewButton.find('img').remove();
                $previewButton.addClass('hidden');
            });

            $pickAbles.click(function () {
                if ($filePicker.find('.finder').length >= 1) {
                    $filePicker.slideToggle();
                    $uploadButton.toggleClass('disabled');
                    return;
                }

                self.actionGetFinder($field);
            });
        });
    },

    initTinyMCE: function () {
        tinymce.init({
            selector: this.getWysiwygSelector(),
            setup: function (editor) {
                editor.on('change', function () {
                    tinymce.triggerSave();
                });
            },
            language_url: '/cmsassets/js/vendor/tinymce/' + KikCMS.tl('system.langCode') + '.js',
            language: KikCMS.tl('system.langCode'),
            theme: 'modern',
            relative_urls : false,
            remove_script_host : true,
            document_base_url : KikCMS.baseUri,
            plugins: [
                'advlist autolink lists link image charmap print preview hr anchor pagebreak searchreplace visualblocks',
                'visualchars code insertdatetime media nonbreaking save table contextmenu directionality template paste',
                'textcolor colorpicker textpattern imagetools codesample toc'
            ],
            toolbar1: 'undo redo | insert | styleselect | bold italic | alignleft aligncenter alignright alignjustify' +
            ' | bullist numlist outdent indent | link image | forecolor backcolor | codesample',
            image_advtab: true,
            content_css: ['/cmsassets/css/tinymce/content.css'],
            link_list: this.getLinkListUrl()
        });
    },

    initUploader: function ($field) {
        var self = this;

        var uploader = new FinderFileUploader({
            $container: $field,
            action: '/cms/webform/uploadAndPreview',
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

    getLinkListUrl: function () {
        var linkListUrl = '/cms/getTinyMceLinks/';

        if( ! this.parent) {
            return linkListUrl;
        }

        var languageCode = this.parent.getLanguageCode();

        if( ! languageCode) {
            return linkListUrl;
        }

        return linkListUrl + this.parent.getLanguageCode() + '/';
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