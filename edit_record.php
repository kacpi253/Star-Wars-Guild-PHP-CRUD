<?php
require_once 'config/db.php';

$allowed_tables = ['Bounty_Hunters', 'Clients', 'Targets', 'Bounties', 'Missions', 'Equipment', 'Hunter_Gear'];
$current_table = isset($_GET['table']) ? $_GET['table'] : 'Bounty_Hunters';

if (!in_array($current_table, $allowed_tables)) {
    die("Odmowa dostępu do tego sektora.");
}

$pk = isset($_GET['pk']) ? $_GET['pk'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';

if (empty($pk) || empty($id)) {
    header("Location: view_table.php?table=" . urlencode($current_table));
    exit();
}

$message = "";

// Pobranie listy dozwolonych kolumn z bazy
$table_columns = [];
try {
    $col_res = $conn->query("SHOW COLUMNS FROM `$current_table`");
    if ($col_res) {
        while ($col = $col_res->fetch_assoc()) {
            $table_columns[$col['Field']] = $col;
        }
    }
} catch (Exception $e) {}

if (!array_key_exists($pk, $table_columns)) {
    die("Nieprawidłowy identyfikator kolumny.");
}

// Obsługa zapisu edycji (POST)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $updates = [];
    $values = [];

    foreach ($_POST as $key => $value) {
        if ($key != 'submit' && $key != $pk && array_key_exists($key, $table_columns)) {
            if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $value)) {
                $value = str_replace('T', ' ', $value) . ':00';
            }
            $updates[] = "`" . $key . "` = ?";
            $values[] = $value;
        }
    }

    if (!empty($updates)) {
        $updates_str = implode(", ", $updates);
        $update_sql = "UPDATE `$current_table` SET $updates_str WHERE `$pk` = ?";
        $values[] = $id;

        try {
            $stmt = $conn->prepare($update_sql);
            if (!$stmt) {
                throw new Exception($conn->error);
            }

            $types = str_repeat('s', count($values));
            $stmt->bind_param($types, ...$values);

            if ($stmt->execute()) {
                $message = "<div class='msg success'>[SUKCES] Rekord został zaktualizowany w archiwach.</div>";
            } else {
                $message = "<div class='msg error'>" . htmlspecialchars($stmt->error) . "</div>";
            }
        } catch (Exception $e) {
            $message = "<div class='msg error'>Błąd bazy danych: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}

// Pobranie aktualnych danych rekordu do edycji
$current_record = null;
try {
    $stmt_get = $conn->prepare("SELECT * FROM `$current_table` WHERE `$pk` = ?");
    if ($stmt_get) {
        $stmt_get->bind_param("s", $id);
        $stmt_get->execute();
        $res = $stmt_get->get_result();
        if ($res && $res->num_rows > 0) {
            $current_record = $res->fetch_assoc();
        }
    }
} catch (Exception $e) {}

if (!$current_record) {
    die("<div style='color:red; background:#111; padding:20px; font-family:monospace;'>[BŁĄD]: Nie znaleziono wskazanego rekordu w archiwach.<br><br><a href='view_table.php?table=" . urlencode($current_table) . "' style='color:#00ffcc;'>Powrót</a></div>");
}

$page_title = 'Edytuj Rekord - Gildia';
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<div class="container container-small">
    <h2>Edytuj wpis w: <?php echo htmlspecialchars($current_table); ?> (ID: <?php echo htmlspecialchars($id); ?>)</h2>

    <?php echo $message; ?>

    <form action="edit_record.php?table=<?php echo urlencode($current_table); ?>&pk=<?php echo urlencode($pk); ?>&id=<?php echo urlencode($id); ?>" method="POST">
        <?php
        $fk_mapping = [
            'Client_ID' => ['table' => 'Clients', 'id_col' => 'Client_ID', 'disp_col' => 'Name'],
            'Target_ID' => ['table' => 'Targets', 'id_col' => 'Target_ID', 'disp_col' => 'Name'],
            'Hunter_ID' => ['table' => 'Bounty_Hunters', 'id_col' => 'Hunter_ID', 'disp_col' => 'Name'],
            'Equipment_ID' => ['table' => 'Equipment', 'id_col' => 'Equipment_ID', 'disp_col' => 'Item_Name'],
            'Bounty_ID' => ['table' => 'Bounties', 'id_col' => 'Bounty_ID', 'disp_col' => 'Bounty_ID']
        ];

        foreach ($table_columns as $col_name => $row) {
            $val = isset($current_record[$col_name]) ? $current_record[$col_name] : '';

            // Klucz główny wyświetlamy jako pole tylko do odczytu
            if ($col_name === $pk) {
                echo "<div class='form-group'>";
                echo "<label>" . htmlspecialchars($col_name) . " (Klucz Główny - Stały)</label>";
                echo "<input type='text' value='" . htmlspecialchars((string)$val) . "' disabled style='opacity: 0.6;'>";
                echo "</div>";
                continue;
            }

            if ($row['Extra'] != 'auto_increment' && $row['Default'] !== 'CURRENT_TIMESTAMP') {
                echo "<div class='form-group'>";
                echo "<label>" . htmlspecialchars($col_name) . "</label>";

                if (strpos($row['Type'], 'enum') === 0) {
                    preg_match('/enum\((.*)\)/', $row['Type'], $matches);
                    $enum_values = explode(',', $matches[1]);

                    echo "<select name='" . htmlspecialchars($col_name) . "' required>";
                    foreach ($enum_values as $enum_val) {
                        $enum_val = trim($enum_val, "'");
                        $selected = ($val == $enum_val) ? "selected" : "";
                        echo "<option value='" . htmlspecialchars($enum_val) . "' $selected>" . htmlspecialchars($enum_val) . "</option>";
                    }
                    echo "</select>";
                } elseif (array_key_exists($col_name, $fk_mapping)) {
                    $fk = $fk_mapping[$col_name];
                    echo "<select name='" . htmlspecialchars($col_name) . "' required>";
                    $fk_res = $conn->query("SELECT " . $fk['id_col'] . ", " . $fk['disp_col'] . " FROM " . $fk['table']);
                    if ($fk_res) {
                        while ($fk_row = $fk_res->fetch_assoc()) {
                            $display = htmlspecialchars((string)$fk_row[$fk['disp_col']]);
                            if ($col_name === 'Bounty_ID') {
                                $display = "Zlecenie #" . $display;
                            }
                            $selected = ($val == $fk_row[$fk['id_col']]) ? "selected" : "";
                            echo "<option value='" . htmlspecialchars((string)$fk_row[$fk['id_col']]) . "' $selected>" . $display . "</option>";
                        }
                    }
                    echo "</select>";
                } else {
                    if ($row['Type'] === 'datetime' || $row['Type'] === 'timestamp') {
                        $formatted_dt = !empty($val) ? date('Y-m-d\TH:i', strtotime($val)) : '';
                        echo "<input type='datetime-local' name='" . htmlspecialchars($col_name) . "' value='" . htmlspecialchars($formatted_dt) . "' required>";
                    } elseif ($row['Type'] === 'date') {
                        echo "<input type='date' name='" . htmlspecialchars($col_name) . "' value='" . htmlspecialchars((string)$val) . "' required>";
                    } else {
                        echo "<input type='text' name='" . htmlspecialchars($col_name) . "' value='" . htmlspecialchars((string)$val) . "' required>";
                    }
                }

                echo "</div>";
            }
        }
        ?>
        <button type="submit" name="submit" class="btn-block">ZAPISZ ZMIANY</button>
    </form>

    <a href="view_table.php?table=<?php echo urlencode($current_table); ?>" class="back-link">< Powrót do przeglądarki</a>
</div>

<?php
require_once 'includes/footer.php';
?>
