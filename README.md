# Schema-webbapplikation

En enkel webbapplikation för att hantera och visa scheman baserat på händelser i olika kategorier och rum. Användaren kan filtrera händelser baserat på datum, kategori, och rum.

## Funktioner

- Lägg till, redigera, och ta bort händelser.
- Visa scheman i en tabellvy, organiserade efter tid och rum.
- Filtrera händelser baserat på datum, kategori, och rum.
- Anpassningsbar tidsintervall och datum som ska visas.
- Möjlighet att ladda upp en logotyp för anpassad branding.

## Installation

Följ dessa steg för att installera och köra applikationen:

1. Installera nödvändiga beroenden. Eftersom detta är en PHP-applikation krävs inga extra beroenden, men du behöver en fungerande PHP-installation och en MySQL/MariaDB-databas.

2. Konfigurera databasen:

    Skapa en ny MySQL/MariaDB-databas.
    Importera SQL-skriptet database/schema.sql för att skapa nödvändiga tabeller.
    Uppdatera admin/config.php med dina databasuppgifter (server, användarnamn, lösenord, och databasnamn).

3. Konfigurera applikationen:

    Besök install.php för att konfigurera applikationen för första gången. Denna sida hjälper dig att skapa och spara config.php med rätt databasanslutningsinformation.

4. Starta applikationen:

    Navigera till index.php för att börja använda schemavisningen.
    Logga in via admin/login.php för att hantera händelser och inställningar.

## Användning

    Visning av schema: Gå till huvudvyn för att se schemat. Du kan filtrera efter datum, kategori och rum.
    Administratörsåtgärder: Logga in som administratör för att lägga till, redigera eller ta bort händelser och hantera inställningar.

## Licens

Denna webbapplikation är licensierad under GNU General Public License v3.0. För mer information, se GPL-3.0 License.