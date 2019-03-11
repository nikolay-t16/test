<?php
require_once './App.php';
?>
<!DOCTYPE html>
<html>
<head>
	<title>Тестовое задание</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<script type="text/javascript" src="jquery.min.1.9.1.js"></script>
</head>
<body>
<form id="form">
	E-mail:<br>
	<input type="text" name="email" pattern="<?= App::EMAIL_PATERN ?>" required=""/><br>
	Имя:<br>
	<input type="text" name="name" required=""/><br>
	Отзыв:<br>
	<textarea name="text" required=""></textarea><br>
	<input type="submit" value="отправить">
</form>
<div id="reviews">
	<?php foreach (App::GetReviews() as $r) { ?>
		<p><?= $r["date"] ?></p>
		<p>E-mail : <?= $r["email"] ?></p>
		<p>Имя : <?= $r["name"] ?></p>
		<p>Текст : <?= $r["text"] ?></p>
		<hr/>
	<?php } ?>
</div>
<script type="text/javascript">
	$(document).on('submit', '#form', function (e) {
		e.preventDefault();
		var form = $(this);
		$.ajax({
			type: "POST",
			url: '/form.php',
			data: form.serialize(),
			dataType: "json",
			success: function (data) {
				if (data.success) {
					var review = "<p>" + data.data.date + "</p>" +
						"<p>E-mail : " + data.data.email + "</p>" +
						"<p>Имя : " + data.data.name + "</p>" +
						"<p>Текст : " + data.data.text + "</p>" +
						"<hr/>";
					$("#reviews").prepend(review);
					alert("Ваш отзыв успешно добавлен");
				} else {
					alert("Произошла ошибка");
				}
			},
			error: function () {
				alert("Произошла ошибка");
			}
		});
	});
</script>
</body>
</html>
