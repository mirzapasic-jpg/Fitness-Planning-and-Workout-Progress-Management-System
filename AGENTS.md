# Fitness Planning and Workout Progress Management System

## Project Description
A web application where users can create workout plans,
manage exercises, log completed workouts, and track progress.

## Technology Stack
Frontend: HTML, CSS, JavaScript, Bootstrap (optional)
Backend: PHP with FlightPHP
Database: MySQL using PDO

## Architecture
- Keep frontend and backend separate.
- Frontend uses HTML, CSS and JavaScript.
- Backend uses FlightPHP REST API routes.
- Services contain business logic.
- DAO classes handle database operations.
- All frontend/backend communication uses AJAX.

## Folder Structure
- frontend/ - User interface
- backend/routes/ - API endpoints
- backend/services/ - Business logic
- backend/dao/ - Database operations
- backend/middleware/ - Middleware
- backend/config/ - Configuration
- backend/logs/ - Logs
- docs/ - Project documentation

## Coding Rules
- Use descriptive file and function names.
- Keep code modular and readable.
- Never hardcode passwords or secret keys.
- Use prepared SQL statements.
- Do not mix frontend HTML with backend PHP.
- Implement the frontend as a single-page application.
## Milestone 1 implementation conventions
- Current frontend uses HTML templates fetched by a hash router in frontend/js/app.js. Shared mock data and statistics live in frontend/js/data.js.
- Templates contain structure; application events/rendering are centralized. Do not duplicate listeners or execute template scripts.
- Use kebab-case filenames/routes, camelCase JavaScript names, PascalCase future PHP classes, and snake_case database columns.
- Escape user-generated strings before inserting HTML. Use native required/type/range validation plus business checks. Confirm destructive actions and preserve exercise references.
- Mock role checks demonstrate UX only. Do not describe them as secure authentication; never store real passwords in frontend data. Public demo passwords are fictional UI fixtures.
- Milestone 2 implements only the database and PDO DAO layer. Future routes call services; services call DAOs. All future frontend/backend communication uses AJAX.
- Retain backend/logs/.gitkeep while ignoring generated log files. Never commit secrets, vendor or node_modules.
- Work exclusively on the existing milestone2 branch for this submission. Do not commit, push, create a PR, switch branches, or merge without explicit user approval.
- Run appropriate syntax and workflow checks; record actual results and limitations in docs/TESTING.md and AI usage in docs/AI_LOG.md.

## Milestone 2 conventions
- Preserve the student's original six-entity ERD and its deletion policies. SQL implementation is in backend/database/fitness-planner.sql; original M1 ERD remains as historical design documentation.
- Database.php reads ignored backend/config/config.php; commit only config.example.php. Never print private settings.
- BaseDao.php provides getAll, getById, create, update and delete. Each entity DAO fixes its table and writable column allowlist. Values use native PDO prepared statements; never accept table/column identifiers from a request.
- Use PascalCase PHP filenames/classes and camelCase methods. Inject one shared PDO connection for multi-DAO transactions. DAOs never commit a caller's transaction.
- Let PDOException report constraint failures to callers; never echo database errors into an HTTP response. CLI tests may display diagnostic messages. HTTP error handling comes later.
- No FlightPHP routes, services, authentication, middleware or frontend connection in M2. Date, ownership and form business validation belong to future services, as do authorization checks.
- Run C:\xampp\php\php.exe tests/dao.php against the imported local development database. It rolls back rows on success/failure; auto-increment counters can advance. Do not run against a production database or with concurrent writers.
- Preserve all M1 frontend files and historical documentation. Append actual M2 testing evidence and accurately attribute original database design to the student.

## Milestone 3 conventions
- M1/M2 sections above are historical milestone scope. M3 adds services and FlightPHP routes while preserving the existing frontend, DAO layer, SQL schema and student-designed ERD.
- Stay on the existing branch; do not perform Git/GitHub operations, create/switch branches, commit, push, merge or open a PR without an explicit later request.
- HTTP request -> FlightPHP route -> service -> DAO -> PDO -> MySQL. Only services call DAOs in application code; the original DAO CLI tests remain direct DAO tests.
- backend/index.php loads Composer and routes and maps exceptions to safe JSON. backend/routes/Api.php registers reusable CRUD handlers; each kebab-case entity route file selects its service.
- Keep PascalCase PHP classes/files, camelCase methods and snake_case SQL/JSON columns. Keep secrets in ignored config.php, vendor and generated logs ignored, and existing .gitkeep files intact.
- Validate writable fields, required text, SQL ranges, real dates, references and session/plan owner consistency in services. PUT is a nonempty partial update; null clears only nullable fields. Preserve all existing deletion policies.
- User CRUD uses password_hash or !locked- placeholders, never plaintext passwords. Hide hashes from every response. Registration/login, JWT, authorization and frontend backend integration are M4 tasks.
- JSON errors: 400 validation/constraint conflicts, 404 missing resource, 500 unexpected error, 503 meal provider unavailable. Do not expose SQL, credentials, stack traces or paths. Do not invent 401/403 checks before M4.
- MealService uses the public educational TheMealDB key 1, verified HTTPS, a 3-second connection timeout and an 8-second total timeout. Log external failures to backend/logs/error.log and return a friendly message. Do not add nutritional values or frontend integration.
- Keep backend/docs/openapi.yaml and Swagger aligned with the implementation. Swagger's pinned CDN assets require internet access. Apache must route through backend/.htaccess and deny private backend files.
- Run PHP syntax checks, tests/dao.php and tests/api.php on an idle local development database. API tests clean up only their created IDs; counters can advance. Record real results and limitations in docs/TESTING.md and actual AI corrections in docs/AI_LOG.md.
