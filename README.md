calendar_project/
├── admin/                    # Administratīvā daļa
│   ├── index.php            # Admin panelis – rezervāciju pārskats
│   └── edit.php             # Rezervāciju rediģēšanas lapa
├── public/                   # Publiskā (klienta) puse
│   └── index.php            # Klienta lapa – kalendārs, laika sloti, procedūru saraksts un rezervācijas forma
├── config/
│   └── db_connection.php    # Datubāzes savienojuma konfigurācija
├── css/
│   ├── base.css             # Vispārīgie lapas stili
│   ├── calendar.css         # Kalendāra stili
│   ├── timeslots.css        # Laika slotu stili
│   ├── procedures.css       # Procedūru saraksta un rezervācijas formas stili
│   └── bookings.css         # Rezervāciju saraksta stili
├── includes/                 # Kopīgi atkārtoti lietojami HTML fragmenti
│   ├── header.php           # Galvene (header)
│   ├── calendar.php         # Kalendāra sadaļa
│   ├── timeslots.php        # Laika slotu sadaļa
│   ├── procedures.php       # Procedūru saraksts un rezervācijas forma (var pielāgot)
│   ├── bookings.php         # Rezervāciju saraksta sadaļa
│   └── footer.php           # Kājīte (footer)
├── middleware.php            # Middleware – kopīgas funkcijas (žurnāla ieraksts, uzturēšanas režīma pārbaude)
├── logs/                     # Žurnāla faili
│   └── request.log          # Tiek izveidots automātiski, ieraksta pieprasījumus
└── README.md                 # Projekta dokumentācija

# Calendar Project
calendar_project/project_root/
├── admin/
│   ├── index.php         // Admin paneļa galvenā lapa (rezervāciju saraksts)
│   ├── login.php         // Admin ielogošanās lapa
│   ├── edit.php          // Rezervāciju rediģēšanas lapa
│
├── config/
│   ├── db_connection.php // Datubāzes savienojums (MySQL)
│
├── css/
│   ├── base.css          // Vispārējie stili (visām lapām)
│   ├── admin.css         // Admin paneļa un formu stili (login, register, edit)
│   ├── calendar.css      // Kalendāra stili (public/index.php)
│   ├── timeslots.css     // Laika slotu stili (public/index.php)
│   ├── procedures.css    // Pakalpojumu izvēles stili (public/index.php)
│   ├── bookings.css      // Rezervāciju saraksta stili (public/index.php)
│
├── includes/
│   ├── header.php        // Kopējais galvenes fails (navigācija, logo?)
│   ├── footer.php        // Kopējais kājenes fails
│   ├── calendar.php      // Kalendāra HTML/PHP (public/index.php)
│   ├── timeslots.php     // Laika slotu HTML/PHP (public/index.php)
│   ├── procedures.php    // Pakalpojumu izvēles HTML/PHP (public/index.php)
│   ├── bookings.php      // Lietotāja rezervāciju saraksts (public/index.php)
│
├── public/
│   ├── index.php         // Publiskā galvenā lapa (kalendārs, rezervācijas)
│   ├── login.php         // Publiskā ielogošanās lapa
│   ├── register.php      // Reģistrācijas lapa (ar ielogošanās formu pēc veiksmes)
│
├── middleware.php        // Sesiju un autentifikācijas pārbaude
├── logout.php            // Izlogošanās loģika (dzēš sesiju, novirza uz login)
Šis projekts ir rezervāciju sistēma ar kalendāru, kas izmanto PHP, HTML, CSS un SQL. Projekta struktūra atdalīta starp publisko (klienta) pusi un administratīvo daļu.

