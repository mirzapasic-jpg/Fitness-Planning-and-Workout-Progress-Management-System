# Fitness Planning and Workout Progress Management System

Forma is a responsive single-page fitness dashboard for planning routines, managing an exercise library, recording training, and exploring progress.

## Milestone 1

Implemented: nine HTML views loaded through AJAX without full document reloads; workout plan, exercise and workout log CRUD; dashboard; interactive canvas charts; mock login/registration with User and Admin roles; editable profile; admin user/exercise management; and a six-entity draft ERD. Data is shared between pages, held in memory, and reset on reload. The initial demo is signed in as the sample User.

This is a frontend demonstration. Client-side roles are not security controls. The Milestone 1 frontend remains a mock demonstration. Milestone 2 adds the separate database/DAO layer; Milestone 3 adds services, a FlightPHP API, TheMealDB integration and Swagger below. JWT authentication, frontend database integration and deployment remain future work.

## Run locally with XAMPP

1. Place the repository under `C:\xampp\htdocs\MirzaPasic\Fitness-Planning-and-Workout-Progress-Management-System`.
2. Start Apache from the XAMPP Control Panel; MySQL is not needed for Milestone 1.
3. Open `http://localhost/MirzaPasic/Fitness-Planning-and-Workout-Progress-Management-System/frontend/`.
4. Navigate using the sidebar. Direct hash links such as `frontend/#progress` also work. Do not open `index.html` with `file://`; templates require HTTP fetch.

No build step, CDN, package installation, configuration file, or SQL import is required for this milestone. JavaScript and HTML Canvas must be enabled in a modern browser.

## Roles and demo accounts

| Role | Email | Public demo password |
| --- | --- | --- |
| User | user@forma.demo | Demo123! |
| Admin | admin@forma.demo | Demo123! |

Log out to switch accounts. Registration creates a User. Admin-created and registered accounts use the same public demo password. Passwords are not stored. Use fictional data. These values are public UI fixtures, not credentials for any real service.

Users manage their own plans and workout records, edit their profile, and manage the shared exercise library. Admins also manage users and shared exercises. Deleting a user deletes their mock plans/logs. The active admin cannot delete or demote themselves. Referenced exercises must be removed from plans and logs before deletion.

## Features and definitions

- Plans: name, description, scheduled date, and selected exercise assignments; view/edit/delete with confirmation.
- Exercise library: realistic sample movements, combined name search and muscle filter, validated add/edit/delete.
- Workout history: exercise, date, integer sets/repetitions, and weight in kg; completed dates cannot be in the future.
- Progress: one completed workout means one distinct training date. Total volume is sets × reps × external weight. Bodyweight/timed entries have zero external-load volume; use repetitions as seconds for timed exercises. Personal records show best external weight and best set volume.
- Charts: sets per date and exercise-specific maximum weight per date; hover/tap or focus and use arrow keys to inspect values. Charts redraw on SPA entry, selection, and resize. Accessible text describes the data.
- Dashboard: totals and recent activity derive from the current user's shared log; upcoming plans derive from schedule dates.
- All mutations are tab-local. Reload restores initial fixtures; logout keeps demo data in the same tab.

## Technology and structure

Current: HTML templates, CSS, vanilla JavaScript, Fetch API for SPA templates, native dialog and canvas, plus a separate FlightPHP REST API, validation services, MySQL/PDO DAOs, OpenAPI/Swagger and TheMealDB service. Planned: JWT, request/error middleware, full logging and frontend database integration.

```text
frontend/
  index.html
  css/style.css
  js/data.js          shared fixtures and derived statistics
  js/app.js           router, renderers, event handling and dialogs
  views/              nine HTML templates
  assets/             local favicon and reserved static assets
backend/
  routes/ services/ dao/ middleware/ config/ logs/
docs/
  ERD.html            standalone viewable draft diagram
  ERD.md              schema, relationships and design choices
  AI_LOG.md
  TESTING.md
AGENTS.md
.gitignore
README.md
```

## Database planning

See [draft ERD](docs/ERD.html) in a browser and [schema notes](docs/ERD.md). Six entities: Users, Exercises, Workout Plans, Plan Exercises, Workout Sessions, Exercise Logs. Users own plans and sessions; plans have many exercise assignments; sessions have many logs; exercises are referenced by assignments and logs. Optional session-to-plan association preserves history when a plan is deleted. The mock frontend groups records by user/date; database sessions will provide explicit session IDs later.

Milestone 2 implements this original six-entity design. See the setup and DAO instructions below. The frontend still uses its original in-memory fixtures.

## Planned external API

The dashboard reserves a visible **Fuel your recovery** section for TheMealDB recipe discovery: meal cards, ingredients, and recipe details relevant to post-workout meals. Milestone 3 implements the FlightPHP service/endpoint with timeout and friendly failure handling, error logging, and OpenAPI documentation. Live frontend integration follows in Milestone 4; the M1 dashboard placeholder remains unchanged.

## Later milestones

