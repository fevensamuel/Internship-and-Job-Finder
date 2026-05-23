<?php
class Opportunity {
    public $conn;
    public $id;
    public $company_id;
    public $title;
    public $type;
    public $category;
    public $location;
    public $description;
    public $deadline;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAllApproved() {
        $sql = "SELECT o.*, u.full_name as company_name FROM opportunities o JOIN users u ON o.company_id = u.id WHERE o.status = 'approved' ORDER BY o.created_at DESC";
        return $this->conn->query($sql);
    }

    public function getById($id) {
        $sql = "SELECT o.*, u.full_name as company_name FROM opportunities o JOIN users u ON o.company_id = u.id WHERE o.id = $id";
        $result = $this->conn->query($sql);
        return $result->fetch_assoc();
    }
    
    public function getByCompany($company_id) {
        $sql = "SELECT * FROM opportunities WHERE company_id = $company_id ORDER BY created_at DESC";
        return $this->conn->query($sql);
    }
}
?>
