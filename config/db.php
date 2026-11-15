<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "star_wars_guild";

// Tworzenie połączenia z bazą MySQL
$conn = new mysqli($host, $user, $password, $dbname);

// Sprawdzenie połączenia
if ($conn->connect_error) {
    die("<div style='color:red; font-family:monospace;'>[BŁĄD SYSTEMU]: Brak dostępu do imperialnych archiwów: " . $conn->connect_error . "</div>");
}

// Ustawienie poprawnego kodowania znaków
$conn->set_charset("utf8mb4");
?>