## Struktūra
- **public/** – Klienta lapa (index.php)
- **admin/** – Administrācijas panelis (index.php un edit.php)
- **config/** – Datubāzes savienojuma konfigurācija
- **css/** – Stila faili
- **includes/** – Kopīgi atkārtoti lietojamie HTML fragmenti
- **middleware.php** – Kopīgas funkcijas (žurnāla ieraksts, uzturēšanas režīma pārbaude)
- **logs/** – Žurnāla faili

## Lietošana
1. Iestati savienojumu ar datubāzi failā `config/db_connection.php`.
2. Atver `public/index.php` klienta lapai.
3. Piekļūsti administrācijas panelim caur `admin/index.php`.

-- Izveidojam admins tabulu
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Pievienojam noklusējuma admin lietotāju
-- Parole: admin123 (hash ar password_hash)
INSERT INTO admins (email, password) 
VALUES ('admin@gmail.com', 'admin123 ');
ALTER TABLE users
ADD name VARCHAR(255) NOT NULL,
ADD phone VARCHAR(20) NOT NULL;
ALTER TABLE users
ADD name VARCHAR(255) DEFAULT '' NOT NULL,
ADD phone VARCHAR(255) DEFAULT '' NOT NULL;
calendar_project2/
├── admin/                           # Administrācijas paneļa faili
│   ├── index.php                    # Rezervāciju saraksts (tabula ar rezervācijām, izmanto .table)
│   ├── login.php                    # Admin ielogošanās forma (izmanto .form-group, .container)
│   ├── edit.php                     # Rezervāciju rediģēšanas forma
│   ├── add_service.php              # Jaunu pakalpojumu pievienošanas forma
│   ├── services.php                 # Pakalpojumu pārskats un rediģēšana (iespējams, tabula ar pakalpojumiem)
│   ├── add_user.php                 # Manuāla klienta reģistrācija (līdzīga public/register.php)
│   ├── create_admin.php             # Admin konta izveide (iespējams, līdzīga add_user.php)
│   ├── timeslots.php                # Laika slotu pārvaldība (iespējams, tabula vai forma laika slotu pievienošanai/rediģēšanai)
│
├── config/                          # Konfigurācijas faili
│   ├── db_connection.php            # Datubāzes savienojuma iestatījumi (MySQL vai līdzīga DB)
│
├── css/                             # Stilu faili (saglabāti calendar_project2/css/)
│   ├── base.css                     # Vispārējie stili (body, .message, button pamatstili)
│   ├── admin.css                    # Formas (.form-group), tabulas (.table), konteineri (.container)
│   ├── calendar.css                 # Kalendāra izkārtojuma stili (iespējams, public/index.php)
│   ├── timeslots.css                # Laika slotu tabulas (.timeslots-table) stili
│   ├── procedures.css               # Pakalpojumu saraksta (.procedures) stili
│   ├── bookings.css                 # Rezervāciju lapu stili (iespējams, book.php)
│
├── includes/                        # Kopēji PHP fragmenti
│   ├── header.php                   # Galvene (HTML <head>, CSS atsauces, navigācija)
│   ├── footer.php                   # Kājene (HTML <footer>, autortiesības, skripti)
│   ├── calendar.php                 # Kalendāra fragments (iespējams, kalendāra attēlošana index.php)
│   ├── timeslots.php                # Laika slotu fragments (laika slotu attēlošana)
│   ├── procedures.php               # Pakalpojumu fragments (pakalpojumu saraksts)
│   ├── bookings.php                 # Rezervāciju fragments (rezervāciju attēlošana)
│
├── public/                          # Publiski pieejamās lapas
│   ├── index.php                    # Galvenā lapa (kalendārs, laika sloti, rezervāciju izvēle)
│   ├── login.php                    # Publiskā ielogošanās forma
│   ├── register.php                 # Reģistrācijas forma
│   ├── book.php                     # Rezervācijas izveides lapa (ietver pakalpojuma izvēles lauku <select>)
│
├── middleware.php                   # Sesiju pārbaude, žurnalēšana, autentifikācijas loģika
├── logout.php                       # Izlogošanās (sesijas dzēšana)
ALTER TABLE timeslots
ADD is_active TINYINT(1) NOT NULL DEFAULT 1;