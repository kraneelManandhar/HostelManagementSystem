<?php
require_once './models/Student.php';

class StudentController {
    private $model;

    public function __construct(){
        $this->model = new Student(); // No parameter needed, uses DB::connect()
    }

    public function index(){
        return $this->model->getAll();
    }

    public function add(){
        $this->model->add($_POST['name'],$_POST['email'],$_POST['contact']);
        header("Location:index.php");
    }

    public function delete(){
        $this->model->delete($_GET['id']);
        header("Location:index.php");
    }

    public function toggle($type){
        $this->model->toggle($type,$_GET['id']);
        header("Location:index.php?page=".$type);
    }

    public function timing(){
        $this->model->updateTime($_POST['id'],$_POST['in'],$_POST['out']);
        header("Location:index.php?page=timing");
    }

    public function register($data) {
        return $this->model->registerStudent($data);
    }
}
?>