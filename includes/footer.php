<?php
// Zamknięcie połączenia z bazą danych na końcu każdej strony
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>
</body>
</html>
