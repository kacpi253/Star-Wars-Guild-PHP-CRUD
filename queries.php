<?php
require_once 'config/db.php';

$page_title = 'Widoki i Kwerendy - Gildia';
require_once 'includes/header.php';
require_once 'includes/navbar.php';

// Kwerenda 1: JOIN (Raport Zlecen)
$sql_join = "
    SELECT 
        b.Bounty_ID,
        c.Name AS Klient,
        t.Name AS Cel,
        t.Crime AS Przestepstwo,
        b.Reward_Credits AS Nagroda,
        b.Bounty_State AS Status
    FROM Bounties b
    JOIN Clients c ON b.Client_ID = c.Client_ID
    JOIN Targets t ON b.Target_ID = t.Target_ID
    ORDER BY b.Bounty_ID DESC
";

$res_join = null;
try {
    $res_join = $conn->query($sql_join);
} catch (Exception $e) {}

// Kwerenda 2: GROUP BY (Statystyki Łowców)
$sql_group = "
    SELECT 
        bh.Name AS Lowca,
        bh.Guild_Rank AS Ranga,
        COUNT(m.Mission_ID) AS Liczba_Misji,
        COALESCE(SUM(b.Reward_Credits), 0) AS Zarobione_Kredyty
    FROM Bounty_Hunters bh
    LEFT JOIN Missions m ON bh.Hunter_ID = m.Hunter_ID AND m.Puck_Status = 'Sukces'
    LEFT JOIN Bounties b ON m.Bounty_ID = b.Bounty_ID
    GROUP BY bh.Hunter_ID
    ORDER BY Zarobione_Kredyty DESC
";

$res_group = null;
try {
    $res_group = $conn->query($sql_group);
} catch (Exception $e) {}

?>

<div class="container">
    <h2>Zaawansowane Raporty Systemowe</h2>
    <p style="text-align: center; color: var(--text-muted);">Poniższe zestawienia generowane są na żywo z użyciem zaawansowanych kwerend (JOIN, GROUP BY, SUM, COUNT).</p>

    <!-- RAPORT 1 -->
    <div style="margin-top: 40px;">
        <h3 style="color: var(--primary-neon);">[ KWERENDA 1 ] Pełny Raport Zleceń (JOIN 3 Tabel)</h3>
        <p style="color: var(--text-muted); font-size: 0.9em;">
            <code>SELECT b.Bounty_ID, c.Name, t.Name, b.Reward_Credits FROM Bounties JOIN Clients JOIN Targets...</code>
        </p>
        
        <?php if ($res_join && $res_join->num_rows > 0): ?>
            <table>
                <tr>
                    <th>ID Zlecenia</th>
                    <th>Zleceniodawca (Klient)</th>
                    <th>Poszukiwany (Cel)</th>
                    <th>Przestępstwo</th>
                    <th>Nagroda</th>
                    <th>Status</th>
                </tr>
                <?php while($row = $res_join->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['Bounty_ID']; ?></td>
                    <td><?php echo htmlspecialchars((string)$row['Klient']); ?></td>
                    <td><?php echo htmlspecialchars((string)$row['Cel']); ?></td>
                    <td><?php echo htmlspecialchars((string)$row['Przestepstwo']); ?></td>
                    <td><?php echo htmlspecialchars((string)$row['Nagroda']); ?> cr</td>
                    <td><?php echo htmlspecialchars((string)$row['Status']); ?></td>
                </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>Brak wyników do wyświetlenia. Upewnij się, że baza ma odpowiednie tabele.</p>
        <?php endif; ?>
    </div>

    <!-- RAPORT 2 -->
    <div style="margin-top: 50px;">
        <h3 style="color: var(--primary-neon);">[ KWERENDA 2 ] Statystyki Zarobków Łowców (GROUP BY + AGREGACJA)</h3>
        <p style="color: var(--text-muted); font-size: 0.9em;">
            <code>SELECT bh.Name, COUNT(m.Mission_ID), SUM(b.Reward_Credits) FROM Bounty_Hunters LEFT JOIN Missions GROUP BY bh.Hunter_ID...</code>
        </p>

        <?php if ($res_group && $res_group->num_rows > 0): ?>
            <table>
                <tr>
                    <th>Łowca Nagród</th>
                    <th>Ranga w Gildii</th>
                    <th>Udane Misje</th>
                    <th>Zarobione Kredyty</th>
                </tr>
                <?php while($row = $res_group->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars((string)$row['Lowca']); ?></td>
                    <td><?php echo htmlspecialchars((string)$row['Ranga']); ?></td>
                    <td><?php echo $row['Liczba_Misji']; ?></td>
                    <td style="color: #ffcc00; font-weight: bold;"><?php echo number_format((float)$row['Zarobione_Kredyty'], 2, '.', ' '); ?> cr</td>
                </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>Brak wyników do wyświetlenia.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
