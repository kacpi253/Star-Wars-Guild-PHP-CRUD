<?php
require_once 'config/db.php';

// Ograniczamy usuwanie tylko do dozwolonych tabel
$allowed_tables = ['Bounty_Hunters', 'Clients', 'Targets', 'Bounties', 'Missions', 'Equipment', 'Hunter_Gear'];

if (isset($_GET['table']) && isset($_GET['pk']) && isset($_GET['id'])) {
    $table = $_GET['table'];
    
    if (!in_array($table, $allowed_tables)) {
        die("Odmowa dostępu do tego sektora bazy.");
    }

    $pk = $conn->real_escape_string($_GET['pk']);
    $id = $conn->real_escape_string($_GET['id']);

    // Zapytanie usuwające rekord
    $sql = "DELETE FROM $table WHERE $pk = '$id'";

    try {
        if ($conn->query($sql) === TRUE) {
            // Przekierowanie powrotne po udanym usunięciu
            header("Location: view_table.php?table=" . urlencode($table));
            exit();
        } else {
            die("Nie udało się usunąć wpisu: " . $conn->error);
        }
    } catch (Exception $e) {
        die("<div style='color:red; background:#111; padding:20px; font-family:monospace;'>BŁĄD SQL: " . $e->getMessage() . "<br><br><a href='view_table.php?table=" . urlencode($table) . "' style='color:#00ffcc;'>Powrót</a></div>");
    }
} else {
    // Brak pełnych danych -> wracamy na główną przeglądarkę
    header("Location: view_table.php");
    exit();
}
