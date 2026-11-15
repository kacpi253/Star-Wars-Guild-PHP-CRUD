CREATE DATABASE IF NOT EXISTS star_wars_guild CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE star_wars_guild;

-- Czyszczenie starych danych (z zachowaniem struktury tabel)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS Hunter_Gear;
DROP TABLE IF EXISTS Missions;
DROP TABLE IF EXISTS Bounties;
DROP TABLE IF EXISTS Equipment;
DROP TABLE IF EXISTS Bounty_Hunters;
DROP TABLE IF EXISTS Targets;
DROP TABLE IF EXISTS Clients;
SET FOREIGN_KEY_CHECKS = 1;

-- Tabele słownikowe
CREATE TABLE Clients (
    Client_ID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Faction ENUM('Imperium', 'Nowa Republika', 'Niezależni', 'Huttowie') DEFAULT 'Niezależni'
);

CREATE TABLE Targets (
    Target_ID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Crime VARCHAR(255),
    Last_Known_Location VARCHAR(100)
);

CREATE TABLE Bounty_Hunters (
    Hunter_ID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Guild_Rank ENUM('Nowicjusz', 'Łowca', 'Weteran', 'Mistrz') DEFAULT 'Nowicjusz'
);

CREATE TABLE Equipment (
    Equipment_ID INT AUTO_INCREMENT PRIMARY KEY,
    Item_Name VARCHAR(100) NOT NULL,
    Cost INT NOT NULL
);

-- Tabele operacyjne i relacyjne
CREATE TABLE Bounties (
    Bounty_ID INT AUTO_INCREMENT PRIMARY KEY,
    Client_ID INT,
    Target_ID INT,
    Reward_Credits INT NOT NULL,
    Capture_Condition ENUM('Żywy', 'Martwy', 'Żywy lub Martwy') NOT NULL,
    Bounty_State ENUM('Otwarte', 'W toku', 'Zakończone', 'Anulowane') DEFAULT 'Otwarte',
    Issue_Date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (Client_ID) REFERENCES Clients(Client_ID) ON DELETE CASCADE,
    FOREIGN KEY (Target_ID) REFERENCES Targets(Target_ID) ON DELETE CASCADE
);

CREATE TABLE Missions (
    Mission_ID INT AUTO_INCREMENT PRIMARY KEY,
    Bounty_ID INT,
    Hunter_ID INT,
    Puck_Status ENUM('Aktywny', 'Gorący', 'Zimny', 'Sukces', 'Porażka') DEFAULT 'Aktywny',
    Completion_Date DATETIME,
    FOREIGN KEY (Bounty_ID) REFERENCES Bounties(Bounty_ID) ON DELETE CASCADE,
    FOREIGN KEY (Hunter_ID) REFERENCES Bounty_Hunters(Hunter_ID) ON DELETE CASCADE
);

CREATE TABLE Hunter_Gear (
    Transaction_ID INT AUTO_INCREMENT PRIMARY KEY,
    Hunter_ID INT,
    Equipment_ID INT,
    Quantity INT DEFAULT 1,
    Purchase_Price INT NOT NULL,
    Purchase_Date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (Hunter_ID) REFERENCES Bounty_Hunters(Hunter_ID) ON DELETE CASCADE,
    FOREIGN KEY (Equipment_ID) REFERENCES Equipment(Equipment_ID) ON DELETE CASCADE
);

-- Wstawianie danych
-- 1. Dodajemy Klientów
INSERT INTO Clients (Name, Faction) VALUES 
('Moff Gideon', 'Imperium'),
('Greef Karga', 'Niezależni'),
('Jabba the Hutt', 'Huttowie'),
('Darth Vader', 'Imperium');

-- 2. Dodajemy Cele (Poszukiwanych)
INSERT INTO Targets (Name, Crime, Last_Known_Location) VALUES 
('Grogu', 'Wysoka wartość dla Imperium', 'Arvala-7'),
('Fennec Shand', 'Zabójstwa na zlecenie', 'Tatooine'),
('Han Solo', 'Przemyt, zdrada i niespłacone długi', 'Bespin'),
('Chewbacca', 'Współudział w buncie', 'Kashyyyk'),
('Omega', 'Niezarejestrowany klon', 'Kamino');

