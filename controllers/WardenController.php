<?php
require_once __DIR__ . '/../models/Warden.php';

class WardenController {

    private $model;

    public function __construct($pdo){
        $this->model = new Warden($pdo);
    }

    /* ===== PAGE DATA ===== */

    public function getDashboard(){ return $this->model->getDashboardData(); }
    public function getStudents(){  return $this->model->getStudents(); }
    public function getFood(){      return $this->model->getFoodData(); }
    public function getLaundry(){   return $this->model->getLaundryData(); }
    public function getRooms(){     return $this->model->getRooms(); }
    public function getRoomStudentOptions($roomId = 0){ return $this->model->getRoomStudentOptions($roomId); }
    public function getCleaning(){  return $this->model->getCleaningData(); }
    public function getTiming(){    return $this->model->getTimingData(); }

    /* ===== AJAX ===== */

    public function ajaxFood(){
        header('Content-Type: application/json');
        $id = (int)($_POST['id'] ?? 0);
        $status = ($_POST['status'] ?? 0) == 1 ? 1 : 0;
        $ok = $this->model->updateFood($id,$status);
        echo json_encode(['success'=>$ok]);
    }

    public function ajaxLaundry(){
        header('Content-Type: application/json');
        $id = (int)($_POST['id'] ?? 0);
        $status = ($_POST['status'] ?? 0) == 1 ? 'Completed' : 'Pending';
        $ok = $this->model->updateLaundry($id,$status);
        echo json_encode(['success'=>$ok]);
    }

    public function ajaxCleaning(){
        header('Content-Type: application/json');
        $room_id = (int)($_POST['id'] ?? 0);
        $status = ($_POST['status'] ?? 0) == 1 ? 'Done' : 'Pending';
        $ok = $this->model->updateCleaning($room_id,$status);
        echo json_encode(['success'=>$ok]);
    }

    public function ajaxTiming(){
        header('Content-Type: application/json');
        $id  = (int)($_POST['id'] ?? 0);
        $inValue = trim((string) ($_POST['check_in'] ?? ''));
        $outValue = trim((string) ($_POST['check_out'] ?? ''));
        $in  = $inValue !== '' ? date('Y-m-d H:i:s', strtotime($inValue)) : null;
        $out = $outValue !== '' ? date('Y-m-d H:i:s', strtotime($outValue)) : null;

        $ok = $this->model->updateTiming($id,$in,$out);
        echo json_encode(['success'=>$ok]);
    }

    public function saveRoom(){
        $roomId = (int)($_POST['room_id'] ?? 0);
        $number = trim((string) ($_POST['number'] ?? ''));
        $type = ($_POST['type'] ?? 'double') === 'single' ? 'single' : 'double';
        $student1Id = (int)($_POST['student1_id'] ?? 0);
        $student2Id = $type === 'double' ? (int)($_POST['student2_id'] ?? 0) : 0;

        if ($roomId > 0 && $number !== '') {
            $this->model->saveRoom($roomId, $number, $type, $student1Id, $student2Id);
        }

        header('Location: index.php?action=warden_rooms');
        exit;
    }

    public function addRoom(){
        $number = trim((string) ($_POST['number'] ?? ''));
        $type = ($_POST['type'] ?? 'double') === 'single' ? 'single' : 'double';

        if ($number !== '') {
            $this->model->addRoom($number, $type);
        }

        header('Location: index.php?action=warden_rooms');
        exit;
    }

    public function saveNotice(){
        $noticeId = (int) ($_POST['notice_id'] ?? 0);
        $title = trim((string) ($_POST['title'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $date = trim((string) ($_POST['date'] ?? date('Y-m-d')));

        if ($title !== '' && $description !== '') {
            $this->model->saveNotice($noticeId, $title, $description, $date);
        }

        header('Location: index.php?action=warden_notices');
        exit;
    }
}
?>
