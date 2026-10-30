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