- M2 (implemented): MySQL schema/sample SQL and PDO DAO CRUD.
- M3 (implemented): services, FlightPHP REST CRUD, OpenAPI/Swagger, third-party API service.
- M4: hashed passwords, JWT, reusable role middleware, request/error logging, fully connected AJAX frontend.
- M5: frontend MVC refinement, full client/server validation and deployment.



## Milestone 2: database setup on Windows

I designed the six entities and their relationships in M1. sQL types and lengths make concrete choices where the draft ERD did not specify them: unsigned integer IDs, VARCHAR text fields, DATE dates, DECIMAL weights and nullable optional fields. No entity or ERD column was removed or added.

The future request path is AJAX → FlightPHP route → service → DAO → PDO → MySQL. M2 implements only the last two layers. The frontend and historical ERD files were preserved.

## Milestone 3: services, REST API, Swagger and meals

M3 implements all six entity services and CRUD APIs using the original M2 schema and DAOs. No frontend, ERD, SQL, DAO, or private configuration changes were needed. The original six-entity database design belongs to the student. Authentication, role restrictions, middleware, request logging and frontend integration are M4 work. The M3 API is unauthenticated and intended for local development only; the frontend's demo accounts are not backend login accounts.

### Local XAMPP setup

1. Start Apache and MySQL in the XAMPP Control Panel.
2. On a fresh installation, import `backend/database/fitness-planner.sql` using phpMyAdmin (`http://localhost/phpmyadmin/`). Do not reimport over an existing database: the script creates tables and includes sample data.
3. Copy `backend/config/config.example.php` to ignored `backend/config/config.php`. Set your local database host, port, database, username and password there. Never put private settings in source files or documentation. The existing local configuration was preserved.
4. Install Composer if needed. From the repository root run `composer install --working-dir=backend`. Only `flightphp/core` is a runtime dependency; `composer.lock` records the tested v3.19.5. If XAMPP reports missing ZIP support, enable its ZIP extension, or use `C:\xampp\php\php.exe -d extension=zip C:\path\to\composer.phar install --working-dir=backend` to enable it for this command only. The local installation for this milestone used a checksum-verified Composer PHAR in the Windows temporary directory.
5. Open the backend: <http://localhost/MirzaPasic/Fitness-Planning-and-Workout-Progress-Management-System/backend/>.
6. Open Swagger: <http://localhost/MirzaPasic/Fitness-Planning-and-Workout-Progress-Management-System/backend/docs/>.
7. The existing SPA stays at <http://localhost/MirzaPasic/Fitness-Planning-and-Workout-Progress-Management-System/frontend/> and continues using M1 mock data.

Apache must enable `mod_rewrite` and `AllowOverride All` for this folder (verified on the local XAMPP installation). `backend/.htaccess` routes requests to `index.php` and prevents direct downloads of private backend files. Swagger UI loads its pinned JS/CSS from a CDN, so opening the interactive documentation requires internet access. The YAML specification itself is local at `backend/docs/openapi.yaml`. PHP needs PDO MySQL, cURL, JSON, OpenSSL and mbstring; these are available in the tested XAMPP PHP 8.2.12 environment. For HTTPS certificate errors, configure PHP's `curl.cainfo` with a trusted CA bundle; never disable TLS verification.

### Backend files and responsibilities

```text
backend/
  index.php                    bootstrap, JSON error responses, route loading
  .htaccess                    Apache routing and private-file protection
  composer.json / composer.lock
  services/
    BaseService.php            reusable CRUD and validation helpers
    UserService.php            email/profile/role/hash-input validation; hides hashes
    ExerciseService.php        nonempty exercise name and muscle group
    WorkoutPlanService.php     owner reference, name, description, real planned date
    PlanExerciseService.php    plan/exercise references and prescription ranges
    WorkoutSessionService.php  user/plan consistency and completed workout date
    ExerciseLogService.php     session/exercise references, sets/reps/weight
    MealService.php             TheMealDB transport, mapping, timeout/failure logging
  routes/
    Api.php                    shared JSON body/ID parsing and CRUD route registration
    users.php                  registers UserService CRUD
    exercises.php              registers ExerciseService CRUD
    workout-plans.php          registers WorkoutPlanService CRUD
    plan-exercises.php         registers PlanExerciseService CRUD
    workout-sessions.php       registers WorkoutSessionService CRUD
    exercise-logs.php          registers ExerciseLogService CRUD
    meals.php                 registers GET /api/meals/recovery
  docs/
    openapi.yaml               schemas, requests, responses and all 32 operations
    index.html                 Swagger UI
  dao/ config/ database/       original M2 implementation
  logs/error.log               generated external API failure log, ignored by Git
```

Request flow: **HTTP request → FlightPHP route → service → DAO → PDO → MySQL → JSON response**. A route parses the request and chooses a service; the service validates business rules; the DAO executes prepared SQL. No route calls a DAO. Services share a PDO connection when one is supplied, preserving the existing transaction conventions. The API response for users never includes `password_hash`.

### Endpoint contract

Use the backend URL above as the base URL. Each collection has all five operations:

