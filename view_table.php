<?php
require_once 'config/db.php';

$allowed_tables = ['Bounty_Hunters', 'Clients', 'Targets', 'Bounties', 'Missions', 'Equipment', 'Hunter_Gear'];
$current_table = isset($_GET['table']) ? $_GET['table'] : 'Bounty_Hunters';

if (!in_array($current_table, $allowed_tables)) {
    die("Odmowa dostępu: Nieznany sektor archiwów.");
}

$search_query = "";
$search_param = "";
$search_column = "";

if (isset($_GET['search']) && !empty($_GET['pattern']) && !empty($_GET['column'])) {
    $search_column = $_GET['column'];
    $search_param = "%" . $_GET['pattern'] . "%";
}

$result = null;
try {
    if (!empty($search_param)) {
        // Weryfikacja poprawności nazwy kolumny przed zapytaniem
        $col_check = $conn->query("SHOW COLUMNS FROM `$current_table`");
        $valid_columns = [];
        if ($col_check) {
            while ($c = $col_check->fetch_assoc()) {
                $valid_columns[] = $c['Field'];
            }
        }
        
        if (in_array($search_column, $valid_columns)) {
            $stmt = $conn->prepare("SELECT * FROM `$current_table` WHERE `$search_column` LIKE ?");
            $stmt->bind_param("s", $search_param);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query("SELECT * FROM `$current_table`");
        }
    } else {
        $result = $conn->query("SELECT * FROM `$current_table`");
    }
} catch (Exception $e) {
    // Ignorujemy błędy braku bazy przy testowaniu
}

$page_title = 'Zarządzanie Tabelami - Gildia';
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<div class="layout">
    <div class="sidebar">
        <h3>Dostępne Archiwa</h3>
        <?php foreach ($allowed_tables as $tbl): ?>
            <a href="view_table.php?table=<?php echo $tbl; ?>" <?php if ($current_table == $tbl) echo 'class="active"'; ?>>
                > <?php echo $tbl; ?>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="content">
        <h2>Tabela: <?php echo $current_table; ?></h2>

        <div class="action-buttons">
            <a href="add_record.php?table=<?php echo $current_table; ?>" class="btn-primary">+ Dodaj Nowy Rekord</a>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
            <table>
                <tr>
                    <?php 
                    $fields = [];
                    while ($field = $result->fetch_field()): 
                        $fields[] = $field->name;
                    ?>
                        <th><?php echo $field->name; ?></th>
                    <?php endwhile; ?>
                    <th>Akcje</th> <!-- Nowa kolumna na akcje -->
                </tr>

                <?php
                $result->data_seek(0);
                while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <?php foreach ($row as $cell): ?>
                            <td>
                                
                                <?php 
                                echo htmlspecialchars((string)$cell); 
                                ?>
                            
                            </td>


                        <?php endforeach; ?>
                        
                        <!-- Przyciski akcji: Edytuj oraz Usuń (pobieramy pierwszą kolumnę jako ID) -->
                        <td style="text-align: center; white-space: nowrap; vertical-align: middle;">
                            <?php 
                            $pk_name = $fields[0]; 
                            $pk_value = $row[$pk_name];
                            ?>
                            <a href="edit_record.php?table=<?php echo urlencode($current_table); ?>&pk=<?php echo urlencode($pk_name); ?>&id=<?php echo urlencode($pk_value); ?>" 
                               class="btn-edit">
                               ✎ EDYTUJ
                            </a>
                            <a href="delete_record.php?table=<?php echo urlencode($current_table); ?>&pk=<?php echo urlencode($pk_name); ?>&id=<?php echo urlencode($pk_value); ?>" 
                               onclick="return confirm('Czy na pewno chcesz usunąć ten wpis z archiwów?');"
                               class="btn-delete">
                               X USUŃ
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>Brak danych w tym sektorze (lub brak wyników wyszukiwania).</p>
        <?php endif; ?>

        <?php if ($result): ?>
        <div class="search-box">
            <h3>Wyszukaj w rejestrze</h3>
            <form action="view_table.php" method="GET">
                <input type="hidden" name="table" value="<?php echo $current_table; ?>">

                <label>Kolumna:</label>
                <select name="column">
                    <?php
                    $result->field_seek(0);
                    while ($field = $result->fetch_field()): ?>
                        <option value="<?php echo $field->name; ?>">
                            <?php echo $field->name; ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <label>Wzór:</label>
                <input type="text" name="pattern" required placeholder="np. Fett">
                <button type="submit" name="search">Szukaj</button>
            </form>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php
require_once 'includes/footer.php';
?>