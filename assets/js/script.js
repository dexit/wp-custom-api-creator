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
});
