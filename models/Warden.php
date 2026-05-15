<?php
class Warden {

    private $pdo;

    public function __construct($pdo){
        $this->pdo = $pdo;
    }

    /* ===== FETCH DATA =====
       Each query returns the latest visible state for the Warden pages.
       LEFT JOIN keeps students/rooms visible even if no status record exists yet. */

    public function getStudents(){
        return $this->pdo->query("
            SELECT s.id,
            CONCAT(s.first_name,' ',s.last_name) AS name,
            s.email,
            s.contact_number,
            r.number AS room_number
            FROM students s
            LEFT JOIN rooms r ON s.room_id = r.id
            ORDER BY s.first_name, s.last_name
        ")->fetchAll();
    }

    public function getFoodData(){
        return $this->pdo->query("
            SELECT s.id,
            CONCAT(s.first_name,' ',s.last_name) AS name,
            COALESCE(f.status, 0) AS food
            FROM students s
            LEFT JOIN (
                -- Latest food row per student is used as the current status.
                SELECT f1.student_id, f1.status
                FROM food f1
                INNER JOIN (
                    SELECT student_id, MAX(id) AS id
                    FROM food
                    GROUP BY student_id
                ) latest ON latest.id = f1.id
            ) f ON f.student_id = s.id
            ORDER BY s.first_name, s.last_name
        ")->fetchAll();
    }

    public function getLaundryData(){
        return $this->pdo->query("
            SELECT s.id,
            CONCAT(s.first_name,' ',s.last_name) AS name,
            CASE WHEN l.status = 'Completed' THEN 1 ELSE 0 END AS laundry,
            COALESCE(l.status, 'Pending') AS laundry_status
            FROM students s
            LEFT JOIN (
                -- Latest laundry row per student is used as the current status.
                SELECT l1.student_id, l1.status
                FROM laundry l1
                INNER JOIN (
                    SELECT student_id, MAX(id) AS id
                    FROM laundry
                    GROUP BY student_id
                ) latest ON latest.id = l1.id
            ) l ON s.id=l.student_id
            ORDER BY s.first_name, s.last_name
        ")->fetchAll();
    }

    public function getCleaningData(){
        return $this->pdo->query("
            SELECT r.id AS room_id,
            r.number,
            CASE WHEN c.status = 'Done' THEN 1 ELSE 0 END AS cleaning,
            COALESCE(c.status, 'Pending') AS cleaning_status
            FROM rooms r
            LEFT JOIN (
                -- Latest cleaning row per room is used as the current status.
                SELECT c1.room_id, c1.status
                FROM cleaning c1
                INNER JOIN (
                    SELECT room_id, MAX(id) AS id
                    FROM cleaning
                    GROUP BY room_id
                ) latest ON latest.id = c1.id
            ) c ON r.id=c.room_id
            ORDER BY r.number
        ")->fetchAll();
    }

    public function getTimingData(){
        return $this->pdo->query("
            SELECT s.id,
            CONCAT(s.first_name,' ',s.last_name) AS name,
            t.check_in,t.check_out
            FROM students s
            LEFT JOIN (
                -- Latest timing row per student is used for the editable time fields.
                SELECT t1.student_id, t1.check_in, t1.check_out
                FROM timing t1
                INNER JOIN (
                    SELECT student_id, MAX(id) AS id
                    FROM timing
                    GROUP BY student_id
                ) latest ON latest.id = t1.id
            ) t ON s.id=t.student_id
            ORDER BY s.first_name, s.last_name
        ")->fetchAll();
    }

    public function getDashboardData(){
        return $this->pdo->query("
            SELECT COUNT(*) AS students FROM students
        ")->fetch();
    }

    /* ===== UPDATE (UPSERT) =====
       Update the latest row if one exists; otherwise insert a new row.
       This keeps the views simple because every student/room can be edited directly. */

    public function updateFood($id,$status){
        $existing = $this->latestRecordId('food', 'student_id', $id);

        if ($existing) {
            $stmt=$this->pdo->prepare("UPDATE food SET status=?, date=CURDATE() WHERE id=?");
            return $stmt->execute([$status,$existing]);
        }

        $stmt=$this->pdo->prepare("INSERT INTO food(student_id,status,date) VALUES(?,?,CURDATE())");
        return $stmt->execute([$id,$status]);
    }

    public function updateLaundry($id,$status){
        $existing = $this->latestRecordId('laundry', 'student_id', $id);

        if ($existing) {
            $stmt=$this->pdo->prepare("UPDATE laundry SET status=?, returned_at=? WHERE id=?");
            $returnedAt = $status === 'Completed' ? date('Y-m-d H:i:s') : null;
            return $stmt->execute([$status,$returnedAt,$existing]);
        }

        $stmt=$this->pdo->prepare("INSERT INTO laundry(student_id,status,returned_at) VALUES(?,?,?)");
        $returnedAt = $status === 'Completed' ? date('Y-m-d H:i:s') : null;
        return $stmt->execute([$id,$status,$returnedAt]);
    }

    public function updateCleaning($room,$status){
        $existing = $this->latestRecordId('cleaning', 'room_id', $room);

        if ($existing) {
            $stmt=$this->pdo->prepare("UPDATE cleaning SET status=?, cleaned_at=? WHERE id=?");
            $cleanedAt = $status === 'Done' ? date('Y-m-d H:i:s') : null;
            return $stmt->execute([$status,$cleanedAt,$existing]);
        }

        $stmt=$this->pdo->prepare("INSERT INTO cleaning(room_id,status,cleaned_at) VALUES(?,?,?)");
        $cleanedAt = $status === 'Done' ? date('Y-m-d H:i:s') : null;
        return $stmt->execute([$room,$status,$cleanedAt]);
    }

    public function updateTiming($id,$in,$out){
        $existing = $this->latestRecordId('timing', 'student_id', $id);
        $status = $out ? 'OUT' : 'IN';

        if ($existing) {
            $stmt=$this->pdo->prepare("UPDATE timing SET check_in=?, check_out=?, status=? WHERE id=?");
            return $stmt->execute([$in,$out,$status,$existing]);
        }

        $stmt=$this->pdo->prepare("INSERT INTO timing(student_id,check_in,check_out,status) VALUES(?,?,?,?)");
        return $stmt->execute([$id,$in,$out,$status]);
    }

    public function saveNotice($noticeId,$title,$description,$date){
        if ($noticeId > 0) {
            $stmt = $this->pdo->prepare("UPDATE notices SET title=?, description=?, date=? WHERE id=?");
            return $stmt->execute([$title,$description,$date,$noticeId]);
        }

        $stmt = $this->pdo->prepare("INSERT INTO notices(title,description,date,time,author) VALUES(?,?,?,?,?)");
        return $stmt->execute([$title,$description,$date,date('H:i:s'),'WARDEN']);
    }

    private function latestRecordId($table,$column,$value){
        $allowedTables = ['food', 'laundry', 'cleaning', 'timing'];
        $allowedColumns = ['student_id', 'room_id'];

        // Whitelisting protects the dynamic table/column names used below.
        if (!in_array($table, $allowedTables, true) || !in_array($column, $allowedColumns, true)) {
            return null;
        }

        $stmt = $this->pdo->prepare("SELECT id FROM {$table} WHERE {$column}=? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$value]);
        return $stmt->fetchColumn() ?: null;
    }
}
?>