| Collection | List/create | Read/update/delete |
| --- | --- | --- |
| Users | `GET`, `POST /api/users` | `GET`, `PUT`, `DELETE /api/users/{id}` |
| Exercises | `GET`, `POST /api/exercises` | `GET`, `PUT`, `DELETE /api/exercises/{id}` |
| Workout plans | `GET`, `POST /api/workout-plans` | `GET`, `PUT`, `DELETE /api/workout-plans/{id}` |
| Plan assignments | `GET`, `POST /api/plan-exercises` | `GET`, `PUT`, `DELETE /api/plan-exercises/{id}` |
| Workout sessions | `GET`, `POST /api/workout-sessions` | `GET`, `PUT`, `DELETE /api/workout-sessions/{id}` |
| Exercise logs | `GET`, `POST /api/exercise-logs` | `GET`, `PUT`, `DELETE /api/exercise-logs/{id}` |

Additional operations: `GET /` (API overview) and `GET /api/meals/recovery` (meal suggestions).

POST/PUT require `Content-Type: application/json` and a nonempty JSON object containing only writable fields. PUT accepts partial changes and preserves omitted fields. Null clears only nullable columns. IDs must be positive integers within the SQL unsigned INT range, without leading zeroes. Numeric values must fit the schema; weights allow two decimal places. Numeric strings are accepted too, including PDO's DECIMAL strings during updates; JSON numbers are recommended for requests. Dates must be real `YYYY-MM-DD` dates in the SQL DATE range; completed sessions cannot be in the future in Europe/Sarajevo.

Successful reads, updates and deletes return 200; creates return 201. Lists return JSON arrays; creates/reads/updates return a record; deletes return `{"message":"Record deleted."}`. Errors return `{"error":"..."}` with 400 for invalid input/constraint conflicts, 404 for missing records/routes, 500 for unexpected errors, and 503 when meals are unavailable. SQL details and stack traces are hidden. M3 has no authentication/authorization; 401 and 403 will be added with M4 middleware rather than simulated now.

Unique values and original deletion policies remain enforced by MySQL. Users cascade owned plans/sessions, plans cascade assignments and detach sessions, sessions cascade logs, and referenced exercises cannot be deleted. A session's optional plan must belong to its user. A plan linked to workout history cannot be transferred to a different owner. A plan may be created before assignments; add them through the plan-exercises endpoint.

M3 user CRUD accepts the existing schema's `password_hash` column: supply an existing PHP password hash or a fictional `!locked-...` placeholder. Do not send plaintext passwords. This is data CRUD, not registration/login. Email is trimmed and lowercased. No JWT or password workflows are implemented.

### TheMealDB

`GET /api/meals/recovery` makes a real request to TheMealDB's chicken recipe search, then returns up to three recipe ideas with `id`, `name`, `image`, `category`, and ingredient/measure pairs. This supports the planned **Fuel your recovery** cards; the frontend connection is intentionally deferred to M4. These are recipe ideas, not nutritional or personalized dietary recommendations. No calorie/protein values are invented.

The service uses TheMealDB's documented public educational test key `1`; no private key is needed locally. If a private key is introduced later, store it only in ignored configuration. Transport has a 3-second connection timeout and an 8-second total timeout with TLS verification enabled. Non-200 responses, failed connections, invalid JSON and missing meal data produce a friendly 503 and append a timestamped cause to `backend/logs/error.log`. Other API endpoints continue working. There is no cache or fake meal fallback.

Reference documentation: [FlightPHP installation](https://docs.flightphp.com/en/v2/install) and [TheMealDB API guide](https://www.themealdb.com/docs_api_guide.php).

### Test and demonstrate all CRUD operations

Run from the repository root with Apache/MySQL running on the idle local development database:

```powershell
C:\xampp\php\php.exe tests/dao.php
C:\xampp\php\php.exe tests/api.php
```

The API test accepts an optional different backend base URL as its first argument. The DAO suite rolls back its test transaction. The API suite creates uniquely named fixtures, deletes only the IDs it created in a finally block, and compares all six original collections afterward. It does not reimport or clear tables. Both suites may advance auto-increment counters; interrupted HTTP tests may require removing their `M3 Test` fixtures manually. API failure tests deliberately append simulated failures to the ignored error log.

For manual tests, open Swagger, expand an operation, click **Try it out**, edit its JSON example, then **Execute**. Every CRUD endpoint is documented with schemas and examples. Create user → exercise → plan → assignment → session → log, copying returned IDs into child requests. Use a unique email/exercise name, a past session date, and a plan owned by the same user. For each entity, list it, read its new ID, PUT one valid change, and read again. Delete in reverse order. Never experiment with deletes on the sample records because cascades are intentional.

Check invalid input (empty name, invalid email, zero sets, negative weight, impossible date, missing foreign key), malformed JSON, and nonexistent IDs. Confirm 400/404 and friendly JSON. Open `/api/meals/recovery` to demonstrate a live external request. The CLI API suite additionally simulates upstream connection, status, JSON and payload failures without exposing any production test switches.

Actual results and the known intermittent pre-existing frontend chart timing issue are recorded in [docs/TESTING.md](docs/TESTING.md). No deployed application exists yet; hosting remains M5. No GitHub submission actions were performed for M3.
