<?php
class Warden {

    private $pdo;

    public function __construct($pdo){
        $this->pdo = $pdo;
    }

    /* ===== FETCH DATA ===== */

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

    public function getRooms(){
        return $this->pdo->query("
            SELECT
                r.id,
                r.number,
                r.type,
                r.student1_id,
                r.student2_id,
                TRIM(CONCAT(COALESCE(s1.first_name, ''), ' ', COALESCE(s1.middle_name, ''), ' ', COALESCE(s1.last_name, ''))) AS student1_name,
                COALESCE(s1.contact_number, '') AS student1_contact,
                TRIM(CONCAT(COALESCE(s2.first_name, ''), ' ', COALESCE(s2.middle_name, ''), ' ', COALESCE(s2.last_name, ''))) AS student2_name,
                COALESCE(s2.contact_number, '') AS student2_contact
            FROM rooms r
            LEFT JOIN students s1 ON s1.id = r.student1_id
            LEFT JOIN students s2 ON s2.id = r.student2_id
            ORDER BY r.number
        ")->fetchAll();
    }

    public function getRoomStudentOptions($roomId = 0){
        $stmt = $this->pdo->prepare("
            SELECT
                s.id,
                CONCAT(s.first_name,' ',s.last_name) AS name,
                s.room_id,
                r.number AS room_number
            FROM students s
            LEFT JOIN rooms r ON r.id = s.room_id
            WHERE s.room_id IS NULL OR s.room_id = ?
            ORDER BY s.first_name, s.last_name
        ");
        $stmt->execute([(int) $roomId]);
        return $stmt->fetchAll();
    }

    public function getTimingData(){
        return $this->pdo->query("
            SELECT s.id,
            CONCAT(s.first_name,' ',s.last_name) AS name,
            t.check_in,t.check_out
            FROM students s
            LEFT JOIN (
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

    /* ===== UPDATE (UPSERT) ===== */

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

    public function addRoom($number,$type){
        $stmt = $this->pdo->prepare("INSERT INTO rooms(number,type) VALUES(?,?)");
        return $stmt->execute([$number,$type]);
    }

    public function saveRoom($roomId,$number,$type,$student1Id,$student2Id){
        if ($type === 'single') {
            $student2Id = 0;
        }

        if ($student1Id > 0 && $student1Id === $student2Id) {
            $student2Id = 0;
        }

        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare("SELECT student1_id, student2_id FROM rooms WHERE id=? FOR UPDATE");
            $stmt->execute([$roomId]);
            $current = $stmt->fetch();

            if ($current) {
                foreach ([(int) $current['student1_id'], (int) $current['student2_id']] as $oldStudentId) {
                    if ($oldStudentId > 0) {
                        $clearStudent = $this->pdo->prepare("UPDATE students SET room_id=NULL WHERE id=?");
                        $clearStudent->execute([$oldStudentId]);
                    }
                }
            }

            foreach ([$student1Id, $student2Id] as $studentId) {
                if ($studentId > 0) {
                    $clearSlots = $this->pdo->prepare("
                        UPDATE rooms
                        SET
                            student1_id = CASE WHEN student1_id = ? THEN NULL ELSE student1_id END,
                            student2_id = CASE WHEN student2_id = ? THEN NULL ELSE student2_id END
                    ");
                    $clearSlots->execute([$studentId,$studentId]);

                    $clearStudentRoom = $this->pdo->prepare("UPDATE students SET room_id=NULL WHERE id=?");
                    $clearStudentRoom->execute([$studentId]);
                }
            }

            $stmt = $this->pdo->prepare("UPDATE rooms SET number=?, type=?, student1_id=?, student2_id=? WHERE id=?");
            $stmt->execute([
                $number,
                $type,
                $student1Id > 0 ? $student1Id : null,
                $student2Id > 0 ? $student2Id : null,
                $roomId
            ]);

            foreach ([$student1Id, $student2Id] as $studentId) {
                if ($studentId > 0) {
                    $assignStudent = $this->pdo->prepare("UPDATE students SET room_id=? WHERE id=?");
                    $assignStudent->execute([$roomId,$studentId]);
                }
            }

            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            return false;
        }
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

        if (!in_array($table, $allowedTables, true) || !in_array($column, $allowedColumns, true)) {
            return null;
        }

        $stmt = $this->pdo->prepare("SELECT id FROM {$table} WHERE {$column}=? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$value]);
        return $stmt->fetchColumn() ?: null;
    }
}
?>
