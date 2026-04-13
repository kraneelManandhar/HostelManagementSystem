<?php

class PageController {

    public function home() {
        require __DIR__ . '/../views/pages/about.php';
    }

    public function about() {
        require __DIR__ . '/../views/pages/about.php';
    }

    public function staff() {
        require __DIR__ . '/../views/pages/staff.php';
    }

    public function facilities() {
        require __DIR__ . '/../views/pages/facilities.php';
    }

}