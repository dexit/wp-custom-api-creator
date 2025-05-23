jQuery(document).ready(function ($) {
	var sectionTemplate = $("#api_sections .api-section").first().clone();
	var sectionCount = $("#api_sections .api-section").length;

	// Add new section
	$("#add_section").on("click", function () {
		var newSection = sectionTemplate.clone();
		sectionCount++;

		newSection.find("h4").text("Section " + sectionCount);
		newSection.find("select, input").each(function () {
			var name = $(this).attr("name");
			if (name) {
				$(this).attr(
					"name",
					name.replace("[0]", "[" + (sectionCount - 1) + "]")
				);
			}
		});

		// Clear the section name field
		newSection.find('input[name$="[name]"]').val(sectionCount);

		newSection.attr("data-index", sectionCount - 1);
		newSection.find(".remove-section").show();

		$("#api_sections").append(newSection);
		resetRemoveButtons();
	});

	// Remove section
	$("#api_sections").on("click", ".remove-section", function () {
		$(this).closest(".api-section").remove();
		resetSectionIndexes();
		resetRemoveButtons();
	});

	// Reset section indexes
	function resetSectionIndexes() {
		$("#api_sections .api-section").each(function (index) {
			$(this)
				.find("h4")
				.text("Section " + (index + 1));
			$(this).attr("data-index", index);
			$(this)
				.find("select, input")
				.each(function () {
					var name = $(this).attr("name");
					if (name) {
						$(this).attr("name", name.replace(/\[\d+\]/, "[" + index + "]"));
					}
				});
		});
		sectionCount = $("#api_sections .api-section").length;
	}

	// Reset remove buttons
	function resetRemoveButtons() {
		var sections = $("#api_sections .api-section");
		sections.find(".remove-section").show();
		if (sections.length === 1) {
			sections.first().find(".remove-section").hide();
		}
	}

	// Handle access type switch
	$('input[name="custom_api_access_type"]').on("change", function () {
		if ($(this).val() === "private") {
			$("#custom_api_roles_row").show();
		} else {
			$("#custom_api_roles_row").hide();
		}
	});

	// Initialize
	resetRemoveButtons();

	// Initialize CodeMirror for the Handler Function field
	if (typeof wp !== 'undefined' && wp.codeEditor) {
		var editorSettings = wp.codeEditor.defaultSettings ? _.clone(wp.codeEditor.defaultSettings) : {};
		editorSettings.codemirror = _.extend(
			{},
			editorSettings.codemirror,
			{
				mode: 'php',
				indentUnit: 2,
				tabSize: 2
			}
		);
		var editor = wp.codeEditor.initialize($('#cac_plugin_action_function'), editorSettings);
	}

	// Event listeners for HTTP request and response configuration fields
	$('#cac_plugin_request_config, #cac_plugin_response_config').on('change', function () {
		try {
			JSON.parse($(this).val());
			$(this).removeClass('error');
		} catch (e) {
			$(this).addClass('error');
		}
	});
});
