<?php
require_once 'config/db.php';

// Ograniczamy usuwanie tylko do dozwolonych tabel
$allowed_tables = ['Bounty_Hunters', 'Clients', 'Targets', 'Bounties', 'Missions', 'Equipment', 'Hunter_Gear'];

if (isset($_GET['table']) && isset($_GET['pk']) && isset($_GET['id'])) {
    $table = $_GET['table'];
    
    if (!in_array($table, $allowed_tables)) {
        die("Odmowa dostępu do tego sektora bazy.");
    }

    $pk = $_GET['pk'];
    $id = $_GET['id'];

    // Walidacja poprawności nazwy kolumny klucza
    $col_check = $conn->query("SHOW COLUMNS FROM `$table`");
    $valid_columns = [];
    if ($col_check) {
        while ($c = $col_check->fetch_assoc()) {
            $valid_columns[] = $c['Field'];
        }
    }

    if (!in_array($pk, $valid_columns)) {
        die("Nieprawidłowy identyfikator kolumny.");
    }

    // Bezpieczne zapytanie usuwające z użyciem Prepared Statement
    try {
        $stmt = $conn->prepare("DELETE FROM `$table` WHERE `$pk` = ?");
        if (!$stmt) {
            throw new Exception($conn->error);
        }
        $stmt->bind_param("s", $id);
        
        if ($stmt->execute()) {
            // Przekierowanie powrotne po udanym usunięciu
            header("Location: view_table.php?table=" . urlencode($table));
            exit();
        } else {
            die("Nie udało się usunąć wpisu: " . $stmt->error);
        }
    } catch (Exception $e) {
        die("<div style='color:red; background:#111; padding:20px; font-family:monospace;'>BŁĄD SQL: " . $e->getMessage() . "<br><br><a href='view_table.php?table=" . urlencode($table) . "' style='color:#00ffcc;'>Powrót</a></div>");
    }
} else {
    // Brak pełnych danych -> wracamy na główną przeglądarkę
    header("Location: view_table.php");
    exit();
}
