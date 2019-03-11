<?php

class App {

	/**
	 * Соль для xor шифрования
	 */
	const XOR_STRING_SALT = "zyj*e8u{qfZaL*YrQSRp4AN9w";

	/**
	 * Патерн для валидации email
	 */
	const EMAIL_PATERN = "[a-zA-Z0-9.-_]{1,}@[a-zA-Z.-]{2,}[.]{1}[a-zA-Z]{2,}";

	/**
	 * Формат для вывода даты
	 */
	const DATE_TIME_FORMAT = "d.m.Y H : i : s";

	/**
	 * @var PDO
	 */
	protected static $Connect;

	const DB_HOST     = "localhost";
	const DB_NAME     = "test";
	const DB_USER     = "root";
	const DB_PASSWORD = "";

	/**
	 * Таблица с email отзывов
	 */
	const DB_EMAIL = "reviews_email";

	/**
	 * Таблица с данными отзывов
	 */
	const DB_DATA = "reviews_data";

	/**
	 * @return PDO
	 */
	protected static function Db() {
		if (!self::$Connect) {
			$connect_str = "mysql:host=" . self::DB_HOST . ";dbname=" . self::DB_NAME . ";charset=utf8";
			self::$Connect = new PDO($connect_str, self::DB_USER, self::DB_PASSWORD);
		}
		return self::$Connect;
	}

	/**
	 * Xor шифрование/дешифрование строки
	 * @param string $string
	 * @return string
	 */
	public static function XorString($string) {
		$key = self::XOR_STRING_SALT;
		$modulus = mb_strlen($key);
		$outText = "";
		for ($i = 0; $i < mb_strlen($string); $i++)
			$outText .= $string{$i} ^ $key{$i % $modulus};

		return $outText;
	}

	/**
	 * @param string $email
	 * @return bool
	 */
	public static function ValidateEmail($email) {
		return preg_match("/" . self::EMAIL_PATERN . "/", $email) > 0;
	}

	/**
	 * Список отзывов
	 * @return array
	 */
	public static function GetReviews() {
		$sql = "SELECT * FROM `" . self::DB_DATA . "` as d JOIN " . self::DB_EMAIL . " as e ON e.id = d.id_email ORDER BY d.id DESC";
		$sth = self::Db()->prepare($sql);
		try {
			$sth->execute();
			$data = $sth->fetchAll();
			if (!empty($data)) {
				foreach ($data as &$d) {
					$d["email"] = self::XorString($d["email"]);
					$d["name"]  = htmlspecialchars($d["name"]);
					$d["text"]  = htmlspecialchars($d["text"]);
					$date       = new DateTime($d["date"]);
					$d["date"]  = $date->format(self::DATE_TIME_FORMAT);
				}
				return $data;
			}
		} catch (PDOException $e) {
			return [];
		}
		return [];
	}

	/**
	 * @param string $email
	 * @param string $name
	 * @param string $text
	 */
	public static function AddReview($email, $name, $text) {
		$email = self::XorString($email);
		$id = self::GetEmailId($email);
		if ($id === 0) {
			$id = self::AddEmail($email);
		}
		$id_data = self::AddData($id, $name, $text);
		return $id !== 0 && $id_data !== 0;
	}

	/**
	 * @param string $email
	 * @return int
	 */
	public static function AddEmail($email) {
		$sql = "INSERT INTO " . self::DB_EMAIL . " (email) VALUES (:email)";
		$query = self::Db()->prepare($sql);
		try {
			$query->execute(["email" => $email]);
			return self::Db()->lastInsertId();
		} catch (PDOException $e) {
			return 0;
		}
	}

	/**
	 * @param int $idEmail
	 * @param string $name
	 * @param string $text
	 */
	public static function AddData($idEmail, $name, $text) {
		$sql = "INSERT INTO " . self::DB_DATA . " (id_email, name, text) VALUES (:id_email, :name, :text)";
		$query = self::Db()->prepare($sql);
		try {
			$query->execute([
					"id_email" => $idEmail,
					"name"     => $name,
					"text"     => $text
			]);
			return self::Db()->lastInsertId();
		} catch (PDOException $e) {
			return 0;
		}
	}

	/**
	 * @param string $email
	 * @return int
	 */
	public static function GetEmailId($email) {
		$sql = "SELECT id FROM `" . self::DB_EMAIL . "` WHERE `email` = :email limit 1";
		$sth = self::Db()->prepare($sql);
		try {
			$sth->execute(array(':email' => $email));
			$data = $sth->fetch();
			if (!empty($data))
				return $data["id"];
		} catch (PDOException $e) {
			return 0;
		}
		return 0;
	}

}
