<?php
require_once 'config/db.php';

// Pobieramy statystyki z bazy za pomocą prostych zapytań
$hunters_count = 0;
$bounties_count = 0;
$completed_count = 0;

try {
    $hunters_count = $conn->query("SELECT COUNT(*) FROM Bounty_Hunters")->fetch_row()[0];
    $bounties_count = $conn->query("SELECT COUNT(*) FROM Bounties WHERE Bounty_State='Otwarte'")->fetch_row()[0];
    $completed_count = $conn->query("SELECT COUNT(*) FROM Bounties WHERE Bounty_State='Zakończone'")->fetch_row()[0];
} catch (Exception $e) {
    // Ignorujemy w przypadku braku tabel (przed inicjalizacją)
}

$page_title = 'Terminal Gildii Łowców Nagród';
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<div class="container">
    <h1>GILDIA ŁOWCÓW NAGRÓD - SEKTOR NEVARRO</h1>
    <p style="text-align: center; color: var(--text-muted);">Zalogowano jako: Greef Karga. Witamy w systemie zarządzania zleceniami.</p>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Aktywni Łowcy</h3>
            <div class="number"><?php echo $hunters_count; ?></div>
        </div>
        <div class="stat-card">
            <h3>Otwarte Zlecenia</h3>
            <div class="number"><?php echo $bounties_count; ?></div>
        </div>
        <div class="stat-card">
            <h3>Zamknięte Kontrakty</h3>
            <div class="number"><?php echo $completed_count; ?></div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>