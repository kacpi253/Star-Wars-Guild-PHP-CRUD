<?php
require_once 'config/db.php';

$allowed_tables = ['Bounty_Hunters', 'Clients', 'Targets', 'Bounties', 'Missions', 'Equipment', 'Equipment', 'Hunter_Gear'];
$current_table = isset($_GET['table']) ? $_GET['table'] : 'Bounty_Hunters';

if (!in_array($current_table, $allowed_tables)) {
    die("Odmowa dostępu do tego sektora.");
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fields = [];
    $values = [];

    foreach ($_POST as $key => $value) {
        if ($key != 'submit') {
            // Zamiana formatu 'T' z datetime-local na standardowy format SQL ze spacją
            if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $value)) {
                $value = str_replace('T', ' ', $value) . ':00';
            }
            $fields[] = $key;
            $values[] = "'" . $conn->real_escape_string($value) . "'";
        }
    }

    $fields_str = implode(", ", $fields);
    $values_str = implode(", ", $values);

    $insert_sql = "INSERT INTO $current_table ($fields_str) VALUES ($values_str)";

    try {
        if ($conn->query($insert_sql) === TRUE) {
            $message = "<div class='msg success'>[SUKCES] Nowy wpis został pomyślnie dodany do archiwów.</div>";
        } else {
            $message = "<div class='msg error'>" . $conn->error . "</div>";
        }
    } catch (Exception $e) {
        $message = "<div class='msg error'>Błąd bazy danych: " . $e->getMessage() . "</div>";
    }
}

$page_title = 'Dodaj Rekord - Gildia';
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<div class="container container-small">
    <h2>Dodaj wpis do: <?php echo $current_table; ?></h2>

    <?php echo $message; ?>

    <form action="add_record.php?table=<?php echo $current_table; ?>" method="POST">
        <?php
        $result = null;
        try {
            $result = $conn->query("SHOW COLUMNS FROM " . $current_table);
        } catch (Exception $e) {
            // Ignorujemy przed inicjalizacją bazy
        }

        if ($result) {
            $fk_mapping = [
                'Client_ID' => ['table' => 'Clients', 'id_col' => 'Client_ID', 'disp_col' => 'Name'],
                'Target_ID' => ['table' => 'Targets', 'id_col' => 'Target_ID', 'disp_col' => 'Name'],
                'Hunter_ID' => ['table' => 'Bounty_Hunters', 'id_col' => 'Hunter_ID', 'disp_col' => 'Name'],
                'Equipment_ID' => ['table' => 'Equipment', 'id_col' => 'Equipment_ID', 'disp_col' => 'Item_Name'],
                'Bounty_ID' => ['table' => 'Bounties', 'id_col' => 'Bounty_ID', 'disp_col' => 'Bounty_ID']
            ];

            while ($row = $result->fetch_assoc()) {
                if ($row['Extra'] != 'auto_increment' && $row['Default'] !== 'CURRENT_TIMESTAMP') {
                    echo "<div class='form-group'>";
                    echo "<label>" . $row['Field'] . "</label>";
                    
                    if (strpos($row['Type'], 'enum') === 0) {
                        preg_match('/enum\((.*)\)/', $row['Type'], $matches);
                        $enum_values = explode(',', $matches[1]);
                        
                        echo "<select name='" . $row['Field'] . "' required>";
                        foreach ($enum_values as $value) {
                            $value = trim($value, "'");
                            echo "<option value='$value'>$value</option>";
                        }
                        echo "</select>";
                    } elseif (array_key_exists($row['Field'], $fk_mapping)) {
                        $fk = $fk_mapping[$row['Field']];
                        echo "<select name='" . $row['Field'] . "' required>";
                        $fk_res = $conn->query("SELECT " . $fk['id_col'] . ", " . $fk['disp_col'] . " FROM " . $fk['table']);
                        if ($fk_res) {
                            while ($fk_row = $fk_res->fetch_assoc()) {
                                $display = htmlspecialchars((string)$fk_row[$fk['disp_col']]);
                                if ($row['Field'] === 'Bounty_ID') {
                                    $display = "Zlecenie #" . $display;
                                }
                                echo "<option value='" . htmlspecialchars((string)$fk_row[$fk['id_col']]) . "'>" . $display . "</option>";
                            }
                        }
                        echo "</select>";
                    } else {
                        if ($row['Type'] === 'datetime' || $row['Type'] === 'timestamp') {
                            echo "<input type='datetime-local' name='" . $row['Field'] . "' required>";
                        } elseif ($row['Type'] === 'date') {
                            echo "<input type='date' name='" . $row['Field'] . "' required>";
                        } else {
                            echo "<input type='text' name='" . $row['Field'] . "' required>";
                        }
                    }
                    
                    echo "</div>";
                }
            }
        }
        ?>
        <button type="submit" name="submit" class="btn-block">WPROWADŹ DANE</button>
    </form>

    <a href="view_table.php?table=<?php echo $current_table; ?>" class="back-link">< Powrót do przeglądarki</a>
</div>

<?php
require_once 'includes/footer.php';
?>