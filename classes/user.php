<?php
$filepath = realpath(dirname(__FILE__));
include_once($filepath . '/../lib/session.php');
include_once($filepath . '/../lib/database.php');
include_once($filepath . '/../helpers/format.php');
include_once($filepath . '/../lib/Exception.php');
include_once($filepath . '/../lib/PHPMailer.php');
include_once($filepath . '/../lib/SMTP.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

?>



<?php
/**
 * 
 */
class user
{
	private $db;
	private $fm;
	public function __construct()
	{
		$this->db = new Database();
		$this->fm = new Format();
	}

	public function login($email, $password)
	{
		$email = $this->fm->validation($email); //call fucntion validation from file Format to check
		$password = $this->fm->validation($password);

		$email = mysqli_real_escape_string($this->db->link, $email);
		$password = mysqli_real_escape_string($this->db->link, $password); //mysqli call 2 variable. (email and link) biến link -> gọi conect db từ file db

		if (empty($email) || empty($password)) {
			$alert = "Email và password không được để trống!";
			return $alert;
		} else {
			$query = "SELECT * FROM users WHERE email = '$email' AND password = '$password' LIMIT 1 ";
			$result = $this->db->select($query);

			if ($result) {
				$value = $result->fetch_assoc();
				Session::set('user', true); // set user is true
				Session::set('userId', $value['id']);
				Session::set('email', $value['email']);
				Session::set('fullName', $value['fullName']);
				Session::set('role_id', $value['role_id']);
				header("Location:index.php");
			} else {
				$alert = "Username hoặc password không đúng!";
				return $alert;
			}
		}
	}

	public function insert($data)
	{
		$fullName = mysqli_real_escape_string($this->db->link, $data['fullName']);
		$email = mysqli_real_escape_string($this->db->link, $data['email']);
		$dob = mysqli_real_escape_string($this->db->link, $data['dob']);
		$address = mysqli_real_escape_string($this->db->link, $data['address']);
		$password = mysqli_real_escape_string($this->db->link, md5($data['password']));

		if ($fullName == "" || $email == "" || $dob == "" || $email == "" || $password == "") {
			$alert = '<span class="error">Vui lòng nhập vào đầy đủ thông tin tài khoản</span>';
			return $alert;
		} else {
			$check_email = "SELECT * FROM users WHERE email='$email' LIMIT 1";
			$result_check = $this->db->select($check_email);
			if ($result_check) {
				$alert = '<span class="error">Email đã tồn tại</span>';
				return $alert;
			} else {
				// Genarate captcha
				$captcha = rand(10000, 99999);

				$query = "INSERT INTO users VALUES (NULL,'$email','$fullName','$dob','$password',2,1,'$address',0,'" . $captcha . "') ";
				$result = $this->db->insert($query);
				if ($result) {
					// Send email
					$mail = new PHPMailer();
					$mail->IsSMTP();
					$mail->Mailer = "smtp";

					$mail->SMTPDebug  = 0;
					$mail->SMTPAuth   = TRUE;
					$mail->SMTPSecure = "tls";
					$mail->Port       = 587;
					$mail->Host       = "smtp.gmail.com";
					$mail->Username   = "khuongip564gb@gmail.com";
					$mail->Password   = "googlekhuongip564gb";

					$mail->IsHTML(true);
					$mail->CharSet = 'UTF-8';
					$mail->AddAddress("lapankhuongnguyen@gmail.com", "recipient-name");
					$mail->SetFrom("khuongip564gb@gmail.com", "Instrument Store");
					$mail->AddReplyTo("khuongip564gb@gmail.com", "Instrument Store");
					$mail->AddCC("khuongip564gb@gmail.com", "cc-recipient-name");
					$mail->Subject = "Xác nhận email tài khoản - Instruments Store";
					$mail->Body = "<h3>Cảm ơn bạn đã đăng ký tài khoản tại website InstrumentStore</h3></br>Đây là mã xác minh tài khoản của bạn: " . $captcha . "";

					$mail->Send();

					return '<span class="success">Đăng ký tài khoản thành công!</span>';
				} else {
					return '<span class="error">Đăng ký tài khoản thất bại!</span>';
				}
			}
		}
	}

	public function get()
	{
		$userId = Session::get('userId');
		$query = "SELECT * FROM users WHERE id = '$userId' LIMIT 1";
		$mysqli_result = $this->db->select($query);
		if ($mysqli_result) {
			$result = mysqli_fetch_all($this->db->select($query), MYSQLI_ASSOC)[0];
			return $result;
		}
		return false;
	}

	public function getLastUserId()
	{
		$query = "SELECT * FROM users ORDER BY id DESC LIMIT 1";
		$mysqli_result = $this->db->select($query);
		if ($mysqli_result) {
			$result = mysqli_fetch_all($this->db->select($query), MYSQLI_ASSOC)[0];
			return $result;
		}
		return false;
	}

	public function confirm($userId, $captcha)
	{
		$query = "SELECT * FROM users WHERE id = '$userId' AND captcha = '$captcha' LIMIT 1";
		$mysqli_result = $this->db->select($query);
		if ($mysqli_result) {
			// Update comfirmed
			$sql = "UPDATE users SET isConfirmed = 1 WHERE id = $userId";
			$update = $this->db->update($sql);
			if ($update) {
				return true;
			}
		}
		return 'Mã xác minh không đúng!';
	}
}
?>