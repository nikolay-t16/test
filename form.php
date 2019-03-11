<?php

require "./App.php";
if ($_SERVER["REQUEST_METHOD"] == "POST" &&
	!empty($_POST["email"]) &&
	App::ValidateEmail($_POST["email"]) &&
	!empty($_POST["name"]) &&
	!empty($_POST["text"])
) {
	if (App::AddReview($_POST["email"], $_POST["name"], $_POST["text"])) {
		die(json_encode([
			"success" => 1,
			"data"    => [
				"email" => $_POST["email"],
				"name"  => htmlspecialchars($_POST["name"]),
				"text"  => htmlspecialchars($_POST["text"]),
				"date"  => date(App::DATE_TIME_FORMAT)
			]
		]));
	} else {
		die(json_encode(["success" => 0]));
	}
}
