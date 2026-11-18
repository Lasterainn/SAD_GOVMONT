Dilaksanakan di compose dan di deploy di docker moga moga kecil
tiap iterasi di build dan tampilin aja di github nya sebagai release

Harapan Struktur Projek

project/  
├── public/                     # ONLY folder the web server exposes  
│   ├── index.php               # front controller: loads bootstrap, routes the request  
│   ├── css/style.css  
│   └── js/app.js  
├── src/                        # application code (not web-accessible)  
│   ├── bootstrap.php           # starts session, loads config, sets headers, error handling  
│   ├── Router.php              # maps URL + method to a controller function  
│   ├── Database.php            # PDO connection (prepared statements, exceptions on)  
│   ├── Auth.php                # login, logout, register, password hashing, session regeneration  
│   ├── Csrf.php                # generate and verify CSRF tokens  
│   ├── Validator.php           # server-side input validation helpers  
│   ├── Controllers/  
│   │   ├── AuthController.php  # register/login/logout actions  
│   │   └── NoteController.php  # CRUD, with ownership checks  
│   └── Models/  
│       ├── User.php            # user queries  
│       └── Note.php            # note queries (always filtered by user_id)  
├── templates/                  # HTML only, no SQL or business logic  
│   ├── layout.php              # header, footer, nav, escapes output  
│   ├── login.php  
│   ├── register.php  
│   ├── notes_list.php  
│   └── note_form.php  
├── config/  
│   └── config.php              # reads values from environment variables  
├── database/  
│   ├── schema.sql              # CREATE TABLE users, notes  
│   └── seed.sql                # fake test data, used to reset before scans  
├── docs/                       # your SDLC evidence  
│   ├── 01-requirements.md  
│   ├── 02-design-threat-model.md  
│   ├── 03-secure-coding-notes.md  
│   ├── 04-test-plan.md  
│   ├── 05-deployment-checklist.md  
│   ├── 06-maintenance-plan.md  
│   └── reports/                # raw scan output (zap, semgrep, gitleaks, trivy)  
├── tests/  
│   ├── AuthTest.php            # wrong password fails, session regenerated  
│   └── AccessControlTest.php   # user A cannot read or edit user B's note  
├── docker/  
│   ├── nginx.conf  
│   ├── Caddyfile               # production HTTPS (used on the VPS)  
│   └── php.ini                 # display_errors=Off, expose_php=Off, etc.  
├── .github/workflows/  
│   └── security.yml            # CI: semgrep, composer audit, gitleaks, ZAP baseline  
├── Dockerfile                  # php-fpm + pdo_mysql  
├── docker-compose.yml          # web, php, db  
├── composer.json               # dependencies, PSR-4 autoload  
├── .env.example                # variable names with fake values (committed)  
├── .env                        # real values (NOT committed)  
├── .gitignore                  # .env, vendor/, docs/reports/ if large  
└── README.md                   # how to run, test, and deploy  
