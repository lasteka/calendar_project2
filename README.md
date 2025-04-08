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

