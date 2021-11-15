<?php
include_once 'lib/session.php';
Session::checkSession();
include_once 'classes/cart.php';
include_once 'classes/user.php';

$cart = new cart();
$list = $cart->get();
$totalPrice = $cart->getTotalPriceByUserId();
$totalQty = $cart->getTotalQtyByUserId();

$user = new user();
$userInfo = $user->get();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://use.fontawesome.com/2145adbb48.js"></script>
    <script src="https://kit.fontawesome.com/a42aeb5b72.js" crossorigin="anonymous"></script>
    <title>Checkout</title>
</head>

<body>
    <nav>
        <input type="checkbox" id="check">
        <label for="check" class="checkbtn">
            <i class="fas fa-bars"></i>
        </label>
        <label class="logo">STORENOW</label>
        <ul>
            <li><a href="index.php">Trang chủ</a></li>
            <li><a href="register.php" id="signup">Đăng ký</a></li>
            <li><a href="login.php" id="signin">Đăng nhập</a></li>
            <li><a href="order.php" id="order">Đơn hàng</a></li>
            <li>
                <a href="checkout.php" class="active">
                    <i class="fa fa-shopping-bag"></i>
                    <span class="sumItem" id="totalQtyHeader">
                        <?= $totalQty['total'] ?>
                    </span>
                </a>
            </li>
        </ul>
    </nav>
    <section class="banner"></section>
    <div class="featuredProducts">
        <h1>Giỏ hàng</h1>
    </div>
    <div class="container-single">
        <?php
        if ($list) { ?>
            <table class="order">
                <tr>
                    <th>STT</th>
                    <th>Tên sản phẩm</th>
                    <th>Hình ảnh</th>
                    <th>Đơn giá</th>
                    <th>Số lượng</th>
                    <th>Thao tác</th>
                </tr>
                <?php
                $count = 1;
                foreach ($list as $key => $value) { ?>
                    <tr>
                        <td><?= $count++ ?></td>
                        <td><?= $value['productName'] ?></td>
                        <td><img class="image-cart" src="admin/uploads/<?= $value['productImage'] ?>"></td>
                        <td><?= number_format($value['productPrice'], 0, '', ',') ?></td>
                        <td>
                            <input id="<?= $value['productId'] ?>" type="number" name="qty" class="qty" value="<?= $value['qty'] ?>" onclick="update(this)">
                        </td>
                        <td>
                            <a href="delete_cart.php?id=<?= $value['id'] ?>">Xóa</a>
                        </td>
                    </tr>
                <?php }
                ?>
            </table>
            <div class="orderinfo">
                <div class="buy">
                    <h3>Thông tin đơn đặt hàng</h3>
                    <div>
                        Người đặt hàng: <b><?= $userInfo['fullname'] ?></b>
                    </div>
                    <div>
                        Số lượng: <b id="qtycart"><?= $totalQty['total'] ?></b>
                    </div>
                    <div>
                        Tổng tiền: <b id="totalcart"><?= number_format($totalPrice['total'], 0, '', ',') ?>vnđ</b>
                    </div>
                    <div>
                        Địa chỉ nhận hàng: <b><?= $userInfo['address'] ?></b>
                    </div>
                    <div class="buy-btn">
                        <a href="#">Tiến hành đặt hàng</a>
                    </div>
                </div>
            </div>
        <?php } else { ?>
            <h3>Giỏ hàng hiện đang rỗng</h3>
        <?php }
        ?>
    </div>
    </div>
    <footer>
        <div class="social">
            <a href="#"><i class="fa fa-facebook" aria-hidden="true"></i></a>
            <a href="#"><i class="fa fa-twitter" aria-hidden="true"></i></a>
            <a href="#"><i class="fa fa-instagram" aria-hidden="true"></i></a>
        </div>
        <ul class="list">
            <li>
                <a href="#">Home</a>
            </li>
            <li>
                <a href="#">Product</a>
            </li>
            <li>
                <a href="#">Contact</a>
            </li>
            <li>
                <a href="#">About</a>
            </li>
        </ul>
        <p class="copyright">Khuong Nguyen @ 2021</p>
    </footer>
</body>
<script type="text/javascript">
    function update(e) {
        setTimeout(function() {
            var http = new XMLHttpRequest();
            var url = 'update_cart.php';
            var params = "productId=" + e.id + "&qty=" + e.value;
            http.open('POST', url, true);

            http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

            http.onreadystatechange = function() {
                if (http.readyState === XMLHttpRequest.DONE) {
                    var status = http.status;
                    if (status === 0 || (status >= 200 && status < 400)) {
                        var arr = http.responseText;
                        var b = false;
                        var result = "";
                        for (let index = 0; index < arr.length; index++) {
                            if (arr[index] == "[") {
                                b = true;
                            }
                            if (b) {
                                result += arr[index];
                            }
                        }
                        var arrResult = JSON.parse(result.replace("undefined", ""));

                        document.getElementById("totalQtyHeader").innerHTML = arrResult[1]['total'];
                        document.getElementById("qtycart").innerHTML = arrResult[1]['total'];
                        document.getElementById("totalcart").innerHTML = arrResult[0]['total'].replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,")+"vnđ";

                        alert('Đã cập nhật giỏ hàng!');
                    } else {
                        alert('Cập nhật giỏ hàng thất bại!');
                    }
                }
            }
            http.send(params);
        }, 500);
    }
</script>

</html>