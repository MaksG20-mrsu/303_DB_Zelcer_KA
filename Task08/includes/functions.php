<?php
function getStudents($groupFilter = null) {
    $db = Database::connect();
    $sql = "SELECT s.*, g.group_number, g.direction 
            FROM students s 
            JOIN groups g ON s.group_id = g.id 
            WHERE g.is_active = 1";
    
    $params = [];
    if ($groupFilter && $groupFilter !== '') {
        $sql .= " AND g.group_number = :group_number";
        $params[':group_number'] = $groupFilter;
    }
    
    $sql .= " ORDER BY g.group_number, s.full_name";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getActiveGroups() {
    $db = Database::connect();
    $stmt = $db->query("SELECT group_number FROM groups WHERE is_active = 1 ORDER BY group_number");
    return $stmt->fetchAll();
}

function getStudent($id) {
    $db = Database::connect();
    $stmt = $db->prepare("SELECT * FROM students WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function deleteStudent($id) {
    $db = Database::connect();
    $stmt = $db->prepare("DELETE FROM students WHERE id = ?");
    return $stmt->execute([$id]);
}

function getAllGroups() {
    $db = Database::connect();
    $stmt = $db->query("SELECT id, group_number, direction FROM groups ORDER BY group_number");
    return $stmt->fetchAll();
}
?>