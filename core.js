$(function () {
	$(document).on("submit","form[name=shorten]", function() {
		$.post($(this).attr("action"), $(this).serialize(), function (json) {
			// Handle response from shorten.php
			if (json.error !== false) {
				$("#response").text(json.error);
			} else {
				let url = window.location.href + json.code;
				$("#response").html("Your new link! <a href='" + url + "'>" + url + "</a>");
			}
		}, "json");
		$("#url").val(""); // Clear input since event is cancelled
		return false;
	});
});