-- 3. Dodajemy kultowych Łowców Nagród
INSERT INTO Bounty_Hunters (Name, Guild_Rank) VALUES 
('Din Djarin', 'Weteran'),
('Boba Fett', 'Mistrz'),
('IG-11', 'Łowca'),
('Toro Calican', 'Nowicjusz'),
('Cad Bane', 'Mistrz'),
('Greedo', 'Nowicjusz'),
('Bossk', 'Weteran'),
('IG-88', 'Weteran');

-- 4. Dodajemy Ekwipunek u Zbrojmistrzyni
INSERT INTO Equipment (Item_Name, Cost) VALUES 
('Naramiennik z Beskaru', 5000),
('Świszczące Ptaki (Whistling Birds)', 2000),
('Plecak odrzutowy (Jetpack)', 10000),
('Amunicja do karabinu', 500),
('Miotacz ognia', 3500),
('Karabin snajperski Amban', 8000),
('Elektro-lasso', 1500);

-- 5. Dodajemy Zlecenia
INSERT INTO Bounties (Client_ID, Target_ID, Reward_Credits, Capture_Condition, Bounty_State) VALUES 
(1, 1, 50000, 'Żywy', 'Zakończone'),     
(3, 3, 100000, 'Żywy lub Martwy', 'Otwarte'), 
(2, 2, 20000, 'Żywy', 'W toku'),              
(4, 3, 250000, 'Żywy', 'Otwarte'),            
(1, 5, 75000, 'Żywy', 'Otwarte');             

-- 6. Przypisujemy Zlecenia do Łowców (Kto poluje na kogo)
INSERT INTO Missions (Bounty_ID, Hunter_ID, Puck_Status, Completion_Date) VALUES 
(1, 1, 'Sukces', '2023-11-12 10:00:00'), 
(2, 6, 'Porażka', '1977-05-25 12:00:00'), 
(4, 2, 'Gorący', NULL),                   
(4, 7, 'Aktywny', NULL),                  
(5, 5, 'Aktywny', NULL);                  

-- 7. Historia zakupów
INSERT INTO Hunter_Gear (Hunter_ID, Equipment_ID, Quantity, Purchase_Price) VALUES 
(1, 1, 1, 5000),  
(1, 2, 2, 2000),  
(2, 3, 1, 10000), 
(2, 5, 1, 3500),  
(5, 7, 1, 1500);  

-- ==========================================
-- ZAAWANSOWANE OBIEKTY BAZY DANYCH
-- ==========================================

-- 1. WIDOK (VIEW): Raport zleceń łączący 3 tabele
CREATE OR REPLACE VIEW Raport_Zlecen AS
SELECT 
    b.Bounty_ID,
    c.Name AS Klient,
    t.Name AS Cel,
    t.Crime AS Przestepstwo,
    b.Reward_Credits AS Nagroda,
    b.Bounty_State AS Status
FROM Bounties b
JOIN Clients c ON b.Client_ID = c.Client_ID
JOIN Targets t ON b.Target_ID = t.Target_ID;

DELIMITER $$
-- 2. WYZWALACZE WALIDUJĄCE (Ochrona przed oszustwami)
CREATE TRIGGER check_bounty_reward
BEFORE INSERT ON Bounties
FOR EACH ROW
BEGIN
    IF NEW.Reward_Credits <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = '[BŁĄD KODEKSU GILDII] Zlecenie odrzucone: Nagroda musi być wyższa niż 0 kredytów!';
    END IF;
END$$

CREATE TRIGGER check_hunter_name
BEFORE INSERT ON Bounty_Hunters
FOR EACH ROW
BEGIN
    IF NEW.Name = '' OR NEW.Name IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = '[BŁĄD SYSTEMU] Wpis odrzucony: Brak identyfikatora/imienia łowcy!';
    END IF;
END$$

-- 3. WYZWALACZ AUTOMATYZUJĄCY (Zamykanie zlecenia po sukcesie misji)
CREATE TRIGGER Trg_Zamknij_Zlecenie
AFTER INSERT ON Missions
FOR EACH ROW
BEGIN
    -- Jeśli Łowca zakończył misję sukcesem (zmieniono na Puck_Status = 'Sukces')
    IF NEW.Puck_Status = 'Sukces' THEN
        -- to zaktualizuj główne zlecenie na "Zakończone"
        UPDATE Bounties 
        SET Bounty_State = 'Zakończone' 
        WHERE Bounty_ID = NEW.Bounty_ID;
    END IF;
END$$
DELIMITER ;
