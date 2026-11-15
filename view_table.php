<?php
require_once 'config/db.php';

$allowed_tables = ['Bounty_Hunters', 'Clients', 'Targets', 'Bounties', 'Missions', 'Equipment', 'Hunter_Gear'];
$current_table = isset($_GET['table']) ? $_GET['table'] : 'Bounty_Hunters';

if (!in_array($current_table, $allowed_tables)) {
    die("Odmowa dostępu: Nieznany sektor archiwów.");
}

$search_query = "";
if (isset($_GET['search']) && !empty($_GET['pattern'])) {
    $pattern = $conn->real_escape_string($_GET['pattern']);
    $column = $conn->real_escape_string($_GET['column']);
    $search_query = " WHERE $column LIKE '%$pattern%'";
}

$sql = "SELECT * FROM " . $current_table . $search_query;
$result = null;
try {
    $result = $conn->query($sql);
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
                        
                        <!-- Przycisk Usuń (pobieramy pierwszą kolumnę jako ID) -->
                        <td style="text-align: center; white-space: nowrap; vertical-align: middle;">
                            <?php 
                            $pk_name = $fields[0]; 
                            $pk_value = $row[$pk_name];
                            ?>
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