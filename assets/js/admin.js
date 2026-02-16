jQuery(document).ready(function ($) {
    // Initialize CodeMirror for the Endpoint Code field
    if (typeof wp !== 'undefined' && wp.codeEditor) {
        var editorSettings = wp.codeEditor.defaultSettings ? _.clone(wp.codeEditor.defaultSettings) : {};
        editorSettings.codemirror = _.extend(
            {},
            editorSettings.codemirror,
            {
                mode: 'php',
                indentUnit: 2,
                tabSize: 2,
                lineNumbers: true,
                matchBrackets: true,
                autoCloseBrackets: true,
                lint: true,
                gutters: ["CodeMirror-lint-markers"]
            }
        );

        if ($('#cac_pro_code').length) {
            wp.codeEditor.initialize($('#cac_pro_code'), editorSettings);
        }

        if ($('#helper_code').length) {
            wp.codeEditor.initialize($('#helper_code'), editorSettings);
        }
    }

    // Handle access type switch
    $('#cac_pro_access').on('change', function () {
        if ($(this).val() === 'private') {
            $('#cac_pro_roles_row').show();
        } else {
            $('#cac_pro_roles_row').hide();
        }
    });

    // JSON validation for params and response configs
    $('textarea[name="cac_pro_params"], textarea[name="cac_pro_response"]').on('change', function () {
        try {
            if ($(this).val().trim() !== '') {
                JSON.parse($(this).val());
            }
            $(this).removeClass('error').css('border-color', '');
        } catch (e) {
            $(this).addClass('error').css('border-color', 'red');
        }
    });
});
