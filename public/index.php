<?php
require_once '../controllers/StudentController.php';

$controller = new StudentController();
$page = $_GET['page'] ?? 'dashboard';

if(isset($_POST['add'])) $controller->add();
if(isset($_GET['delete'])) $controller->delete();
if(isset($_GET['food'])) $controller->toggle('food');
if(isset($_GET['laundry'])) $controller->toggle('laundry');
if(isset($_GET['bathroom'])) $controller->toggle('bathroom');
if(isset($_POST['timing'])) $controller->timing();

$data = $controller->index();

include '../views/layout/header.php';

switch($page){
    case 'food': include '../views/dashboard/food.php'; break;
    case 'laundry': include '../views/dashboard/laundry.php'; break;
    case 'bathroom': include '../views/dashboard/bathroom.php'; break;
    case 'timing': include '../views/dashboard/timing.php'; break;
    default: include '../views/dashboard/warden.php';
}

include '../views/layout/footer.php';
?>