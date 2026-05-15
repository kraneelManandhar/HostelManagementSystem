<?php
require_once __DIR__ . '/../models/Warden.php';

class WardenController {

    private $model;

    public function __construct($pdo){
        $this->model = new Warden($pdo);
    }

    /* ===== PAGE DATA =====
       These methods are used by the PHP views to load rows for each page.
       The controller stays small and sends database work to the Warden model. */

    public function getDashboard(){ return $this->model->getDashboardData(); }
    public function getStudents(){  return $this->model->getStudents(); }
    public function getFood(){      return $this->model->getFoodData(); }
    public function getLaundry(){   return $this->model->getLaundryData(); }
    public function getCleaning(){  return $this->model->getCleaningData(); }
    public function getTiming(){    return $this->model->getTimingData(); }

    /* ===== AJAX UPDATES =====
       The dropdowns/time fields call these methods through public/js/script.js.
       Each method returns JSON so JavaScript can show success or rollback on error. */

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
        // The UI sends 1/0, while the database stores readable laundry text.
        $status = ($_POST['status'] ?? 0) == 1 ? 'Completed' : 'Pending';
        $ok = $this->model->updateLaundry($id,$status);
        echo json_encode(['success'=>$ok]);
    }

    public function ajaxCleaning(){
        header('Content-Type: application/json');
        $room_id = (int)($_POST['id'] ?? 0);
        // The bathroom cleaning dropdown shows Done/Pending to match this mapping.
        $status = ($_POST['status'] ?? 0) == 1 ? 'Done' : 'Pending';
        $ok = $this->model->updateCleaning($room_id,$status);
        echo json_encode(['success'=>$ok]);
    }

    public function ajaxTiming(){
        header('Content-Type: application/json');
        $id  = (int)($_POST['id'] ?? 0);
        $inValue = trim((string) ($_POST['check_in'] ?? ''));
        $outValue = trim((string) ($_POST['check_out'] ?? ''));
        // Empty date/time inputs are stored as NULL instead of invalid dates.
        $in  = $inValue !== '' ? date('Y-m-d H:i:s', strtotime($inValue)) : null;
        $out = $outValue !== '' ? date('Y-m-d H:i:s', strtotime($outValue)) : null;

        $ok = $this->model->updateTiming($id,$in,$out);
        echo json_encode(['success'=>$ok]);
    }

    public function saveNotice(){
        header('Location: index.php?action=warden_notices');
        exit;
    }
}
?>
