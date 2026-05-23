<?php
class Application {
    public $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function apply($student_id, $opportunity_id, $message = '', $cv_file = '') {
        // Prevent duplicate
        $check = "SELECT id FROM applications WHERE student_id = $student_id AND opportunity_id = $opportunity_id";
        $result = $this->conn->query($check);
        if ($result->num_rows > 0) return false;

        $msg = mysqli_real_escape_string($this->conn, $message);
        $cv = mysqli_real_escape_string($this->conn, $cv_file);
        $sql = "INSERT INTO applications (student_id, opportunity_id, message, cv_file, status) VALUES ($student_id, $opportunity_id, '$msg', '$cv', 'pending')";
        return $this->conn->query($sql);
    }

    public function hasApplied($student_id, $opportunity_id) {
        $check = "SELECT id FROM applications WHERE student_id = $student_id AND opportunity_id = $opportunity_id";
        $result = $this->conn->query($check);
        return $result->num_rows > 0;
    }

    public function getStudentApplications($student_id) {
        $sql = "SELECT a.*, o.title, u.full_name as company_name, u.email as company_email 
                FROM applications a 
                JOIN opportunities o ON a.opportunity_id = o.id 
                JOIN users u ON o.company_id = u.id 
                WHERE a.student_id = $student_id ORDER BY a.applied_at DESC";
        return $this->conn->query($sql);
    }
}
?>
